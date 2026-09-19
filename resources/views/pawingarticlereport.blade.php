<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pawning Article Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- DataTables & Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* Global Styles */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-wrapper {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px auto;
            max-width: 98%;
        }

        /* Header Styles */
        h2 {
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            padding-bottom: 15px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .text-danger {
            font-size: 1.2rem;
            font-weight: 600;
            padding: 20px;
            background: #fee;
            border-left: 4px solid #dc3545;
            border-radius: 5px;
        }

        /* Form Styles */
        .filter-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* Button Styles */
        .btn {
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #718096;
        }

        .btn-secondary:hover {
            background: #4a5568;
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            font-size: 1.1rem;
            padding: 12px 40px;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.4);
        }

        /* Table Styles */
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        table.dataTable {
            border-collapse: separate;
            border-spacing: 0;
        }

        table.dataTable th {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 15px 10px;
            white-space: nowrap;
            border: none;
        }

        table.dataTable td {
            padding: 12px 10px;
            vertical-align: middle;
            white-space: nowrap;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }

        table.dataTable tbody tr {
            transition: all 0.2s ease;
        }

        table.dataTable tbody tr:hover {
            background-color: #f7fafc;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        table.dataTable tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table.dataTable tfoot th {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            font-weight: 700;
            font-size: 1rem;
            padding: 15px 10px;
            border: none;
        }

        /* DataTables Controls */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 15px;
            margin-left: 10px;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #667eea;
            outline: none;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px 10px;
            margin: 0 10px;
        }

        /* DataTables Buttons */
        .dt-buttons {
            margin-bottom: 20px;
        }

        .dt-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            margin-right: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .dt-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
        }

        /* Pagination */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 12px;
            margin: 0 3px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #667eea;
            color: white !important;
            border-color: #667eea;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            border-color: #667eea;
        }

        /* Print Button Container */
        .print-container {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container-wrapper {
                padding: 15px;
                margin: 10px;
            }

            .filter-form {
                padding: 15px;
            }

            .d-flex.gap-3 {
                flex-direction: column;
                gap: 15px !important;
            }

            .btn {
                width: 100%;
            }

            h2 {
                font-size: 1.5rem;
            }
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container-wrapper {
            animation: fadeIn 0.5s ease;
        }

        /* User Role Badge */
        .user-role-badge {
            position: absolute;
            top: 20px;
            right: 30px;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.5s ease;
            z-index: 1000;
        }

        .user-role-badge.admin {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            color: #fff;
            border: 2px solid #f6d365;
        }

        .user-role-badge.cashier {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #2d3748;
            border: 2px solid #a8edea;
        }

        .user-role-badge i {
            font-size: 1.3rem;
            animation: pulse 2s infinite;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Enhanced Button Styles */
        .btn i {
            margin-right: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096 0%, #4a5568 100%);
            position: relative;
            overflow: hidden;
        }

        .btn-secondary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }

        .btn-secondary:hover::before {
            left: 100%;
        }

        .btn-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            font-size: 1.1rem;
            padding: 12px 40px;
            position: relative;
            overflow: hidden;
        }

        .btn-success::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }

        .btn-success:hover::before {
            left: 100%;
        }

        .btn-success i {
            font-size: 1.2rem;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
            }

            .container-wrapper {
                box-shadow: none;
                padding: 0;
            }

            .user-role-badge,
            .filter-form,
            .print-container,
            .dt-buttons,
            .dataTables_filter,
            .dataTables_info,
            .dataTables_paginate,
            .dataTables_length {
                display: none !important;
            }

            table.dataTable th,
            table.dataTable td {
                padding: 8px;
                font-size: 12px;
            }
        }

        /* Mobile Responsive for Badge */
        @media (max-width: 768px) {
            .user-role-badge {
                position: static;
                margin: 0 auto 20px auto;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="p-3">
    <!-- User Role Badge -->
    @if(auth()->user()->role == 'Admin')
        <div class="user-role-badge admin">
            <i class="bi bi-shield-fill-check"></i>
            <span>Admin</span>
        </div>
    @else
        <div class="user-role-badge cashier">
            <i class="bi bi-person-circle"></i>
            <span>Cashier</span>
        </div>
    @endif

    <div class="container-wrapper">
        @if($receipts->count() > 0)
            <h2 class="text-center">Pawning Article Report</h2>
        @else
            <p class="text-center text-danger">No results found.</p>
        @endif

        <!-- Date Filter Form -->
        <form action="" method="get" class="filter-form mb-4 d-flex gap-3 align-items-end">
            <div>
                <label for="from_date" class="form-label">From Date:</label>
                <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <div>
                <label for="to_date" class="form-label">To Date:</label>
                <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i>Search
            </button>
            <a href="{{ route('home') }}" class="btn btn-secondary">
                <i class="bi bi-house-door"></i>Back
            </a>
        </form>

        <!-- Receipt Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped display nowrap" id="receiptTable">
                <thead class="table-dark text-center">
                    <tr>
                        @if(auth()->user()->role == 'Admin')
                            <th>Receipt Number</th>
                        @endif
                        <th>Invoice Number</th>
                        <th>Receipt Type</th>
                        <th>Category</th>
                        <th>Articles</th>
                        <th>Condition</th>
                        <th>Karatage</th>
                        <th>Pawn Weight</th>
                        <th>Total Weight</th>
                        <th>QTY</th>
                        <th>OC</th>
                        <th>BC</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($receipts as $item)
                        <tr>
                            @if(auth()->user()->role == 'Admin')
                                <td>{{ $item->Receipt_Number }}</td>
                            @endif
                            <td>{{ $item->Invoice_Number }}</td>
                            <td>{{ $item->Receipt_Type }}</td>
                            <td>{{ $item->Category }}</td>
                            <td>{{ $item->Articles }}</td>
                            <td>{{ $item->Condition }}</td>
                            <td>{{ $item->Karatage }}</td>
                            <td>{{ number_format($item->Weight, 2) }}</td>
                            <td>{{ number_format($item->Total_Weight, 2) }}</td>
                            <td>{{ $item->QTY }}</td>
                            <td>{{ $item->OC }}</td>
                            <td>{{ $item->BC }}</td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr class="table-secondary text-center">
                        @if(auth()->user()->role == 'Admin')
                            <th colspan="7">Total</th>
                        @else
                            <th colspan="6">Total</th>
                        @endif
                        <th>{{ number_format($pawnWeight, 2) }}</th>
                        <th>{{ number_format($totalWeight, 2) }}</th>
                        <th>{{ number_format($pawnqty, 2) }}</th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Print Button -->
        <div class="print-container text-center">
            <button onclick="printTablefun()" class="btn btn-success">
                <i class="bi bi-printer-fill"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables and Export Scripts -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        $(document).ready(function () {
            $('#receiptTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'pdfHtml5',
                    'print'
                ],
                scrollX: true,
                pageLength: 1000,
                language: {
                    search: "Search records:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });

            // Set today's date to 'to_date' input if not set
            if (!document.getElementById('to_date').value) {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('to_date').value = today;
            }
        });

        function printTablefun() {
            let printContent = document.getElementById("receiptTable").outerHTML;
            let newWin = window.open("");
            newWin.document.write("<html><head><title>Pawning Article Report - Print</title>");
            newWin.document.write("<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'>");
            newWin.document.write("<style>");
            newWin.document.write("body { padding: 20px; font-family: Arial, sans-serif; }");
            newWin.document.write("h2 { text-align: center; color: #2d3748; margin-bottom: 30px; }");
            newWin.document.write("table { width: 100%; border-collapse: collapse; }");
            newWin.document.write("th { background: #2d3748; color: white; padding: 12px 8px; text-align: center; font-weight: 600; border: 1px solid #ddd; }");
            newWin.document.write("td { padding: 10px 8px; text-align: left; border: 1px solid #ddd; }");
            newWin.document.write("tfoot th { background: #48bb78; color: white; font-weight: 700; }");
            newWin.document.write("@media print { .dt-buttons, .dataTables_filter, .dataTables_info, .dataTables_paginate { display: none; } }");
            newWin.document.write("</style>");
            newWin.document.write("</head><body>");
            newWin.document.write("<h2>Pawning Article Report</h2>");
            newWin.document.write(printContent);
            newWin.document.write("</body></html>");
            newWin.print();
            newWin.close();
        }
    </script>
</body>
</html>