<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pawning Receipts - Professional Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #8B9DC3 0%, #B8C5D6 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
        }

        .role-badge {
            position: absolute;
            top: 20px;
            right: 40px;
            background: rgba(255, 255, 255, 0.25);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .controls-section {
            background: #f8f9fa;
            padding: 25px 40px;
            border-bottom: 2px solid #e9ecef;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-size: 11px;
            font-weight: 700;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input[type="date"] {
            padding: 10px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            min-width: 180px;
        }

        .form-group input[type="date"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .table-section {
            padding: 40px;
            overflow-x: auto;
        }

        table.dataTable {
            border-collapse: collapse;
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        table.dataTable thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        table.dataTable thead th {
            padding: 15px 10px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            border: none;
            white-space: nowrap;
            text-align: center;
        }

        table.dataTable tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
            color: #495057;
            white-space: nowrap;
            text-align: center;
        }

        table.dataTable tbody tr {
            transition: all 0.2s ease;
        }

        table.dataTable tbody tr:hover {
            background: #f8f9fa !important;
            transform: scale(1.001);
        }

        table.dataTable tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table.dataTable tfoot {
            background: #f8f9fa;
            font-weight: 600;
        }

        table.dataTable tfoot td {
            padding: 15px 10px;
            border-top: 2px solid #dee2e6;
            font-size: 14px;
            text-align: center;
            font-weight: 700;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #dc3545;
            font-size: 18px;
            font-weight: 500;
        }

        .dataTables_wrapper {
            padding: 20px 0;
        }

        .dataTables_filter input {
            padding: 8px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin-left: 10px;
            font-size: 14px;
        }

        .dataTables_filter input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .dataTables_filter label {
            font-weight: 600;
            color: #495057;
        }

        .dataTables_length select {
            padding: 8px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin: 0 10px;
        }

        .dataTables_length label {
            font-weight: 600;
            color: #495057;
        }

        .dt-buttons {
            margin-bottom: 15px;
        }

        .dt-button {
            padding: 8px 15px !important;
            border: none !important;
            border-radius: 6px !important;
            background: #667eea !important;
            color: white !important;
            margin-right: 8px !important;
            margin-bottom: 8px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
            font-size: 13px !important;
        }

        .dt-button:hover {
            background: #5568d3 !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3) !important;
        }

        /* Color coding for amounts and weights */
        .amount-cell {
            font-weight: 700;
            color: #28a745;
        }

        .weight-cell {
            color: #fd7e14;
            font-weight: 600;
        }

        .ticket-cell {
            font-weight: 700;
            color: #17a2b8;
        }

        /* DataTables pagination styling */
        .dataTables_paginate .paginate_button {
            padding: 5px 12px !important;
            margin: 0 2px !important;
            border-radius: 5px !important;
            border: 1px solid #dee2e6 !important;
            background: white !important;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #667eea !important;
            color: white !important;
            border-color: #667eea !important;
        }

        .dataTables_paginate .paginate_button.current {
            background: #667eea !important;
            color: white !important;
            border-color: #667eea !important;
        }

        @media print {
            body {
                background: white;
            }

            .controls-section, .dt-buttons, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate, .role-badge {
                display: none !important;
            }

            .container {
                box-shadow: none;
            }

            table.dataTable tbody tr:nth-child(even) {
                background: #f0f0f0 !important;
            }
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }

            .form-group {
                width: 100%;
            }

            .table-section {
                padding: 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .role-badge {
                position: static;
                display: inline-block;
                margin-top: 10px;
            }
        }
    </style>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>

<body>
    @if($recipts->count() > 0)
    <div class="container">
        <div class="header">
            <span class="role-badge">
                @if(auth()->user()->role == 'Admin')
                    👑 Admin
                @else
                    👤 Cashier
                @endif
            </span>
            <h1>💎 Pawning Receipts </h1>
        </div>

        <div class="controls-section">
            <form action="" method="get" class="filter-form">
                <div class="form-group">
                    <label for="from_date">From Date</label>
                    <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}">
                </div>

                <div class="form-group">
                    <label for="to_date">To Date</label>
                    <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}">
                </div>

                <button type="submit" class="btn btn-primary">🔍 Search</button>
                <button type="button" onclick="printTablefun()" class="btn btn-success">🖨️ Print</button>
                <a href="{{ route('home') }}" class="btn btn-secondary">🏠 Back</a>
            </form>
        </div>

        <div class="table-section">
            <table class="display nowrap" id="receiptTable" style="width:100%">
                <thead>
                    <tr>
                        @if(auth()->user()->role == 'Admin')
                            <th>NIC</th>
                            <th>NAME</th>
                            <th>ADDRESS</th>
                            <th>PHONE</th>
                            <th>Receipt Number</th>
                        @endif
                        <th>TYPE</th>
                        <th>TICKET #</th>
                        <th>DATE</th>
                        <th>PAWN WT.</th>
                        <th>TOTAL WT.</th>
                        <th>AMOUNT</th>
                        <th>OC</th>
                        <th>BC</th>
                        <th>TICKET TIME</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recipts as $receipts)
                    <tr>
                        @if(auth()->user()->role == 'Admin')
                            <td>{{ $receipts->Customer_NIC }}</td>
                            <td>{{ $receipts->Customer_Name }}</td>
                            <td>{{ $receipts->Customer_Address }}</td>
                            <td>{{ $receipts->Customer_Phone }}</td>
                            <td>{{ $receipts->Receipt_Number }}</td>
                        @endif
                        <td>{{ $receipts->Receipt_Type }}</td>
                        <td class="ticket-cell">{{ $receipts->Invoice_Number }}</td>
                        <td>{{ $receipts->Receipt_Date }}</td>
                        <td class="weight-cell">{{ $receipts->Pawn_Weight }}</td>
                        <td class="weight-cell">{{ $receipts->Total_Weight }}</td>
                        <td class="amount-cell">{{ $receipts->Amount }}</td>
                        <td>{{ $receipts->OC }}</td>
                        <td>{{ $receipts->BC }}</td>
                        <td>{{ $receipts->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        @if(auth()->user()->role == 'Admin')
                            <td colspan="8" style="text-align: right;"><strong>📊 TOTALS:</strong></td>
                        @else
                            <td colspan="4" style="text-align: right;"><strong>📊 TOTALS:</strong></td>
                        @endif
                        <td class="weight-cell"><strong>{{ $pawnWeight }}</strong></td>
                        <td class="weight-cell"><strong>{{ $totalWeight }}</strong></td>
                        <td class="amount-cell"><strong>{{ $totalAmount }}</strong></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    <div class="container">
        <div class="header">
            <span class="role-badge">
                @if(auth()->user()->role == 'Admin')
                    👑 Admin
                @else
                    👤 Cashier
                @endif
            </span>
            <h1>💎 Pawning Receipts Dashboard</h1>
            <p>Professional Receipt Management System</p>
        </div>
        <div class="no-results">
            <p>⚠️ No results found.</p>
            <a href="{{ route('home') }}" class="btn btn-secondary" style="margin-top: 20px;">🏠 Back to Home</a>
        </div>
    </div>
    @endif

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                    {
                        extend: 'copyHtml5',
                        text: '📋 Copy'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '📊 Excel'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '📄 PDF',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    },
                    {
                        extend: 'print',
                        text: '🖨️ Print'
                    }
                ],
                scrollX: true,
                pageLength: 1000,
                lengthMenu: [[25, 50, 100, 500, 1000, -1], [25, 50, 100, 500, 1000, "All"]],
                order: [[{{ auth()->user()->role == 'Admin' ? '5' : '1' }}, 'desc']], // Sort by Ticket # descending
                language: {
                    search: "🔍 Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ receipts",
                    infoEmpty: "No receipts available",
                    infoFiltered: "(filtered from _MAX_ total receipts)",
                    zeroRecords: "No matching receipts found",
                    paginate: {
                        first: "⏮ First",
                        last: "Last ⏭",
                        next: "Next ▶",
                        previous: "◀ Previous"
                    }
                }
            });

            // Set today's date to 'to_date' input if not already set
            const today = new Date().toISOString().split('T')[0];
            if (!document.getElementById('to_date').value) {
                document.getElementById('to_date').value = today;
            }
        });

        function printTablefun() {
            let printContent = document.getElementById("receiptTable").outerHTML;
            let newWin = window.open("");
            newWin.document.write("<html><head><title>Pawning Receipts - Print</title>");
            newWin.document.write("<style>");
            newWin.document.write("@page { size: landscape; margin: 1cm; }");
            newWin.document.write("body { font-family: Arial, sans-serif; padding: 10px; }");
            newWin.document.write("table { border-collapse: collapse; width: 100%; }");
            newWin.document.write("th, td { border: 1px solid #000; padding: 6px; text-align: center; font-size: 10px; }");
            newWin.document.write("th { background: #667eea; color: white; font-weight: bold; }");
            newWin.document.write("tbody tr:nth-child(even) { background: #f0f0f0; }");
            newWin.document.write("tfoot td { background: #f8f9fa; font-weight: bold; border-top: 2px solid #000; }");
            newWin.document.write(".amount-cell { color: #28a745; font-weight: bold; }");
            newWin.document.write(".weight-cell { color: #fd7e14; font-weight: bold; }");
            newWin.document.write(".ticket-cell { color: #17a2b8; font-weight: bold; }");
            newWin.document.write("</style>");
            newWin.document.write("</head><body>");
            newWin.document.write("<h2 style='text-align: center; color: #667eea; margin-bottom: 15px;'>Pawning Receipts Report</h2>");
            newWin.document.write("<p style='text-align: center; margin-bottom: 20px;'>Generated on: " + new Date().toLocaleString() + "</p>");
            newWin.document.write(printContent);
            newWin.document.write("</body></html>");
            newWin.document.close();
            setTimeout(function() {
                newWin.print();
            }, 250);
        }
    </script>
</body>

</html>
