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

.rwd-table th {
  display: none;
}

.rwd-table td {
  display: block;
}

.rwd-table td:first-child {
  margin-top: .5em;
}

.rwd-table td:last-child {
  margin-bottom: .5em;
}

.rwd-table td:before {
  content: attr(data-th) ": ";
  font-weight: bold;
  width: 120px;
  display: inline-block;
  color: #000;
}

.rwd-table th,
.rwd-table td {
  text-align: left;
}

.rwd-table {
  color: #333;
  border-radius: .4em;
  overflow: hidden;
}

.rwd-table tr {
  border-color: #bfbfbf;
}

.rwd-table th,
.rwd-table td {
  padding: .5em 1em;
}
@media screen and (max-width: 601px) {
  .rwd-table tr:nth-child(2) {
    border-top: none;
  }
}
@media screen and (min-width: 600px) {
  .rwd-table tr:hover:not(:first-child) {
    background-color: #d8e7f3;
  }
  .rwd-table td:before {
    display: none;
  }
  .rwd-table th,
  .rwd-table td {
    display: table-cell;
    padding: .25em .5em;
  }
  .rwd-table th:first-child,
  .rwd-table td:first-child {
    padding-left: 0;
  }
  .rwd-table th:last-child,
  .rwd-table td:last-child {
    padding-right: 0;
  }
  .rwd-table th,
  .rwd-table td {
    padding: 1em !important;
  }
}

.container {
  display: block;
  text-align: center;
  width: 100%; /* Set the width to 80% of its parent container */
  max-width: 1500px; /* Set a maximum width to prevent it from growing too wide */
  margin: 0 auto; /* Center the container horizontally */
}

    </style>



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
  @foreach ($customerData as $customer )
<input type="hidden"  class="form-control text-center" value="{{ $customer['First_name'] }} {{ $customer['Last_name'] }}" name="Customer_Name" id="Customer_Name">
<input type="hidden" class="form-control text-center" value="{{ $customer['NIC'] }}" id="Customer_NIC" name="Customer_NIC">
     @endforeach


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
                    <input class="form-control text-center"  type="text" placeholder="Original Pawn Amount"
                        value="" id="total" name="current_pawn_amount"  readonly/>

                    <input type="hidden"
                    name="original_pawn_amount"
                    value="{{ $receipt['Pawn_Amount'] }}"
                    id="original_pawn_amount" readonly/>

                    <input type="hidden"  id="BalanceInterest" name="BalanceInterest" text=''>
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
                    <input class="form-control text-center" id="Postage_Charges" type="text" placeholder="Postage_Charges"
                    name="Postage_Charges" value="" readonly/>
                </p>
            </td>
        </tr>


        <tr>
            <th>Stampduty</th>
            <td>
                <p>
                    <input class="form-control text-center" id="stampduty" type="text" placeholder="Stampduty"
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
                </p>
            </td>
        </tr>
    </tbody>
</table>

<br>
<br>
<div class="container">
    <div class="table-responsive">
    <table  class="rwd-table">
        <tbody>
            <tr style="background-color: #428bca">
                <th width = >01 Months</th>
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
                <td id="output1"><input type="text" value="1" id="Interest1" style="outline: #ebf3f9" readonly></td>
                <td id="output2"><input type="text" value="2" id="Interest2" style="outline: #ebf3f9" readonly></td>
                <td id="output3"><input type="text" value="3" id="Interest3" style="outline: #ebf3f9" readonly></td>
                <td id="output4"><input type="text" value="4" id="Interest4" style="outline: #ebf3f9" readonly></td>
                <td id="output5"><input type="text" value="5" id="Interest5" style="outline: #ebf3f9" readonly></td>
                <td id="output6"><input type="text" value="6" id="Interest6" style="outline: #ebf3f9" readonly></td>
                <td id="output7"><input type="text" value="7" id="Interest7" style="outline: #ebf3f9" readonly></td>
                <td id="output8"><input type="text" value="8" id="Interest8" style="outline: #ebf3f9" readonly></td>
                <td id="output9"><input type="text" value="9" id="Interest9" style="outline: #ebf3f9" readonly></td>
                <td id="output10"><input type="text"  value="10" id="Interest10" style="outline: #ebf3f9" readonly></td>
                <td id="output11"><input type="text" value="11" id="Interest11" style="outline: #ebf3f9" readonly></td>
                <td id="output12"><input type="text" value="12" id="Interest12" style="outline: #ebf3f9" readonly></td>
              </tr>
            </tbody>
    </table>
    </div>
</div>
<br>


