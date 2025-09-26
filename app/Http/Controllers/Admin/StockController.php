<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\{StockLog, StockCategory, Stock};
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockController extends Controller
{
    // app/Http/Controllers/Admin/StockController.php

    public function index(Request $request)
    {
        $q    = trim($request->input('q', ''));
        $cat  = $request->input('category');
        $sort = $request->input('sort', 'name');
        $dir  = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // whitelist ฟิลด์ที่อนุญาตให้เรียง
        $allowed = ['name','unit','category','added_qty','used_qty','book_qty','remain'];

        if (!in_array($sort, $allowed, true)) {
            $sort = 'name';
        }

        $stocks = \App\Models\Stock::query()
            // join category เฉพาะตอนต้องใช้เรียง/ค้นหาชื่อหมวด
            ->leftJoin('stock_categories as sc', 'sc.id', '=', 'stocks.stock_category_id')
            ->select('stocks.*') // ป้องกัน column ambiguous
            ->with('category:id,name')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('stocks.name', 'like', "%{$q}%")
                    ->orWhere('sc.name', 'like', "%{$q}%");
                });
            })
            ->when($cat, fn($qq) => $qq->where('stocks.stock_category_id', $cat))
            ->withSum(['logs as added_qty' => fn($w) => $w->where('direction','in')],  'qty')
            ->withSum(['logs as used_qty'  => fn($w) => $w->where('direction','out')], 'qty');

        // จัดการ order ตามฟิลด์ที่เลือก
        switch ($sort) {
            case 'name':
            case 'unit':
                $stocks->orderBy("stocks.$sort", $dir);
                break;
            case 'category':
                $stocks->orderBy('sc.name', $dir);
                break;
            case 'added_qty':
                $stocks->orderByRaw('COALESCE(added_qty,0) '.$dir);
                break;
            case 'used_qty':
                $stocks->orderByRaw('COALESCE(used_qty,0) '.$dir);
                break;
            case 'book_qty':
                // book = qty_open + in
                $stocks->orderByRaw('(COALESCE(stocks.qty_open,0) + COALESCE(added_qty,0)) '.$dir);
                break;
            case 'remain':
                // remain = qty_open + in - out
                $stocks->orderByRaw('(COALESCE(stocks.qty_open,0) + COALESCE(added_qty,0) - COALESCE(used_qty,0)) '.$dir);
                break;
        }

        $stocks = $stocks
            ->paginate(10)
            ->withQueryString();

        return view('admin.stock', compact('stocks','q','cat','sort','dir'));
    }

    public function addIn(Request $request, Stock $stock)
    {
        $data = $request->validate([
            'qty'  => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        $stock->logs()->create([
            'direction' => 'in',
            'qty'       => $data['qty'],
            'note'      => $data['note'] ?? null,
            'user_id'   => auth()->id(),
        ]);

        return back()->with('success', 'บันทึก “รับเข้า” แล้ว');
    }

    public function addOut(Request $request, Stock $stock)
    {
        $data = $request->validate([
            'qty'  => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        // ป้องกันติดลบ
        $remain = $stock->qty_open + $stock->logs()->where('direction','in')->sum('qty')
                                   - $stock->logs()->where('direction','out')->sum('qty');
        if ($data['qty'] > $remain) {
            return back()->with('error', 'จำนวนที่เบิกเกินกว่าคงเหลือ');
        }

        $stock->logs()->create([
            'direction' => 'out',
            'qty'       => $data['qty'],
            'note'      => $data['note'] ?? null,
            'user_id'   => auth()->id(),
        ]);

        return back()->with('success', 'บันทึก “เบิกออก/ใช้ไป” แล้ว');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'category_name'  => 'required|string|max:100',
            'unit'           => 'required|string|max:32',
            'qty_open'       => 'nullable|integer|min:0',
        ]);

        $cat = StockCategory::firstOrCreate(['name' => $data['category_name']]);

        Stock::create([
            'name' => $data['name'],
            'stock_category_id' => $cat->id,
            'unit' => $data['unit'],
            'qty_open' => $data['qty_open'] ?? 0,
        ]);

        return back()->with('success', 'เพิ่มรายการสต็อกเรียบร้อย');
    }

    /** ปุ่ม "Import" CSV */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('csv_file')->getRealPath();
        $f = fopen($file, 'r');

        // อ่าน header
        $headers = array_map('trim', fgetcsv($f));
        // รองรับหัวคอลัมน์:
        // name, category, unit, qty_open, in_qty, out_qty, note
        $idx = array_flip($headers);

        $required = ['name','category','unit'];
        foreach ($required as $h) {
            if (!isset($idx[$h])) {
                return back()->with('error', "ไฟล์ CSV ต้องมีหัวคอลัมน์ '{$h}'");
            }
        }

        $count = 0;
        while (($row = fgetcsv($f)) !== false) {
            $row = array_map('trim', $row);
            if ($row === [null] || $row === [] || ($row[$idx['name']] ?? '') === '') continue;

            $name = $row[$idx['name']];
            $categoryName = $row[$idx['category']];
            $unit = $row[$idx['unit']];
            $qtyOpen = isset($idx['qty_open']) ? (int)($row[$idx['qty_open']] ?? 0) : null;
            $inQty  = isset($idx['in_qty'])  ? (int)($row[$idx['in_qty']]  ?? 0) : 0;
            $outQty = isset($idx['out_qty']) ? (int)($row[$idx['out_qty']] ?? 0) : 0;
            $note   = isset($idx['note']) ? ($row[$idx['note']] ?? null) : null;

            $cat = StockCategory::firstOrCreate(['name' => $categoryName]);

            // upsert โดยอิง name+category
            $stock = Stock::firstOrCreate(
                ['name' => $name, 'stock_category_id' => $cat->id],
                ['unit' => $unit, 'qty_open' => max(0, (int)$qtyOpen)]
            );

            // อัปเดต unit/qty_open หากมีค่าใหม่ในไฟล์
            $changed = false;
            if ($stock->unit !== $unit) { $stock->unit = $unit; $changed = true; }
            if ($qtyOpen !== null && $qtyOpen !== $stock->qty_open) { $stock->qty_open = max(0,(int)$qtyOpen); $changed = true; }
            if ($changed) $stock->save();

            if ($inQty > 0) {
                $stock->logs()->create([
                    'direction' => 'in',
                    'qty' => $inQty,
                    'note' => $note ?? 'import',
                    'user_id' => auth()->id(),
                ]);
            }
            if ($outQty > 0) {
                $stock->logs()->create([
                    'direction' => 'out',
                    'qty' => $outQty,
                    'note' => $note ?? 'import',
                    'user_id' => auth()->id(),
                ]);
            }

            $count++;
        }
        fclose($f);

        return back()->with('success', "นำเข้าเรียบร้อย: {$count} แถว");
    }

    /** ปุ่ม "Export" CSV */
    public function export(): StreamedResponse
    {
        $fileName = 'stocks_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->streamDownload(function() {
            $out = fopen('php://output', 'w');

            // หัวคอลัมน์ (ให้เข้ากับ import)
            fputcsv($out, ['name','category','unit','qty_open','in_qty','out_qty','remain']);

            $rows = Stock::with('category')
                ->withSum(['logs as in_qty'  => fn($q)=>$q->where('direction','in')],  'qty')
                ->withSum(['logs as out_qty' => fn($q)=>$q->where('direction','out')], 'qty')
                ->orderBy('id')
                ->get();

            foreach ($rows as $s) {
                $remain = ($s->qty_open + (int)$s->in_qty) - (int)$s->out_qty;
                fputcsv($out, [
                    $s->name,
                    $s->category?->name,
                    $s->unit,
                    $s->qty_open,
                    (int)$s->in_qty,
                    (int)$s->out_qty,
                    $remain,
                ]);
            }
            fclose($out);
        }, $fileName, $headers);
    }

    // ปุ่ม "Download Template" CSV
    public function downloadTemplate()
    {
        $filename = 'stock_import_template_'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            // ใส่ BOM ให้ Excel อ่าน UTF-8 ถูก
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // หัวคอลัมน์ (ต้องตรงกับตัว import)
            fputcsv($out, ['name', 'category', 'unit', 'qty_open', 'in_qty', 'out_qty', 'note']);

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function destroy(Stock $stock)
    {
        DB::transaction(function () use ($stock) {
            // ลบประวัติทั้งหมดก่อน (กัน FK error)
            $stock->logs()->delete();
            // ลบตัวสต็อก
            $stock->delete();
        });

        return back()->with('success', 'ลบรายการสต็อกเรียบร้อย');
    }
}