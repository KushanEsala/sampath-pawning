<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pawn Redemption Receipt</title>

<style>
    /* RESET */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* A4 PAGE */
    @page {
        size: A4;
        margin: 15mm;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #fff;
        color: #000;
        font-size: 13px;
    }

    /* CONTAINER */
    .container {
        max-width: 190mm;
        margin: auto;
        padding: 10mm;
        border: 1px solid #000;
    }

    /* HEADER */
    .receipt_header {
        text-align: center;
        padding-bottom: 12px;
        margin-bottom: 12px;
        border-bottom: 2px dashed #000;
    }

    .receipt_header h3 span {
        font-size: 24px;
        letter-spacing: 1px;
    }

    .receipt_header h6,
    .receipt_header h5 {
        font-size: 13px;
        margin-top: 4px;
    }

    /* BODY */
    .receipt_body {
        margin-top: 15px;
    }

    /* INFO ROW */
    .row-flex {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    /* CUSTOMER INFO BOX */
    .customer-info {
        border: 1px dashed #000;
        padding: 8px;
        margin: 10px 0;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
    }

    /* TABLE */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    thead th {
        font-size: 14px;
        padding-bottom: 6px;
        border-bottom: 1px solid #000;
    }

    thead th:last-child,
    tbody td:last-child,
    tfoot td:last-child {
        text-align: right;
    }

    tbody td {
        padding: 6px 0;
        border-bottom: 1px dotted #000;
    }

    tfoot td {
        padding: 6px 0;
    }

    tfoot tr:first-child td {
        padding-top: 10px;
    }

    /* TOTAL HIGHLIGHT */
    tfoot tr:last-child td {
        font-size: 16px;
        font-weight: bold;
        border-top: 2px solid #000;
        padding-top: 8px;
    }

    /* FOOTER */
    .thank-you {
        margin-top: 25px;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        border-top: 1px dashed #000;
        padding-top: 10px;
        letter-spacing: 1px;
    }

    /* PRINT */
    @media print {
        body {
            background: #fff;
        }
    }
</style>

</head>

<body>

<div class="container">

    {{-- COMPANY DETAILS --}}
    @foreach ($companyData as $comData)
    <div class="receipt_header">
        <h3><span>{{ $comData->name }}</span></h3>
        <h6>Address : {{ $comData->address }}</h6>
        <h6>Phone : {{ $comData->co_number }} | {{ $comData->fax_number }}</h6>
        <h5>Email : {{ $comData->email }}</h5>
    </div>
    @endforeach

    <div class="receipt_body">

        {{-- REDEMPTION INFO --}}
        @foreach($redeemdata as $sumData)
        <div class="row-flex">
            <div><strong>Redemption No:</strong> {{ $sumData['Receipt_Number'] }}</div>
            <div><strong>Date:</strong> {{ $sumData['Redeem_Date'] }}</div>
        </div>
        @endforeach

        {{-- PAWN INFO --}}
        @foreach($pawnSumData as $sumData)
        <div class="row-flex">
            <div><strong>Stock No:</strong> {{ $sumData['Invoice_Number'] }}</div>
            <div><strong>Pawning Date:</strong> {{ $sumData['Receipt_Date'] }}</div>
        </div>

        <div class="customer-info">
            <div><strong>Name:</strong> {{ $sumData['Customer_Name'] }}</div>
            <div><strong>Address:</strong> {{ $sumData['Customer_Address'] }}</div>
            <div><strong>NIC:</strong> {{ $sumData['Customer_NIC'] }}</div>
            <div><strong>Phone:</strong> {{ $sumData['Customer_Phone'] }}</div>
        </div>
        @endforeach

        {{-- ITEMS TABLE --}}
        <table>
            <thead>
                <tr>
                    <th>Articles</th>
                    <th style="text-align:right">Weight</th>
                    <th style="text-align:right">QTY</th>
                </tr>
            </thead>

            <tbody>
                @foreach($pawnDetailsData as $DetailsData)
                <tr>
                    <td>{{ $DetailsData['Articles'] }}</td>
                    <td style="text-align:right">{{ $DetailsData['Weight'] }}</td>
                    <td style="text-align:right">{{ $DetailsData['QTY'] }}</td>
                </tr>
                @endforeach
            </tbody>

            <tfoot>

                {{-- PAWN ADVANCE --}}
                <tr>
                    <td colspan="2"><strong>Pawning Advance A/C</strong></td>
                    <td>
                        @foreach($redeemdata as $sumData)
                            {{ number_format($sumData['Payable_Pawn_Amount'], 2, '.', ',') }}
                        @endforeach
                    </td>
                </tr>

                @php
                    $total = 0;
                @endphp

                @foreach($redeemdata as $sumData)
                    @php
                        $total += $sumData['Paid_Interest']
                                + $sumData['Stamp_Fee']
                                + $sumData['Postage_Charges'];
                    @endphp
                @endforeach

                @php
                    $documentChargesTotal = $redeemdata->sum('Document_Charges');
                    $old_InterestChargesTotal = $redeemdata->sum('old_Interest');
                    $grandTotal = $total + $documentChargesTotal;
                @endphp

                {{-- INTEREST --}}
                <tr>
                    <td colspan="2"><strong>Interest Received A/C</strong></td>
                    <td>{{ number_format($grandTotal, 2, '.', ',') }}</td>
                </tr>


                <tr>
                    <td colspan="2"><strong>Old System Interest Received A/C</strong></td>
                    <td>{{ number_format($old_InterestChargesTotal, 2, '.', ',') }}</td>
                </tr>

                {{-- PAYABLE --}}
                <tr>
                    <td colspan="2"><strong>Payable Pawn Amount</strong></td>
                    <td>
                        @foreach($redeemdata as $sumData)
                            {{ number_format($sumData['Payable_Total'] + $sumData['Discount'], 2, '.', ',') }}
                        @endforeach
                    </td>
                </tr>

                {{-- DISCOUNT --}}
                <tr>
                    <td colspan="2">Discount</td>
                    <td>
                        @foreach($redeemdata as $sumData)
                            {{ number_format($sumData['Discount'], 2, '.', ',') }}
                        @endforeach
                    </td>
                </tr>

                {{-- TOTAL --}}
                <tr>
                    <td colspan="2"><strong>Total Amount</strong></td>
                    <td>
                        @foreach($redeemdata as $sumData)
                            <strong>{{ number_format($sumData['Payable_Total'], 2, '.', ',') }}</strong>
                        @endforeach
                    </td>
                </tr>

            </tfoot>
        </table>

    </div>

    <div class="thank-you">
        Thank You!
    </div>

</div>

<script>
    window.onload = function () {
        window.print();
    };

    window.onafterprint = function () {
        window.location.href = "{{ route('pawning_redeem') }}";
    };
</script>

</body>
</html>
