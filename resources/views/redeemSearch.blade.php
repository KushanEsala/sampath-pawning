<head>
    <style>
    .styled-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .styled-table thead th {
        background-color: #4183ec;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        padding: 12px;
        border-bottom: 2px solid #dee2e6;
    }

    .styled-table tbody tr {
        background-color: #f9f9f9;
        transition: background-color 0.3s;
    }

    .styled-table tbody tr:hover {
        background-color: #e0f0ff;
    }

    .styled-table td {
        padding: 12px;
        vertical-align: middle;
        color: #333;
        font-size: 15px;
    }

    .styled-table th,
    .styled-table td {
        text-align: center;
    }
    </style>
</head>


<div class="col ">
    <div class="row">
        <div class="col">
            <input type="hidden" id="operator" name="operator" value="{{ Auth::user()->name }}" required>

            @foreach ($receiptData as $receipt)
            <input type="hidden" id="r_number" name="r_number" value="{{ $receipt['Receipt_Number'] }}" required>
            <input type="hidden" id="i_number" name="i_number" value="{{ $receipt['Invoice_Number'] }}">
            <input type="hidden" id="t_number" name="t_number" value="{{ $receipt['Ticket_Number'] }}">

            <input type="hidden" id="sum_total_weight" name="sum_total_weight" value="{{ $receipt['Total_Weight'] }}">
            <input type="hidden" id="sum_pawn_weight" name="sum_pawn_weight" value="{{ $receipt['Pawn_Weight'] }}">
            @endforeach


            <label for="receipt_type">Receipt Type :
                <input class="form-control"  type="text" placeholder="Receipt type"
                    name="receipt_type"
                    @foreach ($receiptData as $receipt)
                    value="{{$receipt['Receipt_Type']}}"
                    @endforeach
                    id="receipt_type" readonly/>
            </label>
        </div>
        <div class="col">
                <label for="date">Date :
                    <input class="form-control" type="date"
                    id="date"
                    @foreach ($receiptData as $receipt)
                    value="{{ $receipt['Pawn_Date'] }}"
                    @endforeach
                    name="redeem_date" required>
                </label>
        </div>
        <div class="col">
            <label for="redeem_no">Redeem Number :
                <input class="form-control" type="text" placeholder="Redeem Number:"
                value="{{ $maxRedeem+1 }}"
                id="redeem_no" name="redeem_no" required>
            </label>
        </div>
        <div class="col">
            @if($pawnType == "Pawn")
            <label for="pawn_receipt_type"><span class="text-success">Pawn Receipt Type :</span>
                <input class="form-control text-success" type="text" placeholder="Pawn Receipt Type:"
                value="{{ $pawnType }}"
                id="pawn_receipt_type" name="pawn_receipt_type" required readonly>
            </label>
            @elseif($pawnType == "Opening_Pawn")
            <label for="pawn_receipt_type"><span class="text-primary">Pawn Receipt Type :</span>
                <input class="form-control text-primary" type="text" placeholder="Pawn Receipt Type:"
                value="{{ $pawnType }}"
                id="pawn_receipt_type" name="pawn_receipt_type" required readonly>
            </label>

            @endif
        </div>
    </div>
</div>
<h5 class="mt-3 mb-2">Receipt Info</h5>
<table class="table table-bordered text-center" id="dynamicAdded">
    <thead class="thead-light">
        <tr>
            <th>Final Date</th>
            <th>Receipt Date Time</th>
            <th>Period (Days)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <p id="date-final">{{ $receiptData[0]['Final_date'] ?? '' }}</p>
            </td>
            <td>
                <p>
                @foreach ($receiptData as $receipt)
                   {{ $receipt['Pawn_Date'] }}
                @endforeach
                </p>
            </td>
            <td>
                <p id="date-period"></p>
            </td>
        </tr>
    </tbody>
