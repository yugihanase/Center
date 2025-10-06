<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-left">
            {{ __('ตารางเวรช่าง') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container">
        <h1>การทำงานของช่างเวรโรงพยาบาล</h1>
        <h2 id="month-year-display"></h2>
        <p style="text-align:center; color:#555;">กดปุ่ม ➕ เพื่อกำหนดช่างเวรประจำวัน</p>
        <div class="calendar-container">
            <table class="calendar">
                <thead>
                    <tr>
                        <th class="weekend-header">อาทิตย์</th><th>จันทร์</th><th>อังคาร</th><th>พุธ</th><th>พฤหัสบดี</th><th>ศุกร์</th><th class="weekend-header">เสาร์</th>
                    </tr>
                </thead>
                <tbody id="calendar-body">
                    </tbody>
            </table>
        </div>

        <div class="calendar-nav">
            <button class="nav-button" onclick="changeMonth(-1)">หน้าก่อนหน้า</button>
            <button class="nav-button" onclick="changeMonth(1)">ถัดไป &rarr;</button>
        </div>
        
        </div>

    <div id="shiftModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>กำหนดช่างเวร</h3>
                <span class="close-button" onclick="closeModal()">&times;</span>
            </div>
            <p id="modal-date-display" class="modal-date"></p>
            <div class="modal-body">
                <input type="text" id="shiftInput" placeholder="กรอกรหัสพนักงาน (เช่น 000001)" oninput="displayEmployeeName()">
                <div id="employeeNameDisplay"></div>
            </div>
            <div class="modal-footer">
                <button class="save-button" onclick="saveShift()">เพิ่ม</button>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>แก้ไขช่างเวร</h3>
                <span class="close-button" onclick="closeEditModal()">&times;</span>
            </div>
            <p id="edit-modal-date-display" class="modal-date"></p>
            <div class="modal-body">
                <ul id="employeeList" class="employee-list"></ul>
                <div style="margin-top: 15px;">
                    <input type="text" id="editShiftInput" placeholder="เพิ่มรหัสพนักงานใหม่" oninput="displayNewEmployeeName()">
                    <div id="newEmployeeNameDisplay"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="save-button" onclick="saveEditedShifts()">บันทึก</button>
            </div>
        </div>
    </div>
        </div>
    </div>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #0056b3;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 1.8em;
            color: #495057;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .calendar-container {
            overflow-x: auto;
        }
        .calendar {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 20px;
            font-size: 0.7em;
        }
        .calendar th, .calendar td {
            border: 1px solid #dee2e6;
            padding: 6px;
            text-align: center;
            vertical-align: top;
            height: 80px;
            position: relative;
        }
        .calendar th {
            background-color: #e9ecef;
            color: #495057;
            font-size: 1em;
        }
        .calendar td {
            background-color: #ffffff;
        }
        .day-header {
            font-weight: bold;
            font-size: 1em;
            color: #212529;
            text-align: right;
            padding-right: 5px;
            margin-bottom: 5px;
        }
        .control-button-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 40px;
            margin: 10px auto;
        }
        .add-button, .edit-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        .add-button {
            background-color: #28a745;
        }
        .add-button:hover {
            background-color: #218838;
        }
        .edit-button {
            background-color: #6c757d;
            font-size: 1.2em;
            width: 30px;
            height: 30px;
        }
        .edit-button:hover {
            background-color: #5a6268;
        }
        .selected-name {
            padding: 6px;
            border-radius: 4px;
            font-weight: bold;
            color: #000;
            margin-top: 5px;
            display: block;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .close-button {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .modal-body {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .modal-body input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .modal-footer {
            text-align: right;
            margin-top: 20px;
        }
        .save-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .nav-button {
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            border: 1px solid #ced4da;
            border-radius: 5px;
            background-color: #e9ecef;
            color: #495057;
            text-decoration: none;
        }
        .nav-button:hover {
            background-color: #dee2e6;
        }
        #employeeNameDisplay {
            margin-top: -5px;
            font-weight: bold;
            color: #333;
        }
        .found-name {
            color: #004085;
        }
        .not-found-name {
            color: #dc3545;
        }
        .modal-date {
            text-align: center;
            font-size: 1.2em;
            font-weight: bold;
            color: #0056b3;
            margin-bottom: 15px;
        }
        
        /* Styles for weekends and holidays */
        .holiday-text {
            color: #dc3545 !important;
        }
        .weekend-header {
            background-color: #f8d7da !important;
        }

        /* Styles for edit modal */
        #editModal {
            display: none;
        }
        .employee-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .employee-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #e9ecef;
        }
        .employee-list li:last-child {
            border-bottom: none;
        }
        .remove-button {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #dc3545;
        }
    </style>
    <script>
        const employees = [
            { id: '000001', name: 'สมชาย แสง', color: '#f48fb1' },
            { id: '000002', name: 'ประสิทธิ์ คำหล้า', color: '#b39ddb' },
            { id: '000003', name: 'กิตติ วงศ์ดี', color: '#91a7ff' },
            { id: '000004', name: 'ชูชาติ สวัสดิ์', color: '#81d4fa' },
            { id: '000005', name: 'พิชิต สุขเกษม', color: '#4db6ac' },
            { id: '000006', name: 'ชัย ศรีสุข', color: '#72d572' },
            { id: '000007', name: 'สมพงษ์ รุ่งเรือง', color: '#dce775' },
            { id: '000008', name: 'อนุชา ใจดี', color: '#fff176' },
            { id: '000009', name: 'ศักดิ์ ทองมาก', color: '#ffb74d' },
            { id: '000010', name: 'ชล นครินทร์', color: '#ff7043' },
            { id: '000011', name: 'บรรจง ศรี', color: '#a1887f' },
            { id: '000012', name: 'วิทยา แก้วใส', color: '#e84e40' }
        ];

        let currentCell = null;
        let currentMonth = 0; // 0 = มกราคม
        let currentYear = 2569;
        const monthNames = [
            "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
            "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
        ];
        const dayNames = [
            "อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์"
        ];
        
        // วันหยุดราชการสำหรับปี 2569 (2026) ในรูปแบบ เดือน-วัน
        const publicHolidays = [
            '0-1', '0-2', // ม.ค. 1, 2
            '2-3', // มี.ค. 3
            '3-6', '3-13', '3-14', '3-15', // เม.ย. 6, 13, 14, 15
            '4-1', '4-4', '4-31', // พ.ค. 1, 4, 31 (วันวิสาขบูชา)
            '5-1', '5-3', // มิ.ย. 1 (ชดเชยวิสาขบูชา), 3
            '6-28', '6-29', '6-30', // ก.ค. 28, 29, 30
            '7-12', // ส.ค. 12
            '9-13', '9-23', // ต.ค. 13, 23
            '11-5', '11-7', '11-10', '11-31' // ธ.ค. 5, 7, 10, 31
        ];

        let shiftData = JSON.parse(localStorage.getItem('shiftData')) || {};

        document.addEventListener('DOMContentLoaded', function() {
            renderCalendar(currentMonth, currentYear);
        });

        function getDaysInMonth(month, year) {
            const christianYear = year - 543;
            return new Date(christianYear, month + 1, 0).getDate();
        }

        function renderCalendar(month, year) {
            const calendarBody = document.getElementById('calendar-body');
            const monthYearDisplay = document.getElementById('month-year-display');
            calendarBody.innerHTML = '';
            
            monthYearDisplay.textContent = `เดือน ${monthNames[month]} พ.ศ. ${year}`;

            const christianYear = year - 543;
            const firstDay = new Date(christianYear, month, 1).getDay();
            const daysInMonth = getDaysInMonth(month, year);

            let row = document.createElement('tr');

            for (let i = 0; i < firstDay; i++) {
                row.innerHTML += '<td></td>';
            }

            for (let i = 1; i <= daysInMonth; i++) {
                if (row.children.length === 7) {
                    calendarBody.appendChild(row);
                    row = document.createElement('tr');
                }
                const cell = document.createElement('td');
                const dateKey = `${year}-${month}-${i}`;
                
                const date = new Date(christianYear, month, i);
                const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday
                const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                const isHoliday = publicHolidays.includes(`${month}-${i}`);

                const dayHeaderClass = (isWeekend || isHoliday) ? 'day-header holiday-text' : 'day-header';

                cell.innerHTML = `
                    <div class="${dayHeaderClass}">${i}</div>
                    <div class="control-button-container"></div>
                `;
                
                const controlContainer = cell.querySelector('.control-button-container');

                if (shiftData[dateKey] && shiftData[dateKey].length > 0) {
                    shiftData[dateKey].forEach(emp => {
                        const nameDiv = document.createElement('div');
                        nameDiv.classList.add('selected-name');
                        nameDiv.textContent = `${emp.name}`;
                        nameDiv.style.backgroundColor = emp.color;
                        cell.appendChild(nameDiv);
                    });
                    
                    const editButton = document.createElement('button');
                    editButton.classList.add('edit-button');
                    editButton.textContent = '✎';
                    editButton.onclick = function() {
                        openEditModal(this, dateKey);
                    };
                    controlContainer.appendChild(editButton);
                } else {
                    const addButton = document.createElement('button');
                    addButton.classList.add('add-button');
                    addButton.textContent = '+';
                    addButton.onclick = function() {
                        openAddModal(this, dateKey);
                    };
                    controlContainer.appendChild(addButton);
                }

                row.appendChild(cell);
            }

            while (row.children.length < 7) {
                row.innerHTML += '<td></td>';
            }
            calendarBody.appendChild(row);
        }

        function changeMonth(direction) {
            currentMonth += direction;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            } else if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar(currentMonth, currentYear);
        }

        function openAddModal(button, dateKey) {
            currentCell = button.closest('td');
            currentCell.dateKey = dateKey;
            
            const [year, month, day] = dateKey.split('-').map(Number);
            const date = new Date(year - 543, month, day);
            const dayName = dayNames[date.getDay()];

            document.getElementById('modal-date-display').textContent = `วันที่ ${dayName} ที่ ${day} ${monthNames[month]} พ.ศ. ${year}`;
            
            document.getElementById('shiftInput').value = '';
            document.getElementById('employeeNameDisplay').textContent = '';
            document.getElementById('employeeNameDisplay').classList.remove('found-name', 'not-found-name');

            document.getElementById('shiftModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('shiftModal').style.display = 'none';
        }

        function displayEmployeeName() {
            const shiftId = document.getElementById('shiftInput').value.trim();
            const nameDisplay = document.getElementById('employeeNameDisplay');
            nameDisplay.textContent = '';
            nameDisplay.classList.remove('found-name', 'not-found-name');
            
            if (shiftId.length === 0) {
                return;
            }

            const emp = employees.find(e => e.id === shiftId);
            if (emp) {
                nameDisplay.textContent = `ชื่อ: ${emp.name}`;
                nameDisplay.classList.add('found-name');
            } else {
                nameDisplay.textContent = 'ไม่พบรหัสพนักงานนี้';
                nameDisplay.classList.add('not-found-name');
            }
        }
        
        function saveShift() {
            const shiftId = document.getElementById('shiftInput').value.trim();
            const dateKey = currentCell.dateKey;
            
            if (!shiftId) {
                alert('กรุณากรอกรหัสพนักงาน');
                return;
            }
            
            const shiftsInDay = shiftData[dateKey] || [];
            if (shiftsInDay.length >= 2) {
                alert('ไม่สามารถเพิ่มได้เกิน 2 คนต่อวัน');
                return;
            }

            const emp = employees.find(e => e.id === shiftId);
            if (!emp) {
                alert('ไม่พบรหัสพนักงานนี้');
                return;
            }
            
            if (!shiftData[dateKey]) {
                shiftData[dateKey] = [];
            }
            
            shiftData[dateKey].push(emp);
            localStorage.setItem('shiftData', JSON.stringify(shiftData));

            renderCalendar(currentMonth, currentYear);
            closeModal();
        }

        // --- Edit Modal Functions ---
        function openEditModal(button, dateKey) {
            currentCell = button.closest('td');
            currentCell.dateKey = dateKey;
            
            const [year, month, day] = dateKey.split('-').map(Number);
            const date = new Date(year - 543, month, day);
            const dayName = dayNames[date.getDay()];

            document.getElementById('edit-modal-date-display').textContent = `วันที่ ${dayName} ที่ ${day} ${monthNames[month]} พ.ศ. ${year}`;
            
            const employeeList = document.getElementById('employeeList');
            employeeList.innerHTML = '';
            
            const currentShifts = shiftData[dateKey] || [];
            currentShifts.forEach(emp => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <span>${emp.name} (${emp.id})</span>
                    <button class="remove-button" onclick="removeEmployee('${emp.id}')">&times;</button>
                `;
                employeeList.appendChild(li);
            });
            
            document.getElementById('editShiftInput').value = '';
            document.getElementById('newEmployeeNameDisplay').textContent = '';

            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function removeEmployee(employeeId) {
            const dateKey = currentCell.dateKey;
            if (shiftData[dateKey]) {
                shiftData[dateKey] = shiftData[dateKey].filter(emp => emp.id !== employeeId);
                localStorage.setItem('shiftData', JSON.stringify(shiftData));
            }
            // Re-open the modal to refresh the list instantly
            openEditModal(currentCell.querySelector('.edit-button'), dateKey); 
            renderCalendar(currentMonth, currentYear);
        }

        function displayNewEmployeeName() {
            const shiftId = document.getElementById('editShiftInput').value.trim();
            const nameDisplay = document.getElementById('newEmployeeNameDisplay');
            nameDisplay.textContent = '';
            nameDisplay.classList.remove('found-name', 'not-found-name');

            if (shiftId.length === 0) {
                return;
            }
            
            const emp = employees.find(e => e.id === shiftId);
            if (emp) {
                nameDisplay.textContent = `ชื่อ: ${emp.name}`;
                nameDisplay.classList.add('found-name');
            } else {
                nameDisplay.textContent = 'ไม่พบรหัสพนักงานนี้';
                nameDisplay.classList.add('not-found-name');
            }
        }

        function saveEditedShifts() {
            const shiftId = document.getElementById('editShiftInput').value.trim();
            const dateKey = currentCell.dateKey;

            const currentShifts = shiftData[dateKey] || [];
            
            if (shiftId) {
                const isAlreadyAdded = currentShifts.some(emp => emp.id === shiftId);
                if (isAlreadyAdded) {
                    alert('พนักงานคนนี้ถูกเพิ่มแล้ว');
                    return;
                }
                
                if (currentShifts.length >= 2) {
                    alert('ไม่สามารถเพิ่มได้เกิน 2 คนต่อวัน');
                    return;
                }
                
                const emp = employees.find(e => e.id === shiftId);
                if (!emp) {
                    alert('ไม่พบรหัสพนักงานนี้');
                    return;
                }
                
                currentShifts.push(emp);
                localStorage.setItem('shiftData', JSON.stringify(shiftData));
            }

            closeEditModal();
            renderCalendar(currentMonth, currentYear);
        }
    </script>
</x-app-layout>