{{-- Functions for date and time  calculation --}}
<script>
    $(document).ready(function () {

        // form default date set for today
        document.getElementById('date').valueAsDate = new Date();

        calculateFinalDate();
        interestCalculations();

        //getting document charges
        let doc_chargers = 0 ;
        let stampduty = 0 ;
        let s_charge_less = 0;
        let s_charge_greater= 0;

            @foreach ($receiptTypeData as $receiptType)
                doc_chargers = {{ $receiptType['documentCharges'] }};
                stampduty = {{ $receiptType['stampduty'] }};
                s_charge_less = parseFloat({{ $receiptType['s_charge_less'] }}) || 0;
                s_charge_greater = parseFloat({{ $receiptType['s_charge_greater'] }}) || 0;
            @endforeach;

            // $('#document_charges').val(doc_chargers);

            let p_amount = parseFloat( {{  $receiptData[0]['Pawn_Amount'] }} ) || 0;
            if(p_amount < 25){
                $('#document_charges').val(s_charge_less);
            }else{
                let cal_service = parseFloat((p_amount / 100) * s_charge_greater) || 0;
                $('#document_charges').val(cal_service);
            }

        // run interest calculation when change the amount field
        $('#date, #date-period').on('keyup, change',function(e){
                e.preventDefault();
                calculateFinalDate();
                interestCalculations();
        })

        // Function to calculate and display the final date
        function calculateFinalDate() {
            let receiptDate = new Date("@foreach ($receiptData as $receipt){{ $receipt['Pawn_Date'] }}@endforeach");
            let today_str = $('#date').val();
            let today_format = new Date(today_str);

            // Calculate the time difference in milliseconds
            let timeDiffMilliseconds = today_format - receiptDate;
            // Convert milliseconds to days
            let date_range_days = Math.floor(timeDiffMilliseconds / (1000 * 60 * 60 * 24)) + 1;
            // Display the date range
            let DateRangeDisplay = document.getElementById("date-period");
            DateRangeDisplay.textContent = date_range_days;
        }

        function interestCalculations(){
            let receiptDate = new Date("@foreach ($receiptData as $receipt){{ $receipt['Pawn_Date'] }}@endforeach");
            let today_str = $('#date').val();
            let today_format = new Date(today_str);

            // Calculate the time difference in milliseconds
            let timeDiffMilliseconds = today_format - receiptDate;
            // Convert milliseconds to days
            let date_range_days = Math.floor(timeDiffMilliseconds / (1000 * 60 * 60 * 24)) + 1;

            // CHECK: If date_range_days is 0 or negative, set interest to 0 and return
            if (date_range_days <= 0) {
                $('#interest').val('0.00');
                redeemTotalCalculation();
                return;
            }

            // getting period details
            let receipt_name='';
            let period1 = 0;
            let period2 = 0;
            let period3 = 0;
            let rate1 = 0;
            let rate2 = 0;
            let rate3 = 0;
            let valid_period  = 0;
            let s_char_less = 0;
            let s_char_grea = 0;

            @foreach ($receiptTypeData as $receiptType)
                receipt_name = '{{ $receiptType['receiptname'] }}';
                period1 += {{ $receiptType['period1'] }};
                period2 += {{ $receiptType['period2'] }};
                period3 += {{ $receiptType['period3'] }};
                rate1 += {{ $receiptType['rate1'] }};
                rate2 += {{ $receiptType['rate2'] }};
                rate3 += {{ $receiptType['rate3'] }};
                valid_period += {{ $receiptType['validPeriod'] }};
                s_char_less += {{ $receiptType['s_charge_less'] }};
                s_char_grea += {{ $receiptType['s_charge_greater'] }};
            @endforeach;


            // Calculate the interest
            let amount = parseFloat( {{  $receiptData[0]['Pawn_Amount'] }} ) || 0;
            let paid_interest = parseFloat( {{  $receiptData[0]['interest_Paid'] }} ) || 0;
            let carried_interest = parseFloat( {{  $receiptData[0]['BalanceInterest'] }} ) || 0;
            let months = Math.ceil(date_range_days / 30);
            let panelty_dif = date_range_days-valid_period;
            let total_int = 0;
            let interest = 0;

            if (receipt_name === "SILVER") {
                interest = ((amount / 100) * rate1) * Math.max(1, date_range_days / Math.max(1, period3 || 30));
                $('#interest').val(interest.toFixed(2));

            } else if (receipt_name === "D" && valid_period > 0 && date_range_days > valid_period) {
                let interest_set = ((amount / 100) * rate2 * months);
                let panelty_months = Math.ceil(panelty_dif / 30);
                let panelty_charge = ((amount / 100) * 0.5 * panelty_months);
                interest = interest_set + panelty_charge;
            } else if (receipt_name === "D") {
                interest = (((amount / 100) * rate2 ) * months);
            } else {
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


        // Calling the total payment function
        redeemTotalCalculation();
        // Calculate the total payment
       function redeemTotalCalculation() {
    let amount = parseFloat({{ $receiptData[0]['Pawn_Amount'] }}) || 0;
    let discount = parseFloat($('#redeem_discount').val()) || 0;
    let enteredPayment = parseFloat($('#interest_Paid').val()) || 0;
    let interest_to_pay = parseFloat($('#interest').val()) || 0;
    let document_charges = parseFloat({{ $financial['service_charge'] ?? 0 }}) || 0;
    let stamp_duty = parseFloat($('#stampduty').val()) || 0;
    let letter_pay_one = parseFloat({{ $receiptData[0]['letter_pay_one'] }}) || 0;
    let letter_pay_two = parseFloat({{ $receiptData[0]['letter_pay_two'] }}) || 0;
    let letter_pay_three = parseFloat({{ $receiptData[0]['letter_pay_three'] }}) || 0;


    let Postage_Charges = parseFloat({{ $financial['letter_charge'] ?? 0 }}) || 0;
    let chargesDue = Math.max(0, interest_to_pay + document_charges + stamp_duty + Postage_Charges - discount);
    let paymentReceived = Math.max(0, enteredPayment - discount);
    let totalPay = amount + chargesDue;

    if (totalPay < 0) totalPay = 0;

    $('#redeem_total').val(totalPay.toFixed(2));
    $('#total').val(amount.toFixed(2));
    $('#BalanceInterest').val(Math.max(0, chargesDue - paymentReceived).toFixed(2));
    $('#Postage_interest').val((interest_to_pay + Postage_Charges).toFixed(2));

    let advancePayment = Math.min(amount, Math.max(0, paymentReceived - chargesDue));
    $('#advance_payment').val(advancePayment > 0 ? advancePayment.toFixed(2) : '0.00');

    let pawningPayment = amount - advancePayment;
    $('#pawning_payment').val(pawningPayment > 0 ? pawningPayment.toFixed(2) : '0.00');

    $('#payable_total').val(paymentReceived.toFixed(2));

    let paidCharges = Math.min(paymentReceived, chargesDue);
    $('#PayTotalAmount').val(paidCharges > 0 ? paidCharges.toFixed(2) : '0.00');

    // Interest Calculation Section
    let totalvalueinterestTotal = parseFloat('{{ $receiptData[0]['Total_Amount'] }}') || 0;
    let silverPayment = @json(strtoupper((string) ($receiptData[0]['receiptname'] ?: $receiptData[0]['Receipt_Type'])) === 'SILVER');
    let interestRatetwo;

    if (silverPayment) {
        interestRatetwo = {{ (float) ($receiptTypeData->first()->rate1 ?? 0) }};
    } else if (totalvalueinterestTotal >= 100000) {
        interestRatetwo = 1.68;
    } else if (totalvalueinterestTotal >= 50000) {
        interestRatetwo = 2;
    } else {
        interestRatetwo = 2.5;
    }

    let months_interest = totalvalueinterestTotal / 100 * interestRatetwo;
    let Valid_Period = silverPayment ? 1 : 12;

    let Interests = [];
    for (let i = 0; i < 12; i++) {
        Interests[i] = (Valid_Period >= i + 1) ? totalvalueinterestTotal - (months_interest * i) : null;
        setInterestField(`Interest${i + 1}`, Interests[i]);
    }

    $('#redeem_total').val(totalPay.toFixed(2));
    $('#redeem_ammount').val(totalPay.toFixed(2));
    $('#ammount').val(totalvalueinterestTotal.toFixed(2));
    $('#Postage_Charges').val(Postage_Charges.toFixed(2));
}

function setInterestField(id, value) {
    let field = $('#' + id);
    field.val(value !== null ? value.toFixed(2) : '');
    if (value === null) {
        field.closest('td').hide();
    } else {
        field.closest('td').show();
    }
}

// Event listener to trigger the calculation
$(document).on('input', '#redeem_discount,#total,#BalanceInterest,#Postage_interest,#PayTotalAmount,#advance_payment,#interest_Paid,#interest,#document_charges,#stampduty,#pawning_payment,#Postage_Charges', function () {
    redeemTotalCalculation();
});

$('#redeem_discount').on('keyup', function (e) {
    e.preventDefault();
    redeemTotalCalculation();
});



    });

</script>
