<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            background: #f4f6f8;
            padding: 10px;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .invoice-title {
            font-size: 26px;
            color: #1f4fd8;
            font-weight: bold;
        }

        /* INFO TABLE */
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px;
            vertical-align: top;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
        }

        /* ITEMS TABLE */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.items thead th {
            background: #1f4fd8;
            color: #fff;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        table.items tbody td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        table.items th:last-child,
        table.items td:last-child {
            text-align: right;
        }

        /* TOTAL BOX */
        .totals {
            width: 40%;
            float: right;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .totals td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .totals tr.total td {
            background: #e6efff;
            font-weight: bold;
        }

        /* FOOTER */
        .footer {
            clear: both;
            margin-top: 40px;
            font-size: 10px;
            color: #555;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
        }
    </style>
    {{-- HEADER --}}
<style>
    .header {
        text-align: center;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        margin-bottom: 15px;
        font-family: Arial, sans-serif;
    }

    .company-details strong {
        font-size: 20px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .company-details {
        font-size: 13px;
        line-height: 1.6;
    }

    .branch-name {
        font-weight: bold;
        margin-top: 5px;
    }

    .contact {
        font-size: 12px;
        margin-top: 4px;
    }
</style>

</head>
<body>

<div class="invoice-box">

    {{-- HEADER --}}
<div class="header">
    <div class="company-details">
        @foreach($companyData as $company)
            <strong>{{ $company->name }}</strong><br>
        @endforeach

        @foreach($branchDetails as $branch)
            Branch : {{ $branch->name }}<br>
            {{ $branch->address }}<br>
            Phone: {{ $branch->contact1 }}
        @endforeach
    </div>
</div>


    {{-- BILL INFO --}}
    @foreach($redeemdata as $redeem)
    <table class="info-table">
        <tr>
            <td width="50%">
                <div class="info-box">
                    <strong>Bill To</strong><br>
                    {{ $pawnSumData[0]['Customer_Name'] }}<br>
                    {{ $pawnSumData[0]['Customer_Address'] }}<br>
                    {{ $pawnSumData[0]['Customer_Phone'] }}
                </div>
            </td>

            <td width="50%">
                <div class="info-box">
                    <strong>PartPayment No:</strong> {{ $redeem['Receipt_Number'] }}<br>
                    <strong>Date:</strong> {{ $redeem['Redeem_Date'] }}<br>
                    <strong>Pawning No:</strong> {{ $pawnSumData[0]['Receipt_Number'] }}<br>
                    <strong>Pawning Final Date:</strong> {{ $pawnSumData[0]['Final_date'] }}
                </div>
            </td>
        </tr>
    </table>
    @endforeach

    {{-- ITEMS --}}
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Description</th>
                <th>Qty</th>
                <th>Weight</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pawnDetailsData as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['Articles'] }}</td>
                <td>{{ $item['QTY'] }}</td>
                <td>{{ $item['Weight'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTALS --}}
    @foreach($redeemdata as $redeem)
    <table class="totals">
         <tr>
            <td>Pawn Capital </td>
            <td>Rs. {{ number_format($redeem['current_pawn_amount'],2) }}</td>
        </tr>
        <tr>
            <td>Postage Charges</td>
            <td>Rs. {{ number_format($redeem['Postage_Charges'],2) }}</td>
        </tr>

        <tr>
            <td>Interest <small>({{ $interestDays }} days)</small></td>
            <td>Rs. {{ number_format($redeem['Paid_Interest'],2) }}</td>
        </tr>

        <tr>
            <td>Total Amount</td>
            <td>Rs. {{ number_format($redeem['paid_cap_amount'], 2) }}</td>
        </tr>

        <tr class="total">
            <td>Total Paid</td>
            <td>Rs. {{ number_format($redeem['Payable_Total'],2) }}</td>
        </tr>
        <tr class="total">
            <td>Balance Capital</td>
            <td>Rs. {{ number_format($redeem['paid_cap_amount'] - $redeem['Payable_Total'], 2) }}</td>
        </tr>
    </table>
    @endforeach

</div>

    <div class="footer">
        <strong>Terms & Conditions</strong><br>
        Payment is due upon receipt of this invoice.<br>
        This is a computer generated invoice.
    </div>

</body>
</html>
