<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $pawnSumData->first()->Invoice_no ?? '' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            margin: 0;
            background-color: #f5f7fa;
            color: #333;
        }

        .invoice-box {
            max-width: 850px;
            margin: 30px auto;
            background: #fff;
            padding: 40px 50px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .logo img {
            width: 120px;
            margin-bottom: 10px;
        }

        .company-info {
            font-size: 14px;
            color: #444;
            line-height: 1.6;
        }

        .balance {
            text-align: right;
            font-size: 16px;
            color: #111;
        }

        .invoice-title {
            font-size: 30px;
            font-weight: bold;
            color: #00a3c8;
            margin: 30px 0 15px;
        }

        .info-table {
            font-size: 14px;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 4px 0;
        }

        .table-box {
            margin-top: 20px;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background-color: #00a3c8;
            color: white;
            text-align: left;
            padding: 12px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f1f1f1;
        }

        .summary {
            width: 300px;
            float: right;
            margin-top: 30px;
        }

        .summary td {
            padding: 10px;
        }

        .summary .total {
            background-color: #00a3c8;
            color: #fff;
            font-weight: bold;
            font-size: 16px;
        }

        .signature {
            margin-top: 70px;
            text-align: right;
            font-size: 14px;
        }

        .signature .line {
            margin-top: 40px;
            border-top: 1px solid #888;
            width: 200px;
            margin-left: auto;
        }

        .footer {
            clear: both;
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }

    </style>
</head>
<body>
<div class="invoice-box">
    <div class="top-bar">
        <div class="logo">
            <img src="{{ public_path('images/logo3.jpg') }}" alt="Logo New"/>
            <div class="company-info">
                @foreach($companyData as $sumData)
                    <strong>{{ $sumData['name'] }}</strong><br>
                    {{ $sumData['address'] }}<br>
                    {{ $sumData['co_number'] }}<br>
                    Email: {{ $sumData['email'] }}<br>
                    A/C 101010001477 HNB
                @endforeach
            </div>
        </div>
    </div>

    <div class="invoice-title"> SALES INVOICE</div>

    <table class="info-table">
        <tr>
            <td><strong>Invoice No:</strong> {{ $pawnSumData->first()->Invoice_no ?? '' }}</td>
            <td><strong>Issue Date:</strong> {{ $pawnSumData->first()->Invoice_date ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($pawnSumData->first()->Invoice_date)->addDays(30)->format('Y-m-d') }}</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td><strong>Invoice To:</strong> {{ $customerData->first()->First_name ?? 'Walk-in Customer' }}</td>
        </tr>
        <tr>
            <td>{{ $pawnSumData->first()->Customer_Phone ?? '' }}</td>
        </tr>
        <tr>
            <td>{{ $pawnSumData->first()->Customer_Address ?? '' }}</td>
        </tr>
    </table>

    <div class="table-box">
        <table>
            <thead>
            <tr>
                <th>Description</th>
                <th>Unit Price</th>
                <th>Weight</th>
                <th>Karatage</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($pawnDetailsData as $item)
                <tr>
                    <td>{{ $item->Item_description }}</td>
                    <td>{{ number_format($item->Unit_price, 2) }}</td>
                    <td>{{ $item->Total_Weight}}</td>
                    <td>{{ $item->Make}}</td>
                    <td>{{ $item->QTY }}</td>
                    <td>{{ number_format($item->Net_value, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

<div class="summary">
    <table>
        <tr>
            <td>Subtotal:</td>
            <td>{{ number_format($pawnSumData->first()->Gross_Amount ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Discount:</td>
            <td>{{ number_format($pawnSumData->first()->Discount ?? 0, 2) }}</td>
        </tr>
        <tr class="total">
            <td>Total:</td>
            <td>{{ number_format($pawnSumData->first()->Net_Amount ?? 0, 2) }}</td>
        </tr>
    </table>
</div>
    <div class="footer">
        Thank you for your business!<br>
    </div>
</div>
</body>
</html>