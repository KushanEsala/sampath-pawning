<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Stock Report - Professional Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #9eabe6 0%, #e3e3e4 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .admin-notice {
            background: #fff3cd;
            padding: 10px 40px;
            border-bottom: 2px solid #ffc107;
            text-align: center;
            color: #856404;
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
            font-size: 13px;
            font-weight: 600;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
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
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        th {
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            border: none;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
            color: #495057;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }

        tbody tr.checked-row {
            background: #d1ecf1 !important;
            border-left: 4px solid #17a2b8;
        }

        tfoot {
            background: #f8f9fa;
            font-weight: 600;
        }

        tfoot td {
            padding: 15px;
            border-top: 2px solid #dee2e6;
            font-size: 15px;
        }

        .checkbox-cell {
            text-align: center;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .dataTables_wrapper {
            padding: 20px 0;
        }

        .dataTables_filter input {
            padding: 8px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin-left: 10px;
        }

        .dataTables_filter input:focus {
            outline: none;
            border-color: #667eea;
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
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
        }

        .dt-button:hover {
            background: #5568d3 !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3) !important;
        }

        @media print {
            body {
                background: white;
            }

            .controls-section, .dt-buttons, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate {
                display: none !important;
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
                overflow-x: auto;
            }
        }
    </style>

    <!-- Include jQuery and DataTables CSS and JS libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Stock Report Management</h1>
            <p>Professional Receipt Tracking System</p>
        </div>

        @if(auth()->check() && auth()->user()->is_admin)
        <div class="admin-notice">
            <strong>🔒 Admin View</strong> - Customer records visible
        </div>
        @endif

        <div class="controls-section">
            <form action="" method="get" class="filter-form">
                <div class="form-group">
                    <label for="from_date">From Date</label>
                    <input type="date" name="from_date" id="from_date">
                </div>

                <div class="form-group">
                    <label for="to_date">To Date</label>
                    <input type="date" name="to_date" id="to_date">
                </div>

                <button type="submit" class="btn btn-primary">🔍 Search</button>
                <button type="button" onclick="printTablefun()" class="btn btn-secondary">🖨️ Print</button>
                <button type="button" class="btn btn-success"><a href="{{ route('home') }}" style="color: white; text-decoration: none;">🏠 Back</a></button>
                <button type="button" id="resetCheckboxStock" class="btn btn-danger">🔄 Reset All</button>
            </form>
        </div>

        <div class="table-section">
            <table class="table" id="t_pawn_sums">
                <thead>
                    <tr>
                        <th>Receipt Number</th>
                        <th>Check</th>
                        @if(auth()->user()->role == 'Admin')
                        <th>Customer NIC</th>
                        <th>Customer Name</th>
                        <th>Customer Address</th>
                        <th>Customer Phone</th>
                        @endif
                        <th>Receipt Type</th>
                        <th>Receipt Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recipts as $key => $receipt)
                        <tr class="{{ $receipt->checkbox_stock == 1 ? 'checked-row' : '' }}">
                            <td>{{ $receipt->Invoice_Number }}</td>
                            <td class="checkbox-cell">
                                <input
                                    class="custom-checkbox save-checkbox"
                                    type="checkbox"
                                    data-receipt_number="{{ $receipt->Receipt_Number }}"
                                    id="flexCheckDefault{{ $receipt->Receipt_Number }}"
                                    {{ $receipt->checkbox_stock == 1 ? 'checked' : '' }}
                                >
                            </td>
                            @if(auth()->user()->role == 'Admin')
                            <td>{{ $receipt->Customer_NIC }}</td>
                            <td>{{ $receipt->Customer_Name }}</td>
                            <td>{{ $receipt->Customer_Address }}</td>
                            <td>{{ $receipt->Customer_Phone }}</td>
                            @endif
                            <td>{{ $receipt->Receipt_Type }}</td>
                            <td>{{ $receipt->Receipt_Date }}</td>
                            <td><strong>{{ $receipt->Amount }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="2"><strong>📋 Total Count: {{ $pawnCount }}</strong></td>
                        <td colspan="{{ auth()->user()->role == 'Admin' ? '6' : '2' }}" style="text-align: right;"><strong>Grand Total:</strong></td>
                        <td><strong>{{ $amount }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        function printTablefun() {
            var divToPrint = document.getElementById("t_pawn_sums");
            var newWin = window.open("");
            newWin.document.write('<html><head><title>Stock Report</title>');
            newWin.document.write('<style>table{border-collapse:collapse;width:100%;}th,td{border:1px solid #000;padding:8px;}th{background:#667eea;color:white;}</style>');
            newWin.document.write('</head><body>');
            newWin.document.write(divToPrint.outerHTML);
            newWin.document.write('</body></html>');
            newWin.document.close();
            newWin.print();
        }

        $(document).ready(function() {
            $('#t_pawn_sums').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'excel', 'csv', 'pdf', 'print'
                ],
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                pageLength: -1
            });

            // Checkbox change handler
            $('.save-checkbox').on('change', function() {
                var checkbox = $(this);
                var isChecked = checkbox.is(':checked') ? 1 : 0;
                var recordId = checkbox.data('receipt_number');

                // Toggle row class
                if (isChecked) {
                    checkbox.closest('tr').addClass('checked-row');
                } else {
                    checkbox.closest('tr').removeClass('checked-row');
                }

                // Send the data via AJAX
                $.ajax({
                    url: '/update-checkbox',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        Receipt_Number: recordId,
                        checked: isChecked
                    },
                });
            });

            // Reset all checkboxes
            $('#resetCheckboxStock').on('click', function() {
                if (confirm('Are you sure you want to reset all checkboxes?')) {
                    $.ajax({
                        url: '/reset-checkbox-stock',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            alert('All checkbox stocks have been reset successfully!');
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            alert('Error resetting checkbox stock. Please try again.');
                        }
                    });
                }
            });

            // Set default to_date to today
            var dateObj = new Date();
            document.getElementById('to_date').value = dateObj.toISOString().slice(0, 10);
        });
    </script>
</body>

</html>