</table>
<table class="table table-bordered text-center" id="dynamicAdded">
    <thead class="thead-light">
        <tr>
            <th>Paid Advance Payment</th>
            <th>Paid Interest Payment</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <p>
                @foreach ($receiptData as $receipt)
                @foreach ($receiptData as $receipt)
                {{ ($receipt['Amount'] ?? 0) - ($receipt['Pawn_Amount'] ?? 0) }}
            @endforeach

                @endforeach
                </p>
            </td>
            <td>
                <p>
                    @foreach ($receiptData as $receipt)
                       {{ $receipt['interest_Paid'] }}
                    @endforeach
                    </p>
            </td>
        </tr>
    </tbody>
</table>
<br>
<div class="row">

      <div class="col-md-6">
            <h5 class="mt-3 mb-2">Customer Info</h5>
           <table class="styled-table text-center">
                <thead>
                    <tr>
                        <th>Name</th>
                        @foreach ($customerData as $customer)
                        <td><h7>{{ $customer['First_name'] }} {{ $customer['Middle_name'] }} {{ $customer['Last_name'] }}</h7></td>
                        @endforeach
                    </tr>
                    @if(auth()->user()->role === 'Admin')
                    <tr>
                        <th>Address</th>
                        @foreach ($customerData as $customer)
                        <td><h6>{{ $customer['Address_1'] }}</h5></td>
                        @endforeach
                    </tr>
                     @endif
                    <tr>
                        <th>NIC</th>
                        @foreach ($customerData as $customer)
                        <td><h5>{{ $customer['NIC'] }}</h5></td>
                        @endforeach
                    </tr>
                    @if(auth()->user()->role === 'Admin')
                    <tr>
                        <th>Contact</th>
                        @foreach ($customerData as $customer)
                        <td><h5>{{ $customer['Contact_1'] }}</h5></td>
                        @endforeach
                    </tr>
                    @endif

                </thead>
            </table>
      </div>

     <div class="col-md-6">
        <h5 class="mt-3 mb-2">Customer comment</h5>
        <table class="styled-table text-center">
            <thead>
                <tr>
                    <th>Comment</th>
                    <th>Comment Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($MPawnfeedback as $customer)
                <tr>
                    <td>{{ $customer['feedback'] }}</td>
                    <td>{{ $customer['Current_date'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<br>
<div class="row" style="align-content: center;">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-outline-info form-control" type="button"
                data-bs-toggle="modal" data-bs-target="#viewArticleDetailsModel">
                <i class="far fa-eye me-1"></i>
                    View Article Details
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-12">
            <button class="btn btn-outline-info form-control" type="button"
            data-bs-toggle="modal" data-bs-target="#viewPaymentHistoryModel">
            <i class="far fa-eye me-1"></i>
                Payment History
            </button>
        </div>
    </div>
</div>
</div>

<br>

<input type="hidden"  class="form-control text-center" value="{{ $customer['First_name'] }} {{ $customer['Last_name'] }}" name="Customer_Name" id="Customer_Name">
<input type="hidden" class="form-control text-center" value="{{ $customer['NIC'] }}" id="Customer_NIC" name="Customer_NIC">

<br>
<h5 class="mt-3 mb-2">Article Details</h5>
<table class="table table-bordered text-center" >
    <thead class="thead-light">
        <tr>
            <th>Pawn Weight </th>
            <th>Total Weight</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            @foreach ($receiptData as $receipt )
            <td><h5> {{ number_format($receipt['Pawn_Weight'], 3) }} g <br></h5></td>
            <td><h5> {{ number_format($receipt['Total_Weight'], 3) }} g</h5></td>
            @endforeach
        </tr>
    </tbody>
</table>

<br>
<div class="row" style="align-content: center;">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-outline-info form-control" type="button"
                data-bs-toggle="modal" data-bs-target="#viewArticleDetailsModel">
                <i class="far fa-eye me-1"></i>
                    View Article Details
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-12">
            <button class="btn btn-outline-info form-control" type="button"
            data-bs-toggle="modal" data-bs-target="#viewPaymentHistoryModel">
            <i class="far fa-eye me-1"></i>
                Payment History
            </button>
        </div>
    </div>
</div>
<br>
<h5 class="mt-3 mb-2">Redeem Details</h5>
<table class="table table-bordered text-center" id="dynamicAdded">
    <tbody class="thead-light">
        <tr>
            <th>Original Pawn Amount</th>
            <td>
                @foreach ($receiptData as $receipt)
                <p>
                    <input class="form-control text-center" name="pawn_amount_new"  type="text" placeholder="Original Pawn Amount"
                        value="" id="total" readonly/>

                    <input type="hidden"
                    name="original_pawn_amount"
                    value="{{ $receipt['Pawn_Amount'] + $receipt['interest_Paid'] }}"
                    id="original_pawn_amount" readonly/>

                    <input type="hidden" id="BalanceInterest" name="BalanceInterest" text=''>
                </p>
                @endforeach
            </td>
        </tr>
        <tr>
            <th>Paid Interest</th>
            <td>
                <p>
                    <input class="form-control text-center" id="interest" type="text" placeholder="Paid Interest"
                    name="paid_interest" value="" readonly/>
                </p>
            </td>
        </tr>

        <tr>
            <th> Postage Charges</th>
            <td>
                <p>
                    <input class="form-control text-center" id="Postage_Charges" name="Postage_Charges" type="text" placeholder="Postage_Charges"
                    name="Postage_Charges" value="" readonly/>
                </p>
            </td>
        </tr>

        <tr style="display: none;">
            <th>Stampduty</th>
            <td>
                <p>
                    <input class="form-control text-center" id="stampduty"  type="text" placeholder="Stampduty"
                    name="stampduty" value="" readonly/>
                </p>
            </td>
        </tr>
        <tr>
            <th>Service Charges</th>
            <td>
                <p>
                    <input class="form-control text-center" id="document_charges" type="text" placeholder="Document Charges"
                    name="document_charges" value="" readonly/>
                    <label for="addServiceCharges" style="margin-left: 10px;">
                        <input type="checkbox" name="addServiceCharges" id="addServiceCharges" required> Add to Total
                    </label>
                </p>
            </td>
        </tr>
    </tbody>
</table>


<br>


{{-- Functions for date and time calculation --}}
<script>
$(document).ready(function () {

    // Set default date to today
    document.getElementById('date').valueAsDate = new Date();

    calculateFinalDate();
    interestCalculations();

    // --- Document charge setup ---
    let doc_chargers = 0, stampduty = 0, s_charge_less = 0, s_charge_greater = 0;

    @foreach ($receiptTypeData as $receiptType)
        doc_chargers = {{ $receiptType['documentCharges'] }};
        stampduty = {{ $receiptType['stampduty'] }};
        s_charge_less = parseFloat({{ $receiptType['service_charge'] }}) || 0;
        s_charge_greater = parseFloat({{ $receiptType['s_charge_greater'] }}) || 0;
    @endforeach;

    let p_amount = parseFloat({{ $receiptData[0]['Pawn_Amount'] }}) || 0;
    if (p_amount > 0) {
        $('#document_charges').val(s_charge_less);
    } else {
        let cal_service = parseFloat((p_amount / 100) * s_charge_greater) || 0;
        $('#document_charges').val(cal_service);
    }

    // --- Event listener for date and period changes ---
    $('#date, #date-period').on('keyup change', function (e) {
        e.preventDefault();
        calculateFinalDate();
        interestCalculations();
    });

    // --- Service Charges Checkbox Handler ---
    $('#addServiceCharges').on('change', function() {
        redeemTotalCalculation();
    });

    // --- Calculate the final date ---
    function calculateFinalDate() {
        let receiptDate = new Date("@foreach ($receiptData as $receipt){{ $receipt['Pawn_Date'] }}@endforeach");
        let today_str = $('#date').val();
        let today_format = new Date(today_str);

        let timeDiffMilliseconds = today_format - receiptDate;
        let date_range_days = Math.floor(timeDiffMilliseconds / (1000 * 60 * 60 * 24)) + 1;

        $('#date-period').text(date_range_days);
    }

    // --- Interest calculation function ---
    function interestCalculations() {
        let receiptDate = new Date("@foreach ($receiptData as $receipt){{ $receipt['Pawn_Date'] }}@endforeach");
        let today_str = $('#date').val();
        let today_format = new Date(today_str);
        let timeDiffMilliseconds = today_format - receiptDate;
        let date_range_days = Math.floor(timeDiffMilliseconds / (1000 * 60 * 60 * 24)) + 1;

        // --- Getting receipt details ---
        let receipt_name = '';
        let period1 = 0, period2 = 0, period3 = 0;
        let rate1 = 0, rate2 = 0, rate3 = 0;
        let valid_period = 0;

        @foreach ($receiptTypeData as $receiptType)
            receipt_name = '{{ $receiptType['receiptname'] }}';
            period1 += {{ $receiptType['period1'] }};
            period2 += {{ $receiptType['period2'] }};
            period3 += {{ $receiptType['period3'] }};
            rate1 += {{ $receiptType['rate1'] }};
            rate2 += {{ $receiptType['rate2'] }};
            rate3 += {{ $receiptType['rate3'] }};
            valid_period += {{ $receiptType['validPeriod'] }};
        @endforeach;

        // --- Calculate Interest ---
        let amount = parseFloat({{ $receiptData[0]['Pawn_Amount'] }}) || 0;
        let paid_interest = parseFloat({{ $receiptData[0]['interest_Paid'] }}) || 0;
        let carried_interest = parseFloat({{ $receiptData[0]['BalanceInterest'] ?? 0 }}) || 0;
        let months = Math.ceil(date_range_days / 30);
        let penalty_days = date_range_days - valid_period;
        let interest = 0;

        if (receipt_name === "SILVER") {
            let full_months = Math.floor(date_range_days / 30);
            let remaining_days = date_range_days % 30;
            let totalRate = date_range_days <= 30
                ? rate1
                : ((rate1 * full_months) + ((rate1 / 30) * remaining_days));
            interest = (amount / 100) * totalRate;
        } else if (receipt_name === "D") {
            let interest_set = (amount / 100) * rate2 * months;
            if (date_range_days > valid_period && valid_period > 0) {
                let penalty_rate = 0.5; // 50%
                let penalty_months = Math.ceil(penalty_days / 30);
                let penalty_interest = penalty_rate * penalty_months;
                let penalty_charge = (amount / 100) * penalty_interest;
                interest = interest_set + penalty_charge;
            } else {
                interest = interest_set;
            }
        } else {
            // Normal interest calculation
            if (date_range_days <= period1) {
                interest = ((amount / 100) * rate1);
            } else if (date_range_days <= period2) {
                interest = ((amount / 100) * rate2);
            } else if (date_range_days <= 30) {
                interest = ((amount / 100) * rate3);
            } else {
                let full_months = Math.floor(date_range_days / 30);
                let remaining_days = date_range_days % 30;
                let totalRate = (rate3 * full_months) + ((rate3 / 30) * remaining_days);
                interest = ((amount / 100) * totalRate);
            }
        }

        interest = Math.max(0, interest - paid_interest + carried_interest);
        $('#interest').val(interest.toFixed(2));

        redeemTotalCalculation();
    }

    // --- Redeem total calculation ---
    function redeemTotalCalculation() {
        let amount = parseFloat({{ $receiptData[0]['Pawn_Amount'] }}) || 0;
        let paid_interest = parseFloat({{ $receiptData[0]['interest_Paid'] }}) || 0;
        let Bal_int = parseFloat({{ $receiptData[0]['BalanceInterest'] ?? 0 }}) || 0;
        let discount = parseFloat($('#redeem_discount').val()) || 0;
        let advance_payment = parseFloat($('#advance_payment').val()) || 0;
        let interest_Paid = parseFloat($('#interest_Paid').val()) || 0;
        let interest_to_pay = parseFloat($('#interest').val()) || 0;
        let document_charges = parseFloat({{ $financial['service_charge'] ?? 0 }}) || 0;
        let letter_pay_one = parseFloat({{ $receiptData[0]['letter_pay_one'] }}) || 0;
        let letter_pay_two = parseFloat({{ $receiptData[0]['letter_pay_two'] }}) || 0;
        let letter_pay_three = parseFloat({{ $receiptData[0]['letter_pay_three'] }}) || 0;

        let Postage_Charges = parseFloat({{ $financial['letter_charge'] ?? 0 }}) || 0;
        let stamp_duty = (Postage_Charges > 0) ? 0 : 0;
        $('#stampduty').val(stamp_duty.toFixed(2));

        let totalPay = 0;
        let interestpay = 0;

        // Check if service charges should be added
        let includeServiceCharges = true;

        if (Postage_Charges > 0) {
            totalPay = interest_to_pay + stamp_duty + amount + Postage_Charges;
            interestpay = interest_to_pay + stamp_duty;

            // Add document charges if checkbox is checked
            if (includeServiceCharges) {
                totalPay += document_charges;
                interestpay += document_charges;
            }
        } else if (Postage_Charges < 0) {
            totalPay = interest_to_pay + amount + Postage_Charges;
            interestpay = interest_to_pay;

            // Add document charges if checkbox is checked
            if (includeServiceCharges) {
                totalPay += document_charges;
                interestpay += document_charges;
            }
        } else {
            totalPay = interest_to_pay + amount;
            interestpay = interest_to_pay;

            // Add document charges if checkbox is checked
            if (includeServiceCharges) {
                totalPay += document_charges;
                interestpay += document_charges;
            }
        }

        // Prevent negative totals
        if (totalPay < 0) totalPay = 0;

        // Update values
        $('#redeem_total').val(totalPay.toFixed(2));
        $('#total').val(amount.toFixed(2));
        $('#BalanceInterest').val((interest_to_pay - interest_Paid).toFixed(2));
        $('#Postage_Charges').val(Postage_Charges.toFixed(2));

        let advancePayment = interest_Paid - interestpay;
        $('#advance_payment').val(advancePayment > 0 ? advancePayment.toFixed(2) : '0.00');

        let pawningPayment = amount + interestpay - interest_Paid;
        $('#pawning_payment').val(pawningPayment > 0 ? pawningPayment.toFixed(2) : '0.00');

        $('#payable_total').val((interest_Paid - discount).toFixed(2));

        let payTotalAmount = totalPay - advancePayment - (includeServiceCharges ? 0 : document_charges) - (pawningPayment > 0 ? pawningPayment : 0);
        $('#PayTotalAmount').val(payTotalAmount > 0 ? payTotalAmount.toFixed(2) : '0.00');
    }

    // --- Live calculation triggers ---
    $(document).on('input', '#redeem_discount,#total,#BalanceInterest,#PayTotalAmount,#advance_payment,#interest_Paid,#interest,#document_charges,#stampduty,#pawning_payment', function () {
        redeemTotalCalculation();
    });

    $('#redeem_discount').on('keyup', function (e) {
        e.preventDefault();
        redeemTotalCalculation();
    });

});
</script>


<script>
$(document).ready(function () {
    function updateStampDuty() {
        let postage = parseFloat($("#Postage_Charges").val()) || 0;
        let stampduty = parseFloat($("#stampduty").val()) || 25;
        if (postage > 0) {
            $("#stampduty").val(stampduty); // ✅ show postage value in stampduty
        } else {
            $("#stampduty").val(0); // ✅ set 0 if postage <= 0
        }
    }

    // Run when page loads
    updateStampDuty();

    // Run whenever Postage_Charges changes
    $("#Postage_Charges").on("input change", function () {
        updateStampDuty();
    });
});
</script>
