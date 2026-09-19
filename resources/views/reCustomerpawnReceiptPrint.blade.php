<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pawn Ticket</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            width: 21cm;
            height: 14.85cm;
            position: relative;
        }

        .field {
            position: absolute;
            font-size: 10pt;
        }

        /* Fixed fields */
        .customer-name { left: 0.5cm; top: 4cm; }
        .customer-address { left: 0.5cm; top: 4.5cm; }
        .ticket-number { left: 10cm; top: 2.8cm; }
        .branch { left: 10cm; top: 3.3cm; }
        .branch-contact { left: 10cm; top: 3.6cm; }
        .cashier { left: 10cm; top: 4.2cm; }
        .market-value { left: 17.2cm; top: 4.2cm; }
        .intrest { left: 17.2cm; top: 3.65cm; }
        .receipt-date { left: 17.2cm; top: 2.8cm; }
        .acc {left: 14cm; top: 5.2cm;}

        /* Bottom summary */
        .expiry-date { left: 1.5cm; top: 12cm; color: #b61717;font-weight: bold; }
        .duration-month { left: 5.5cm; top: 12cm; }
        .interest-10days { left: 9cm; top: 12cm; }
        .advance-amount { left: 12cm; top: 12cm; color: #53924b;font-weight: bold; }

        /* Dynamic item row positions */
        .item-row {
            position: absolute;
            font-size: 10pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .col-article { left: 0.5cm; width: 3.3cm; max-width: 3.3cm; }
        .col-desc { left: 4cm; width: 4.3cm; max-width: 4.3cm; }
        .col-net { left: 8.5cm; }
        .col-gold { left: 11.7cm; }
        .col-karat { left: 14.8cm; }
        .col-int { left: 17.6cm; }
    </style>
</head>

<body>

@php
    $currentTimeUTC = \Carbon\Carbon::now('UTC');
    $currentTimeFormatted = $currentTimeUTC->setTimezone('Asia/Kolkata')->format('h:i A');

    $oneMonthInterestPercentage = !empty($pawnSumData)
        ? $pawnSumData[0]['Interest_Rate']
        : 0;

    // Get rate1 from receipt type
    $rate1 = $T_Receipt_Type->rate1 ?? 0;
    $rate2 = $T_Receipt_Type->rate2 ?? 0;
    $rate3 = $T_Receipt_Type->rate3 ?? 0;
    $receiptname = $T_Receipt_Type->receiptname ?? 0;

    // ✅ Calculate sum of value_market from pawnDetailsData
    $totalValueMarket = 0;
    foreach($pawnDetailsData as $detail) {
        $totalValueMarket += $detail['value_market'] ?? 0;
    }
@endphp

@foreach($pawnSumData as $sumData)

    <div class="field customer-name">{{ $sumData['First_name'] }}&nbsp;{{ $sumData['Last_name'] }}</div>
    <div class="field customer-address">{{ $sumData['Customer_Address'] }}</div>

    <div class="field ticket-number">{{ $sumData['Receipt_Number'] }}</div>
    <div class="field receipt-date">{{ $sumData['RePawning_date'] }} {{ $currentTimeFormatted }}</div>

    <div class="field branch">{{ Auth::user()->Branch }}</div>
    <div class="field branch-contact">{{ $branchDetails->contact1 ?? '' }}</div>

    <div class="field cashier">{{ $sumData['OC'] }}</div>

    {{-- ✅ Display the sum of value_market here --}}
    <div class="field market-value">{{ number_format($totalValueMarket, 2) }}</div>

    <div class="field intrest">{{ $rate3}}%</div>
    <div class="field acc">ACC NO: HNB Bank - 101010001477</div>

    @php
        $receiptDate = new DateTime($sumData['Receipt_Date']);
        $validPeriod = $sumData['Valid_Period'] ?? 0;

        $receiptDate->modify("+{$validPeriod} months");
        $dueDate = $receiptDate->format('Y-m-d');

        $interestFor30Days = number_format((($sumData['RePawning_amount'] / 100) * $rate3));
        $interestFor10Days = number_format((($sumData['RePawning_amount'] / 100) * $rate1));
    @endphp

    <div class="field expiry-date">{{ $sumData['Final_date'] }}</div>
    <div class="field duration-month">{{ $sumData['Valid_Period'] }}</div>
    <div class="field interest-10days">{{ $interestFor10Days }} - {{ $interestFor30Days }} </div>
    <div class="field advance-amount">Rs. {{ number_format($sumData['RePawning_amount'], 2) }}</div>

@endforeach


{{-- ========================================================= --}}
{{--   DYNAMIC ITEMS - AUTOMATICALLY ADJUST DOWNWARD            --}}
{{-- ========================================================= --}}
@php
    $startY = 6.7;     // starting Y position (cm)
    $gap = 0.5;        // row gap (cm)
    $row = 0;
@endphp

@foreach($pawnDetailsData as $detail)
    @php
        $topPosition = $startY + ($row * $gap);
    @endphp

    <div class="item-row col-article" style="top: {{ $topPosition }}cm;">
        {{ $detail['Articles'] }}
    </div>

    <div class="item-row col-desc" style="top: {{ $topPosition }}cm;">
        {{ $detail['Condition'] }} Qty:{{ $detail['QTY'] }}
    </div>

    <div class="item-row col-net" style="top: {{ $topPosition }}cm;">
        {{ $detail['Weight'] }}
    </div>

    <div class="item-row col-gold" style="top: {{ $topPosition }}cm;">
        {{ $detail['Weight'] }}
    </div>

    <div class="item-row col-karat" style="top: {{ $topPosition }}cm;">
        {{ $detail['Karatage'] }}
    </div>

    <div class="item-row col-int" style="top: {{ $topPosition }}cm;">
        {{ number_format($detail['Value'], 2) }}
    </div>

    @php $row++; @endphp
@endforeach

</body>
</html>