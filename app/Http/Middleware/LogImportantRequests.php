<?php
// app/Http/Middleware/LogImportantRequests.php
namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class LogImportantRequests
{
    public function handle($request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // กรอง: แค่ POST/PUT/PATCH/DELETE และไม่ใช่ไฟล์ asset
        if (in_array($request->method(), ['POST','PUT','PATCH','DELETE'])
            && !preg_match('#^/(storage|images|js|css|vendor)/#', $request->path())
        ) {
            activity_log([
                'event'       => 'request',
                'subject'     => null,
                'description' => "HTTP {$request->method()} {$request->path()}",
                'properties'  => [
                    'route'   => optional($request->route())->getName(),
                    'inputs'  => $request->except(['password','_token']),
                ],
                'status_code' => $response->getStatusCode(),
            ]);
        }
        return $response;
    }
}
