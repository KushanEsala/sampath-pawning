<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Letter Print</title>
    <style>
        /* ── Reset ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Body: never lock to a fixed height ── */
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background: #fff;
        }

        /* ══════════════════════════════════════════════════
           Each letter occupies exactly one A5-landscape page.
           MUST be position:relative so the absolute .field
           children are contained inside it.
        ══════════════════════════════════════════════════ */
        .letter-page {
            position: relative;          /* ← KEY FIX #1 */
            width:  21cm;
            height: 27cm;
            overflow: hidden;            /* clip anything that overflows */
            margin: 0 auto 20px;
            border: 1px solid #ccc;      /* visible on screen only */
            page-break-after: always;
            page-break-inside: avoid;
        }
        .letter-page:last-child { page-break-after: auto; }

        /* ── Absolute field positions ── */
        .field {
            position: absolute;
            font-size: 10pt;
            white-space: nowrap;
        }

        /* Fixed fields */
        .customer-name      { left: 12cm; top: 2.3cm;  }
        .customer-address   { left: 12cm; top: 2.8cm;  }
        .ticket-number      { left: 10cm;   top: 2.8cm;  }
        .branch             { left: 10cm;   top: 3.3cm;  }
        .branch-contact     { left: 10cm;   top: 3.6cm;  }
        .cashier            { left: 10cm;   top: 4.2cm;  }
        .market-value       { left: 17.2cm; top: 4.2cm;  }
        .intrest            { left: 17.2cm; top: 3.65cm; }
        .receipt-date       { left: 17.2cm; top: 2.8cm;  }
        .acc                { left: 14cm;   top: 5.2cm;  }

        /* Bottom summary */
        .expiry-date            { left: 16cm;   top: 9cm; }
        .expiry-date-tamil      { left: 16.5cm; top: 11.5cm; }
        .expiry-date-english    { left: 3.4cm;  top: 14cm;}  /* NOTE: exceeds A5 height — adjust if needed */
        .Receipt-Number-two     { left: 2cm;    top: 17.5cm; }
        .Receipt-Amount         { left: 11.5cm;   top: 17.5cm; }
        .Receipt-interest       { left: 16cm;   top: 17.5cm; }
        .Receipt-total-amount   { left: 14.5cm; top: 26cm;}
        .advance-amount         { left: 12cm;   top: 12cm;   }
        .customer-Articles     { left: 6cm;    top: 17.5cm; }


        /* Dynamic item row positions */
        .item-row {
            position: absolute;
            font-size: 10pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .col-article { left: 0.5cm;   width: 3.3cm; max-width: 3.3cm; }
        .col-desc    { left: 4cm;     width: 4.3cm; max-width: 4.3cm; }
        .col-net     { left: 8.5cm;   }
        .col-gold    { left: 11.7cm;  }
        .col-karat   { left: 14.8cm;  }
        .col-int     { left: 17.6cm;  }

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

        /* ── Print: hide button bar, keep pages clean ── */
        @media print {
            .print-bar { display: none !important; }
            .letter-page {
                border: none;
                margin: 0;
            }
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

    @foreach ($letters as $item)
        @php
            $receipt  = $item['receipt'];
            $interest = $item['interest'];
            $lno      = $item['letter_no'];
           $pawnDetails = $item['pawnDetails'];
        @endphp

        <div class="letter-page">

            {{-- ── Customer / receipt fields ── --}}
            <div class="field customer-name">{{ $receipt->Customer_Name }}</div>
            <div class="field customer-address">{{ $receipt->Customer_Address }}</div>
            <div class="field expiry-date">{{ $receipt->Final_date }}</div>
            <div class="field expiry-date-tamil">{{ $receipt->Final_date }}</div>
            <div class="field expiry-date-english">{{ $receipt->Final_date }}</div>
            <div class="field Receipt-Number-two">{{ $receipt->Receipt_Number }}</div>
            <div class="field Receipt-Amount">{{ number_format($receipt->RePawning_amount, 2) }}</div>
            <div class="field Receipt-interest">{{ number_format($receipt->Interest, 2) }}</div>
            <div class="field Receipt-total-amount">{{ number_format($receipt->Interest, 2) }}</div>


        </div>
    @endforeach


            <div class="field customer-Articles">
                @foreach ($pawnDetails as $detail)
                    {{ $detail->Articles }}@if (!$loop->last), @endif
                @endforeach
            </div>

    <script>
        // Auto-trigger browser print dialog when the tab opens
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>