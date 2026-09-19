<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Copy - Pawn Receipt (A5)</title>
    <style>
    /* PAGE SETUP (A5) */
    @page {
        size: A4;
        margin: 10mm;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        color: #000;
        margin: 0;
        padding: 0;
        background: #fff;
    }

    /* WATERMARK */
    .watermark {
        position: fixed;
        top: 45%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 48px;
        font-weight: bold;
        color: rgba(0, 0, 0, 0.08);
        z-index: 0;
        white-space: nowrap;
    }

    /* MAIN CONTAINER */
    .invoice-container {
        position: relative;
        z-index: 1;
        border: 1px solid #000;
        padding: 10px;
    }

    /* HEADER */
    .header {
        text-align: center;
        border-bottom: 1px solid #000;
        padding-bottom: 6px;
        margin-bottom: 8px;
    }

    .header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .header p {
        margin: 2px 0;
        font-size: 11px;
    }

    .header h3 {
        margin-top: 6px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* INFO ROW */
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .label {
        font-weight: bold;
    }

    /* SECTION TITLES */
    .section-title {
        margin: 8px 0 4px;
        padding: 3px 5px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 11px;
        border: 1px solid #000;
        background: #f2f2f2;
    }

    /* CUSTOMER GRID */
    .customer-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px 10px;
        margin-bottom: 6px;
    }

    .customer-item {
        font-size: 11px;
    }

    /* TABLES */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
        font-size: 10.5px;
    }

    table th,
    table td {
        border: 1px solid #000;
        padding: 4px;
    }

    table th {
        text-align: center;
        font-weight: bold;
        background: #eaeaea;
    }

    table td {
        vertical-align: middle;
    }

    /* FINANCIAL TABLE */
    .financial-table {
        margin-top: 6px;
        font-size: 11px;
    }

    .financial-table td {
        border: none;
        padding: 3px 2px;
    }

    .financial-table .label {
        text-align: left;
    }

    .financial-table .amount {
        text-align: right;
        font-weight: bold;
    }

    /* PRINT OPTIMIZATION */
    @media print {
        body {
            margin: 0;
        }
    }
</style>

</head>
<body>
    <div class="watermark">OFFICE COPY</div>

    <div class="invoice-container">
        <div class="header">
            <h2>{{ $companyData->name ?? 'Company Name' }}</h2>
            <p>{{ $branchDetails->address ?? '' }}</p>
            <p>Tel: {{ $branchDetails->fax_number ?? '' }}||{{ $branchDetails->fax_number ?? '' }}</p>
        </div>

        <div class="content">
            @foreach($pawnSumData as $pawn)
            <div class="info-row">
                <div>
                    <span class="label">Receipt No:</span>
                    <span class="value">{{ $pawn->Receipt_Number }}</span>
                </div>
                <div>
                    <span class="label">Date:</span>
                    <span class="value">{{ $pawn->Pawn_Date }}</span>
                </div>
            </div>

            <div class="section-title">CUSTOMER INFORMATION</div>
            <div class="customer-grid">
                <div class="customer-item">
                    <span class="label">Name</span>
                    {{ $pawn->Customer_Name }}
                </div>
                <div class="customer-item">
                    <span class="label">NIC</span>
                    {{ $pawn->Customer_NIC }}
                </div>
                <div class="customer-item">
                    <span class="label">Address</span>
                    {{ $pawn->Customer_Address }}
                </div>
            </div>

            <div class="section-title">ARTICLE DETAILS</div>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Article</th>
                        <th>Cond.</th>
                        <th>Karat</th>
                        <th>Weight</th>
                        <th>QTY</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pawnDetailsData as $detail)
                    <tr>
                        <td>{{ $detail->Category }}</td>
                        <td>{{ $detail->Articles }}</td>
                        <td>{{ $detail->Condition }}</td>
                        <td>{{ $detail->Karatage }}</td>
                        <td>{{ $detail->Weight }}</td>
                        <td>{{ $detail->QTY }}</td>
                        <td style="text-align: right;">{{ number_format($detail->Value, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="section-title">FINANCIAL SUMMARY</div>
   <table class="financial-table">
    <tr>
        <td class="label">Original Pawn Amount:</td>
        <td class="amount">Rs. {{ number_format($pawn->Amount ?? 0, 2) }}</td>
    </tr>

        <tr>
        <td class="label">Paid Interest Amount:</td>
        <td class="amount">Rs. {{ number_format($pawn->RePawning_interest ?? 0, 2) }}</td>
    </tr>

    @endforeach

     @foreach($repawnSumData as $pawn)
    <tr>
        <td class="label">Repawned Loan Amount:</td>
        <td class="amount">Rs. {{ number_format($pawn->Redeem_total ?? 0, 2) }}</td>
    </tr>
   @endforeach

   @foreach($pawnSumData as $pawn)

    <tr>
        <td class="label">Repawn Cash Received:</td>
        <td class="amount">Rs. {{ number_format($pawn->RePawn_get_amount ?? 0, 2) }}</td>
    </tr>

      @endforeach
</table>


        </div>
    </div>
</body>
</html>