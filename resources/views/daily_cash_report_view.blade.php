<!DOCTYPE html>
<html>
<head>
    <title>Daily Cash Account Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-header h2 {
            margin: 5px 0;
            font-size: 18px;
        }
        .report-info {
            margin-bottom: 20px;
        }
        .report-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
            border: 1px solid #ddd;
        }
        table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .beginning-balance {
            background-color: #e3f2fd;
            font-weight: bold;
        }
        .period-totals {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .ending-balance {
            background-color: #d4edda;
            font-weight: bold;
        }
        .filter-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-form label {
            margin-right: 10px;
            font-weight: bold;
        }
        .filter-form input {
            padding: 5px;
            margin-right: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .filter-form button {
            padding: 6px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .filter-form button:hover {
            background-color: #45a049;
        }
        .export-btn {
            background-color: #2196F3;
            margin-left: 10px;
        }
        .export-btn:hover {
            background-color: #0b7dda;
        }
        @media print {
            .filter-form, .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h2>Daily Cash A/C Report</h2>
    </div>

    <div class="filter-form no-print">
        <form action="{{ route('daily-cash-report.generate') }}" method="GET">
            <label for="date_from">Date From:</label>
            <input type="date" name="date_from" value="{{ $dateFrom ?? date('Y-m-01') }}" required>

            <label for="date_to">Date To:</label>
            <input type="date" name="date_to" value="{{ $dateTo ?? date('Y-m-d') }}" required>

            <button type="submit">Generate Report</button>
            <button type="button" class="export-btn" onclick="window.print()">Print Report</button>
            <a href="{{ route('daily-cash-report.export') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}">
                <button type="button" class="export-btn">Export to Excel</button>
            </a>
        </form>
    </div>

    @if(isset($reportData))
    <div class="report-info">
        <p><strong>303003 - Daily Cash Account Report</strong></p>
        <p><strong>Date From:</strong> {{ date('Y-m-d', strtotime($dateFrom)) }} <strong>Date To:</strong> {{ date('Y-m-d', strtotime($dateTo)) }}</p>
        <p><strong>Branch:</strong> {{auth()->user()->Branch }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Transaction Date & Time</th>
                <th>Date</th>
                <th>No</th>
                <th>Transaction</th>
                <th>Description</th>
                <th class="text-right">Dr Amount</th>
                <th class="text-right">Cr Amount</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            <!-- Beginning Balance Row -->
            <tr class="beginning-balance">
                <td colspan="5"><strong>Beginning Balance</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right"><strong>{{ number_format($beginningBalance, 2) }}</strong></td>
            </tr>

            <!-- Transaction Rows -->
            @foreach($reportData as $row)
            <tr>
                <td>{{ $row['created_at'] }}</td>
                <td>{{ date('d/m/Y', strtotime($row['date'])) }}</td>
                <td>{{ $row['no'] }}</td>
                <td>
                    {{ $row['transaction'] }}
                    @if(!empty($row['Invoice_no']))
                        <span style="font-size: 14px; color: #348edd;">
                            ({{ $row['Invoice_no'] }})
                        </span>
                    @endif
                </td>

                <td>{{ $row['description'] }}</td>
                <td class="text-right">{{ $row['dr_amount'] }}</td>
                <td class="text-right">{{ $row['cr_amount'] }}</td>
                <td class="text-right">{{ $row['balance'] }}</td>
            </tr>
            @endforeach

            <!-- Period Balance Row -->
            <tr class="period-totals">
                <td colspan="5"><strong>Period Balance</strong></td>
                <td class="text-right"><strong>{{ $periodTotals['total_dr'] }}</strong></td>
                <td class="text-right"><strong>{{ $periodTotals['total_cr'] }}</strong></td>
                <td class="text-right">-</td>
            </tr>

            <!-- Ending Balance Row -->
            <tr class="ending-balance">
                <td colspan="5"><strong>Ending Balance</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right"><strong>{{ $periodTotals['ending_balance'] }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div style="text-align: center; font-size: 10px; color: #666; margin-top: 30px;">
        <p>© {{ date('Y') }} Pawn Management System. All rights reserved</p>
    </div>
    @else
    <div style="text-align: center; padding: 50px;">
        <p>Please select a date range and click "Generate Report" to view the Daily Cash Account Report.</p>
    </div>
    @endif
</body>
</html>