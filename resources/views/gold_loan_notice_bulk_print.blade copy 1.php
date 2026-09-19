<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Letter Print</title>
    <style>
        /* ── Reset ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 13px; background: #fff; }

        /* ── Each letter occupies its own printed page ── */
        .letter-page {
            width: 210mm;
            min-height: 297mm;
            padding: 18mm 20mm;
            margin: 0 auto 20px;
            border: 1px solid #ccc; /* visible on screen only */
            page-break-after: always;
        }
        .letter-page:last-child { page-break-after: auto; }

        /* ── Company header ── */
        .company-header { text-align: center; margin-bottom: 20px; }
        .company-header h2 { font-size: 18px; text-transform: uppercase; }
        .company-header p  { font-size: 12px; color: #555; }

        /* ── Letter title badge ── */
        .letter-title {
            display: inline-block;
            background: #1a3c5e;
            color: #fff;
            padding: 4px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 16px;
        }

        /* ── Address / info table ── */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .info-table td { padding: 4px 8px; }
        .info-table td:first-child { width: 140px; font-weight: bold; color: #333; }

        /* ── Body paragraph ── */
        .letter-body { line-height: 1.7; margin-bottom: 18px; text-align: justify; }
        .highlight { font-weight: bold; color: #b00; }

        /* ── Amount box ── */
        .amount-box {
            border: 2px solid #1a3c5e;
            border-radius: 6px;
            padding: 10px 16px;
            display: inline-block;
            margin-bottom: 18px;
            font-size: 15px;
        }
        .amount-box span { font-weight: bold; color: #1a3c5e; font-size: 17px; }

        /* ── Signature section ── */
        .signature { margin-top: 40px; }
        .signature p { margin-bottom: 6px; }
        .sig-line { border-top: 1px solid #333; width: 200px; margin-top: 30px; }

        /* ── Screen-only print button ── */
        .print-bar {
            text-align: center;
            padding: 14px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .print-bar button {
            background: #1a3c5e;
            color: #fff;
            border: none;
            padding: 9px 28px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-bar button:hover { background: #265a8a; }
        .badge-count {
            display: inline-block;
            background: #28a745;
            color: #fff;
            border-radius: 12px;
            padding: 2px 10px;
            font-size: 13px;
            margin-left: 10px;
        }

        /* ── Print media: hide the button bar ── */
        @media print {
            .print-bar { display: none; }
            .letter-page { border: none; margin: 0; }
        }
    </style>
</head>
<body>

    {{-- ── Print button (screen only) ── --}}
    <div class="print-bar">
        <button onclick="window.print()">
            🖨 Print All Letters
        </button>
        <span class="badge-count">{{ count($letters) }} letter(s)</span>
    </div>

    @php
        $letterOrdinal = ['', '1st', '2nd', '3rd'];
        $letterColor   = ['', '#28a745', '#007bff', '#dc3545'];
    @endphp

    @foreach ($letters as $item)
        @php
            $receipt  = $item['receipt'];
            $interest = $item['interest'];
            $lno      = $item['letter_no'];
        @endphp

        <div class="letter-page">

            {{-- Company header --}}
            <div class="company-header">
                @if($company)
                    <h2>{{ $company->Company_Name }}</h2>
                    <p>{{ $company->Company_Address }}</p>
                    <p>Tel: {{ $company->Company_Phone }}
                        @if($company->Company_Email) &nbsp;|&nbsp; {{ $company->Company_Email }} @endif
                    </p>
                @endif
                <hr style="margin:10px 0; border-color:#1a3c5e;">
            </div>

            {{-- Letter badge --}}
            <div class="letter-title"
                 style="background:{{ $letterColor[$lno] ?? '#1a3c5e' }};">
                {{ $letterOrdinal[$lno] ?? $lno . 'th' }} Reminder Notice
            </div>

            <p style="text-align:right; margin-bottom:14px;">
                Date: <strong>{{ now()->format('Y-m-d') }}</strong>
            </p>

            {{-- Customer info --}}
            <table class="info-table">
                <tr><td>Name</td><td>: {{ $receipt->Customer_Name }}</td></tr>
                <tr><td>NIC</td><td>: {{ $receipt->Customer_NIC }}</td></tr>
                <tr><td>Address</td><td>: {{ $receipt->Customer_Address }}</td></tr>
                <tr><td>Phone</td><td>: {{ $receipt->Customer_Phone }}</td></tr>
                <tr><td>Receipt No</td><td>: {{ $receipt->Receipt_Number }}</td></tr>
                <tr><td>Receipt Type</td><td>: {{ $receipt->Receipt_Type }}</td></tr>
                <tr><td>Pledge Date</td><td>: {{ $receipt->Receipt_Date }}</td></tr>
                <tr><td>Maturity Date</td><td>: {{ $receipt->Final_date }}</td></tr>
            </table>

            {{-- Body --}}
            <div class="letter-body">
                <p>Dear <strong>{{ $receipt->Customer_Name }}</strong>,</p>
                <br>
                @if($lno == 1)
                <p>
                    This is a <strong>first reminder</strong> to inform you that your gold loan pledge
                    under receipt <strong>{{ $receipt->Receipt_Number }}</strong> has passed its maturity
                    date of <span class="highlight">{{ $receipt->Final_date }}</span>.
                    We kindly request you to settle the outstanding balance at your earliest convenience
                    to avoid further charges.
                </p>
                @elseif($lno == 2)
                <p>
                    This is a <strong>second reminder</strong> regarding your gold loan pledge
                    under receipt <strong>{{ $receipt->Receipt_Number }}</strong>, which became overdue
                    on <span class="highlight">{{ $receipt->Final_date }}</span>.
                    Despite our earlier notice, the matter remains unresolved. Please settle your
                    account <strong>immediately</strong> to prevent legal proceedings.
                </p>
                @else
                <p>
                    This is a <strong>final notice</strong> for your gold loan pledge
                    under receipt <strong>{{ $receipt->Receipt_Number }}</strong>, overdue since
                    <span class="highlight">{{ $receipt->Final_date }}</span>.
                    <strong>Failure to settle within 7 days will result in forfeiture</strong>
                    of the pledged items as per the loan agreement.
                </p>
                @endif
            </div>

            {{-- Amount --}}
            <div class="amount-box">
                Outstanding Amount &amp; Charges:&nbsp;
                <span>Rs. {{ number_format($receipt->Amount + $interest, 2) }}</span>
                &nbsp;
                <small style="color:#888;">(Principal: Rs.{{ number_format($receipt->Amount,2) }}
                 + Charges: Rs.{{ number_format($interest,2) }})</small>
            </div>

            {{-- Signature --}}
            <div class="signature">
                <p>Yours faithfully,</p>
                <div class="sig-line"></div>
                <p><strong>{{ $company->Company_Name ?? '' }}</strong></p>
                <p>Authorised Signatory</p>
            </div>

        </div>{{-- end .letter-page --}}
    @endforeach

    <script>
        // Auto-trigger the browser print dialog when the tab opens
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
