<head>
    <style>
        @import 'https://fonts.googleapis.com/css?family=Open+Sans:600,700';

        .rwd-table {
            margin: auto;
            min-width: 300px;
            max-width: 75%;
            border-collapse: collapse;
            font-family: 'Open Sans', sans-serif;
        }

        .rwd-table tr:first-child {
            border-top: none;
            background: #428bca;
            color: #fff;
        }

        .rwd-table tr {
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            background-color: #f5f9fc;
        }

        .rwd-table tr:nth-child(odd):not(:first-child) {
            background-color: #ebf3f9;
        }

        .rwd-table th { display: none; }
        .rwd-table td { display: block; }

        .rwd-table td:first-child  { margin-top: .5em; }
        .rwd-table td:last-child   { margin-bottom: .5em; }

        .rwd-table td:before {
            content: attr(data-th) ": ";
            font-weight: bold;
            width: 120px;
            display: inline-block;
            color: #000;
        }

        .rwd-table th,
        .rwd-table td { text-align: left; }

        .rwd-table {
            color: #333;
            border-radius: .4em;
            overflow: hidden;
        }

        .rwd-table tr { border-color: #bfbfbf; }

        .rwd-table th,
        .rwd-table td { padding: .5em 1em; }

        @media screen and (max-width: 601px) {
            .rwd-table tr:nth-child(2) { border-top: none; }
        }

        @media screen and (min-width: 600px) {
            .rwd-table tr:hover:not(:first-child) { background-color: #d8e7f3; }
            .rwd-table td:before { display: none; }

            .rwd-table th,
            .rwd-table td {
                display: table-cell;
                padding: .25em .5em;
            }

            .rwd-table th:first-child,
            .rwd-table td:first-child { padding-left: 0; }

            .rwd-table th:last-child,
            .rwd-table td:last-child { padding-right: 0; }

            .rwd-table th,
            .rwd-table td { padding: 1em !important; }
        }

        .container {
            display: block;
            text-align: center;
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
        }
    </style>
</head>

@php
    $calculatedTotalValue = $repawningPreview['article_value'];
@endphp

<div class="col">
    <div class="row">
        <div class="col">
            <input type="hidden" id="operator" name="operator"
                value="{{ Auth::user()->name }}" required>

            @foreach ($receiptData as $receipt)
                <input type="hidden" id="r_number" name="r_number"
                    value="{{ $receipt['Receipt_Number'] }}" required>
                <input type="hidden" id="i_number" name="i_number"
                    value="{{ $receipt['Invoice_Number'] }}">
                <input type="hidden" id="t_number" name="t_number"
                    value="{{ $receipt['Ticket_Number'] }}">
                <input type="hidden" id="sum_total_weight" name="sum_total_weight"
                    value="{{ $receipt['Total_Weight'] }}">
                <input type="hidden" id="sum_pawn_weight" name="sum_pawn_weight"
                    value="{{ $receipt['Pawn_Weight'] }}">
            @endforeach

            <label>Receipt Type :
                <input class="form-control" type="text" placeholder="Receipt type"
                    name="receipt_type"
                    @foreach ($receiptData as $receipt) value="{{ $receipt['Receipt_Type'] }}" @endforeach
                    id="receipt_type" readonly />
            </label>
        </div>

        <div class="col">
            <label>Date :
                <input class="form-control" type="date" id="date"
                    @foreach ($receiptData as $receipt) value="{{ $receipt['Pawn_Date'] }}" @endforeach
                    name="redeem_date" required>
            </label>
        </div>

        <div class="col">
            <label>Redeem Number :
                <input class="form-control" type="text" placeholder="Redeem Number:"
                    value="{{ $maxRedeem + 1 }}"
                    id="redeem_no" name="redeem_no" required>
            </label>
        </div>

        <div class="col">
            @if ($pawnType == 'Pawn')
                <label><span class="text-success">Pawn Receipt Type :</span>
                    <input class="form-control text-success" type="text"
                        value="{{ $pawnType }}"
                        id="pawn_receipt_type" name="pawn_receipt_type" required readonly>
                </label>
            @elseif ($pawnType == 'Opening_Pawn')
                <label><span class="text-primary">Pawn Receipt Type :</span>
                    <input class="form-control text-primary" type="text"
                        value="{{ $pawnType }}"
                        id="pawn_receipt_type" name="pawn_receipt_type" required readonly>
                </label>
            @endif
        </div>
    </div>
</div>

{{-- ── Receipt Info + Customer Info ───────────────────────────── --}}
<div class="row">

    {{-- Receipt Info --}}
    <div class="col-md-6">
        <h5 class="mt-3 mb-2">Receipt Info</h5>
        <table class="table table-bordered text-center" style="width:100%">
            <thead>
                @php
                    $finalDueDate = null;
                @endphp
                @foreach ($receiptData as $receipt)
                    @php
                        $receiptDate = new DateTime($receipt['Pawn_Date']);
                        $validPeriod = isset($receipt['Valid_Period']) && $receipt['Valid_Period'] !== null
                            ? (int) $receipt['Valid_Period'] : 0;
                        $receiptDate->modify("+{$validPeriod} months");
                        $dueDate = $receiptDate->format('Y-m-d');
                        if ($finalDueDate === null || $dueDate > $finalDueDate) {
                            $finalDueDate = $dueDate;
                        }
                    @endphp
                @endforeach

                <tr>
                    <th>Final Date</th>
                    <td><p id="date-final">{{ $finalDueDate }}</p></td>
                </tr>
                <tr>
                    <th>Receipt Date Time</th>
                    @foreach ($receiptData as $receipt)
                        <td><p>{{ $receipt['Pawn_Date'] }}</p></td>
                    @endforeach
                </tr>
                <tr>
                    <th>Period (Days)</th>
                    <td><p id="date-period"></p></td>
                </tr>
            </thead>
        </table>
    </div>

    {{-- Customer Info --}}
    <div class="col-md-6">
        <h5 class="mt-3 mb-2">Customer Info</h5>
        <table class="table table-bordered text-center" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    @foreach ($customerData as $customer)
                        <td>{{ $customer['First_name'] }} {{ $customer['Middle_name'] }} {{ $customer['Last_name'] }}</td>
                    @endforeach
                </tr>
                @if (auth()->user()->role === 'Admin')
                    <tr>
                        <th>Address</th>
                        @foreach ($customerData as $customer)
                            <td>{{ $customer['Address_1'] }}</td>
                        @endforeach
                    </tr>
                @endif
                <tr>
                    <th>NIC</th>
                    @foreach ($customerData as $customer)
                        <td><strong>{{ $customer['NIC'] }}</strong></td>
                    @endforeach
                </tr>
                @if (auth()->user()->role === 'Admin')
                    <tr>
                        <th>Contact</th>
                        @foreach ($customerData as $customer)
                            <td>{{ $customer['Contact_1'] }}</td>
                        @endforeach
                    </tr>
                @endif
            </thead>
        </table>
    </div>

</div>

{{-- Hidden customer fields for form submission --}}
<input type="hidden" name="Customer_Name" id="Customer_Name"
    value="{{ $customer['First_name'] }} {{ $customer['Last_name'] }}">
<input type="hidden" name="Customer_NIC" id="Customer_NIC"
    value="{{ $customer['NIC'] }}">

<br>

{{-- ── Buttons ────────────────────────────────────────────────── --}}
<div class="row" style="align-content:center;">
    <div class="col-md-6">
        <button class="btn btn-outline-info form-control" type="button"
            data-bs-toggle="modal" data-bs-target="#viewArticleDetailsModel">
            <i class="far fa-eye me-1"></i> View Article Details
        </button>
    </div>
    <div class="col-md-6">
        <button class="btn btn-outline-info form-control" type="button"
            data-bs-toggle="modal" data-bs-target="#viewPaymentHistoryModel">
            <i class="far fa-eye me-1"></i> Payment History
        </button>
    </div>
</div>
<br>

{{-- ── Repawning Details table ────────────────────────────────── --}}
<h5 class="mt-3 mb-2">Repawning Details</h5>
<table class="table table-bordered text-center">
    <tbody class="thead-light">

        {{-- Pawn Article Total Value (from karatage pawningrate × weight) --}}
        <tr>
            <th>Pawn Article Total Value</th>
            <td>
                <input class="form-control text-center" type="text"
                    value="{{ number_format($calculatedTotalValue, 2) }}" readonly />
                {{-- Hidden: karatage-based total used in JS calculations --}}
                <input type="hidden" id="karatage_total_value"
                    value="{{ $calculatedTotalValue }}">
            </td>
        </tr>

        {{-- Original amount stored at pawn time --}}
        <tr>
            <th>Original Pawn Amount</th>
            <td>
                @foreach ($receiptData as $receipt)
                    <input class="form-control text-center" type="text"
                        value="{{ number_format($receipt['Amount'], 2) }}" readonly />
                    <input type="hidden" name="original_pawn_amount"
                        value="{{ $receipt['Amount'] }}" id="original_pawn_amount" />
                @endforeach
            </td>
        </tr>

        {{-- Current running pawn amount (after any prior repawnings) --}}
        <tr>
            <th>Current Pawn Amount</th>
            <td>
                @foreach ($receiptData as $receipt)
                    <input class="form-control text-center" type="text"
                        value="{{ number_format($receipt['Pawn_Amount'], 2) }}" readonly />
                @endforeach
            </td>
        </tr>

        <tr>
            <th>Paid Interest</th>
            <td>
                <input class="form-control text-center" id="interest" type="text"
                    placeholder="Paid Interest" name="paid_interest" value="" readonly />
            </td>
        </tr>

        <tr>
            <th>Stamp Duty</th>
            <td>
                <input class="form-control text-center" id="stampduty" type="text"
                    placeholder="Stamp Duty" name="stamp_fee" value="" readonly />
            </td>
        </tr>

        <tr>
            <th>Service Charges</th>
            <td>
                <input class="form-control text-center" id="document_charges" type="text"
                    placeholder="Service Charges" name="document_charges" value="" readonly />
                <label style="margin-left:10px;">
                    <input type="checkbox" name="addServiceCharges" id="addServiceCharges" value="1" required>
                    Add to Total
                </label>
            </td>
        </tr>

    </tbody>
</table>
<br>

{{-- ── Interest repawning table (1-12 months) ────────────────── --}}
<div class="container">
    <div class="table-responsive">
        <table class="rwd-table">
            <tbody>
                <tr style="background-color:#428bca">
                    <th>01 Months</th>
                    <th>02 Months</th>
                    <th>03 Months</th>
                    <th>04 Months</th>
                    <th>05 Months</th>
                    <th>06 Months</th>
                    <th>07 Months</th>
                    <th>08 Months</th>
                    <th>09 Months</th>
                    <th>10 Months</th>
                    <th>11 Months</th>
                    <th>12 Months</th>
                </tr>
                <tr>
                    @for ($m = 1; $m <= 12; $m++)
                        @php
                            $monthValue = $repawningPreview['month_options'][$m];
                        @endphp
                        <td id="output{{ $m }}" @if ($monthValue === null) style="display:none" @endif>
                            <input type="text"
                                value="{{ $monthValue === null ? '' : number_format($monthValue, 2, '.', '') }}"
                                id="Interest{{ $m }}"
                                style="outline:#ebf3f9; width:100%; min-width:100px;" readonly>
                        </td>
                    @endfor
                </tr>
            </tbody>
        </table>
    </div>
</div>
<br>


@php
    $repawningJsConfig = [
        'receiptName' => strtoupper((string) ($receiptData[0]['receiptname'] ?: $receiptData[0]['Receipt_Type'])),
        'receiptDate' => $receiptData[0]['Pawn_Date'],
        'principal' => (float) $repawningPreview['principal'],
        'previouslyPaidInterest' => (float) ($receiptData[0]['interest_Paid'] ?? 0),
        'carriedInterest' => (float) ($receiptData[0]['BalanceInterest'] ?? 0),
        'period1' => (int) ($receiptTypeData->first()->period1 ?? 0),
        'period2' => (int) ($receiptTypeData->first()->period2 ?? 0),
        'period3' => (int) ($receiptTypeData->first()->period3 ?? 30),
        'validDays' => (int) ($receiptTypeData->first()->validPeriod ?? 0),
        'rate1' => (float) ($receiptTypeData->first()->rate1 ?? 0),
        'rate2' => (float) ($receiptTypeData->first()->rate2 ?? 0),
        'rate3' => (float) ($receiptTypeData->first()->rate3 ?? 0),
        'articleValue' => (float) $repawningPreview['article_value'],
        'monthlyRate' => (float) $repawningPreview['monthly_rate'],
        'validMonths' => (int) $repawningPreview['valid_months'],
        'serviceCharge' => (float) ($financial['service_charge'] ?? 0),
        'letterCharge' => (float) ($financial['letter_charge'] ?? 0),
        'stampDuty' => (float) $repawningPreview['stamp_duty'],
    ];
@endphp
<script>
$(document).ready(function () {
    const config = @json($repawningJsConfig);

    document.getElementById('date').valueAsDate = new Date();
    $('#document_charges').val(config.serviceCharge.toFixed(2));
    $('#stampduty').val(config.stampDuty.toFixed(2));

    calculateFinalDate();
    interestCalculations();

    $('#date').on('change', function () {
        calculateFinalDate();
        interestCalculations();
    });

    // ── Service charge checkbox ───────────────────────────────────
    $('#addServiceCharges').on('change', function () {
        redeemTotalCalculation();
    });

    function calculateFinalDate() {
        let receiptDate = new Date(config.receiptDate);
        let todayFormat = new Date($('#date').val());
        let diffDays    = Math.floor((todayFormat - receiptDate) / (1000 * 60 * 60 * 24)) + 1;
        $('#date-period').text(diffDays);
    }

    function interestCalculations() {
        let receiptDate = new Date(config.receiptDate);
        let todayFormat = new Date($('#date').val());
        let diffDays    = Math.floor((todayFormat - receiptDate) / (1000 * 60 * 60 * 24)) + 1;

        if (diffDays <= 0) {
            $('#interest').val('0.00');
            redeemTotalCalculation();
            return;
        }

        let months   = Math.ceil(diffDays / 30);
        let interest = 0;

        if (config.receiptName === 'SILVER') {
            interest = ((config.principal / 100) * config.rate1 * Math.max(1, diffDays / Math.max(1, config.period3 || 30)));
        } else if (config.receiptName === 'D' && config.validDays > 0 && diffDays > config.validDays) {
            let penaltyDiff   = diffDays - config.validDays;
            let basePart      = (config.principal / 100) * config.rate2 * months;
            let penaltyMonths = Math.ceil(penaltyDiff / 30);
            let penaltyCharge = (config.principal / 100) * 0.5 * penaltyMonths;
            interest          = basePart + penaltyCharge;
        } else if (config.receiptName === 'D') {
            interest = ((config.principal / 100) * config.rate2 * months);
        } else {
            if (diffDays <= config.period1) {
                interest = (config.principal / 100) * config.rate1;
            } else if (diffDays <= config.period2) {
                interest = (config.principal / 100) * config.rate2;
            } else if (diffDays <= 30) {
                interest = (config.principal / 100) * config.rate3;
            } else {
                let fullMonths    = Math.floor(diffDays / 30);
                let remainingDays = diffDays % 30;
                let totalRate     = (config.rate3 * fullMonths) + ((config.rate3 / 30) * remainingDays);
                interest          = (config.principal / 100) * totalRate;
            }
        }

        interest = Math.max(0, interest - config.previouslyPaidInterest + config.carriedInterest);
        $('#interest').val(parseFloat(interest).toFixed(2));
        redeemTotalCalculation();
    }

    function redeemTotalCalculation() {
        let totalvalueinterest = config.articleValue;
        let discount         = parseFloat($('#redeem_discount').val())    || 0;
        let interest_to_pay  = parseFloat($('#interest').val())           || 0;
        let stampduty        = parseFloat($('#stampduty').val())          || 0;
        let document_charges = parseFloat($('#document_charges').val())   || 0;
        let letter_charges   = config.letterCharge;

        let totalPay = Math.max(0, config.principal + interest_to_pay + document_charges + letter_charges + stampduty - discount);
        let totalvalue = totalvalueinterest - totalPay;
        let monthsInterest = totalvalueinterest / 100 * config.monthlyRate;

        $('#redeem_total').val(totalPay.toFixed(2));
        $('#redeem_ammount').val(totalPay.toFixed(2));
        $('#ammount').val(totalvalueinterest);
        $('#totalvalueinterst').val(totalvalue.toFixed(2));

        // Show / hide repawning row based on available balance
        let val = parseFloat($('#totalvalueinterst').val());
        if (isNaN(val) || val < 0) {
            $('#REPAWNING').hide();
        } else {
            $('#REPAWNING').show();
        }

        // Block form submit if no repawning balance
        $('button[name="redeem"]').off('click.repawnCheck').on('click.repawnCheck', function (e) {
            let v = parseFloat($('#totalvalueinterst').val());
            if (isNaN(v) || v < 0) {
                e.preventDefault();
                alert('Maximum repawning amount reached. Kindly pay the interest and monthly interest.');
            }
        });

        function setInterestField(id, value) {
            let field = $('#' + id);
            if (value === null) {
                field.closest('td').hide();
            } else {
                field.closest('td').show();
                field.val(value < 0 ? 'repawning amount reached' : parseFloat(value).toFixed(2));
            }
        }

        for (let i = 1; i <= 12; i++) {
            let value = i <= config.validMonths
                ? totalvalue - (monthsInterest * (i - 1))
                : null;
            setInterestField('Interest' + i, value);
        }
    }

    // ── Re-run when payable_total changes ────────────────────────
    $('#payable_total').on('blur change', function () {
        redeemTotalCalculation();
    });

    // ── Re-run when discount changes ─────────────────────────────
    $('#redeem_discount').on('keyup', function (e) {
        e.preventDefault();
        redeemTotalCalculation();
    });

});
</script>
