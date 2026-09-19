<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pawn Receipt</title>

    <style>
        /* RESET */
        * {
             margin-top: 5px;
            padding: 0;
             margin-left: 30;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            width: 18cm;
            height: 15cm;
            margin: 10;
            padding: 0;
            background: #ffffff;
            color: #000;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin: 0;
        }

        td, th {
            padding: 5px;
            vertical-align: top;
        }

       .fullborder {
    width: 18cm;
    margin: 10px auto;
}


        .fullborder td {
            border: 1px solid #333;
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 12px;
        }

        .padded {
            padding-top: 20px;
        }

        .spacer {
            height: 15px;
        }
    </style>
</head>

<body>

@php
    $rate1 = $T_Receipt_Type->rate1 ?? 0;
    $rate2 = $T_Receipt_Type->rate2 ?? 0;
     $rate3 = $T_Receipt_Type->rate3 ?? 0;
@endphp
<table style="table-layout:fixed; width:100%; border-collapse:collapse;">
    <tr>
        <td class="fw-bold text-center"
            style="font-size:8px; width:4cm; ">
        </td>

        <td class="fw-bold"
            style="font-size:25px; width:9cm; text-align:left;font-weight: bold">
            @foreach($pawnSumData as $sumData)
                  {{ $sumData['Invoice_Number'] }}
            @endforeach
        </td>

        <td  colspan="2" class="fw-bold text-left"
            style="font-size:25px; width:4cm; font-weight: bold ">
            @foreach($pawnSumData as $sumData)
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $sumData['Invoice_Number'] }}
            @endforeach
        </td>

    </tr>
</table>


<table class="fullborder">
    <tbody>

        <tr>
            <td class="text-center fw-bold" style="width:20%; font-size:18px;">
            </td>


            <td colspan="2" class="text-center" style="width:60%" style="align-items: center">
                <div class="bold">{{ $companyData['name'] ?? '' }}</div>
                <div class="small">{{ $branchDetails['name'] ?? '' }}</div>
            </td>

           <td class="text-center fw-bold" style="width:20%; font-size:18px;">
            </td>
        </tr>

        <tr>
            <td class="bold">Cashier</td>
            <td class="text-right">
                @foreach($pawnSumData as $sumData)
                    {{ $sumData['OC'] }}
                @endforeach
            </td>
            <td class="bold small">Interest Rate</td>
            <td class="text-right">{{ $rate3}}%
            </td>
        </tr>

        @php
            $currentTime = \Carbon\Carbon::now('Asia/Kolkata')->format('h:i A');
        @endphp

        <tr>
            <td class="bold">Date</td>
            <td class="text-right small">
                @foreach($pawnSumData as $sumData)
                    {{ $sumData['Receipt_Date'] }} {{ $currentTime }}
                @endforeach
            </td>
            <td class="bold small">Amount (Rs)</td>
            <td class="text-right">
                @foreach($pawnSumData as $sumData)
                    {{ $sumData['RePawning_amount'] }}
                @endforeach
            </td>
        </tr>

        <tr>
            <td class="bold small">NIC No</td>
            <td class="text-right">
                @foreach($pawnSumData as $sumData)
                    {{ $sumData['Customer_NIC'] }}
                @endforeach
            </td>
            <td class="bold small">Total Weight (g)</td>
            <td class="text-right">
                @foreach($pawnSumData as $sumData)
                    {{ $sumData['Total_Weight'] }}
                @endforeach
            </td>
        </tr>

        <tr>
            <td class="bold small" colspan="2">Monthly Interest : {{ number_format(($sumData['RePawning_amount']/100)*$rate3,2) }}</td>
            <td class="bold small">Pawn Weight (g)</td>
            <td class="text-right">
                 @foreach($pawnSumData as $sumData)
                    {{ $sumData['Pawn_Weight'] }}
                @endforeach
            </td>
        </tr>

        @foreach($pawnSumData as $sumData)
        <tr>
            <td colspan="2" class="bold small">
                10 Days Rate: {{ number_format(($sumData['RePawning_amount']/100)*$rate1,2) }}
            </td>
            <td class="bold small"></td>
            <td class="text-right">
            </td>
        </tr>
        @endforeach

        <tr>
            <td class="bold small">Name</td>
            <td colspan="2" class="bold small">Items Mortgaged</td>
            <td class="bold small text-center">Karats</td>
        </tr>

        <tr>
            <td class="small">
                @foreach($pawnSumData as $sumData)
                    {{ $sumData['First_name'] }}<br>
                @endforeach
            </td>

            <td colspan="2" class="small">
                @foreach($pawnDetailsData as $d)
                    {{ $d['Articles'] }} | Qty: {{ $d['QTY'] }} |
                    {{ $d['Condition'] }} | {{ $d['Weight'] }}<br>
                @endforeach
            </td>

            <td class="text-center small">
                @foreach($pawnDetailsData as $d)
                    {{ $d['Karatage'] }}<br>
                @endforeach
            </td>
        </tr>

        <tr>
            <td class="bold small">Duration</td>
            <td class="text-right">
                @foreach($pawnSumData as $sumData)
                    {{ $sumData['Valid_Period'] }} Months
                @endforeach
            </td>
            <td colspan="2"></td>
        </tr>

        <tr>
            <td class="bold small">Due Date</td>
            <td class="text-right padded">
                @foreach($pawnSumData as $sumData)
                    @php
                        $d = new DateTime($sumData['Receipt_Date']);
                        $d->modify("+{$sumData['Valid_Period']} months");
                    @endphp
                    {{ $d->format('Y-m-d') }}
                @endforeach
            </td>
            <td class="bold small text-right">Pawning Advance</td>
            <td class="text-right padded">
                @foreach($pawnSumData as $sumData)
                    {{ $sumData['RePawning_amount'] }}
                @endforeach
            </td>
        </tr>
    </tbody>
</table>

<div class="spacer"></div>

<table>
    <tr>
        <td class="text-center">....................................</td>
        <td></td>
        <td class="text-center">....................................</td>
        <td></td>
        <td class="text-center">....................................</td>
    </tr>
    <tr>
        <td class="text-center">Mortgagor</td>
        <td></td>
        <td class="text-center">Valuer</td>
        <td></td>
        <td class="text-center">Authorized Officer</td>
    </tr>
</table>

</body>
</html>
