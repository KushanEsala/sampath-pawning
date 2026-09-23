@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <title>Pawning Part Payment</title>

</head>

<body>

    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">

                <div class="row">
                    <div class="col-sm-12">
                        @if (session('delete'))
                        <div class="alert alert-danger text-center" role="alert">
                            {{session('delete')}} &#10004;
                        </div>
                        @endif

                        @if (session('added'))
                        <div class="alert alert-success text-center" role="alert">
                            {{session('added')}} &#10004;
                        </div>
                        @endif

                        <div class="card shadow">
                            <div class="col-md-9" >
                                <h4 class="card-title m-3">Pawning Part Payment </h4>
                            </div>
                            <hr size="6" style="color: rgb(0, 0, 5)">
                            <div class="card-body" style="background-color: aliceblue">

                                {{-- alert section --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger" role="alert">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if (Session::has('done'))
                                    <div class="alert alert-success text-center">
                                        <p>{{ Session::get('done') }}</p>
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            // Replace 'your_pdf_link_here' with the actual variable containing the PDF link
                                            var pdfLink = "{{ Session::get('pdfLink') }}";
                                            var newWindow = window.open(pdfLink, '_blank');

                                            // Wait for the new window load, then trigger the print function
                                            newWindow.onload = function () {
                                                newWindow.print();
                                            };
                                        });
                                    </script>
                                @endif

                                <form action="{{ route('Store_part_payment') }}" method="post" id="partPaymentForm">
                                    @csrf

                                    <div class="row mb-1 form-group">
                                        <div class="col-md-4">
                                        </div>
                                        <div class="col-md-4"></div>
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">Payment No :</span>
                                                <input class="form-control" type="text" placeholder="Redeem Number:"
                                                value="{{ $maxRedeem+1 }}"
                                                id="redeem_no" name="redeem_no" required>
                                              </div>
                                        </div>
                                    </div>

                                    <div class="row mb-1 form-group">
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">Receipt Number :</span>
                                                <input class="form-control" type="text" placeholder="Type Receipt Number:"
                                                name="receipt_number" id="search_receipt" required>
                                              </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" >Ticket Number  :</span>
                                                <input class="form-control" type="text" placeholder="Type Ticket Number:"
                                                name="ticket_number" id="search_ticket">
                                              </div>
                                        </div>

                                       
                                                                                                                  @if(auth()->user()->role === 'Admin')
    <div class="col-md-4 mb-1">
        <label>Invoice Number :</label>
        <input class="form-control"
               type="text"
               name="invoice_number"
               id="search_invoice"
               required>
    </div>
@else
    <input type="hidden" name="invoice_number" value="AUTO" id="search_invoice">
@endif

                                    </div>
                                    <br>

                                        <div class="dynamic-area">
                                            <table class="table table-bordered text-center" id="dynamicAdded">

                                            </table>
                                            <br>

                                            <table class="table table-bordered text-center" id="dynamicAdded">

                                            </table>
                                            <br>
                                            <br>

                                            <div class="row" style="align-content: center;">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <button class="btn btn-outline-info form-control" type="button">View Article Details</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-outline-info form-control" type="button" data-bs-toggle="modal" data-bs-target="#viewPaymentHistoryModel">Payment History</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <h5 class="mt-3">Redeem Details</h5>
                                            <table class="table table-bordered text-center" id="dynamicAdded">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Original Pawn Amount</th>
                                                        <th>Payable Pawn Amount</th>
                                                        <th>Paid Interest</th>
                                                        <th>Payable Interest</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><p>..</p></td>
                                                        <td><p>..</p></td>
                                                        <td><p>..</p></td>
                                                        <td><p>..</p></td>
                                                    </tr>
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>
                                                            Stamp Fee
                                                        </th>
                                                        <th>
                                                            Other Charges
                                                        </th>
                                                        <th>
                                                            Advance Balance
                                                        </th>
                                                    </tr>
                                                </thead>
                                                    <tr>
                                                        <td><p>..</p></td>
                                                        <td><p>..</p></td>
                                                        <td><p>..</p></td>

                                                    </tr>
                                                </tbody>
                                            </table>
                                            <br>
                                        </div>
                                    </div>


                                        <div class="row" style="display: none">
                                            <div class="col-md-2"></div>
                                            <div class="col-md-2">
                                                <input class="form-control" type="text" placeholder="Difference" id="interest_Paid_check" readonly>
                                            </div>

                                            <div class="col-md-2">
                                             <input type="text" class="form-control" id="validyed_type" name="validyed_type" readonly>
                                            </div>
                                            <div class="col-md-2"></div>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="matched_interest_index" name="validyed_type" readonly >
                                            </div>
                                        <div class="col-md-2"></div>
                                        </div>
                                        <br>
                                        <br>

                                    <div class="row ml-6 mb-4">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td style="width: 15%"></td>
                                                    <th style="width: 25%">
                                                        <label for="amount">TOTAL:</label>
                                                    </th>
                                                    <td>
                                                        <input class="form-control" type="text" placeholder="TOTAL:" id="redeem_total" name="redeem_total" readonly>
                                                    </td>
                                                </tr>

                                                <tr style="display: none;">
                                                    <td style="width: 15%"></td>
                                                    <th style="width: 25%">
                                                        <label for="advance_payment">INTEREST PAYMENT :</label>
                                                    </th>
                                                    <td>
                                                        <input class="form-control" type="text" placeholder="INTEREST PAYMENT:" id="PayTotalAmount" name="PayTotalAmount">
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="width: 15%"></td>
                                                    <th style="width: 25%">
                                                        <label for="advance_payment">INTEREST &  POSTAGE:</label>
                                                    </th>
                                                    <td>

                                                      <input class="form-control" type="text" placeholder="INTEREST & POSTAGE:" id="Postage_interest" name="Postage_interest" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 15%"></td>
                                                    <th style="width: 25%">
                                                        <label for="advance_payment">PAY TOTAL AMOUNT:</label>
                                                    </th>
                                                    <td>

                                                      <input class="form-control" type="text" placeholder="PAY TOTAL AMOUNT:" id="interest_Paid" name="interest_Paid">
                                                    </td>
                                                </tr>
                                                <tr style="display: none;">
                                                    <td style="width: 15%"></td>
                                                    <th style="width: 25%">
                                                        <label for="advance_payment" >ADVANCE PAYMENT:</label>
                                                    </th>
                                                    <td>

                                                        <input class="form-control" type="text" placeholder="ADVANCE PAYMENT:" id="advance_payment" name="advance_payment">
                                                    </td>
                                                </tr>

                                                <tr style="display: none;">
                                                    <td style="width: 15%"></td>
                                                    <th style="width: 25%">
                                                        <label for="advance_payment" >PAWNING PAYMENT:</label>
                                                    </th>
                                                    <td>

                                                        <input class="form-control" type="text" placeholder="ADVANCE PAYMENT:" id="pawning_payment" name="Payable_Pawn_Amount">
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="width: 50%"></td>
                                                    <th style="width: 15%">
                                                        <label for="redeem_discount">DISCOUNT:</label>
                                                    </th>
                                                    <td>
                                                        <input class="form-control" type="text" placeholder="DISCOUNT:" id="redeem_discount" name="redeem_discount">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <th>
                                                        <label for="payable_total">PAYABLE TOTAL:</label>
                                                    </th>
                                                    <td>
                                                        <input class="form-control" type="text" placeholder="PAYABLE TOTAL:" id="payable_total" name="payable_total" readonly>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td rowspan="2">&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <br>

                                        <!-- Validation Error Message Container -->
                                        <div id="validationErrorContainer"></div>

                                        <div class="row">
                                            <div class="col-md-2"></div>
                                            <div class="col-md-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-lg btn-info form-control" name="redeem" type="submit">SAVE</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-lg btn-warning form-control" name="print" type="button">PRINT</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-lg btn-success form-control" name="reset" type="reset">RESET</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- show Article Details model --}}
    <div class="modal fade" id="viewArticleDetailsModel" tabindex="-1" role="dialog"
        aria-labelledby="viewArticleDetailsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title m-2" id="viewArticleDetailsLabel"> Article Details History </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="errMsgContainer"></div>
                                    <form action="" method="post" id="addCustomer">
                                        @csrf
                                        <div class="row">

                                            <table class="table table-bordered" >
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width:8%; text-align: center;">Category</th>
                                                        <th style="width:8%; text-align: center;">Articles</th>
                                                        <th style="width:8%; text-align: center;">Condition</th>
                                                        <th style="width:8%; text-align: center;">Karatage</th>
                                                        <th style="width:8%; text-align: center;">Weight</th>
                                                        <th style="width:8%; text-align: center;">QTY</th>
                                                        <th style="width:8%; text-align: center;">Value</th>
                                                        <th class="text-center" style="width:8%;">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="articleDetails">
                                                </tbody>
                                            </table>

                                            {{-- dynamicAdded table --}}
                                            <table class="table table-bordered" id="dynamicArticlesView">


                                            </table>
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-success add_customer bg-success-light text-success me-2">Save</button>
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
                                        </div>
                                </form>
                            </div>
                        </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- show Payment History model --}}
<div class="modal fade" id="viewPaymentHistoryModel" tabindex="-1" role="dialog"
   aria-labelledby="viewPaymentHistoryLabel" aria-hidden="true">
   <div class="modal-dialog modal-xl modal-dialog-centered">
   <div class="modal-content">
       <div class="modal-header">
           <h4 class="modal-title m-2" id="viewPaymentHistoryLabel">Payment History <span id="paymentHistoryReceiptInfo" class="badge bg-primary ms-2 fs-6"></span></h4>
           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
           <div class="row">
               <div class="col-md-12">
                   <div class="card mb-0">
                       <div class="card-body">
                           <div class="table-responsive">
                               <table class="table table-bordered table-hover align-middle mb-0 receipt-ledger-table">
                                   <thead>
                                        <tr><th class="ledger-date">Date</th><th class="ledger-description">Description</th><th class="ledger-money">DR</th><th class="ledger-money">CR</th><th class="ledger-money">Balance</th></tr>
                                    </thead>
                                   <tbody id="CustomerDetails">
                                   </tbody>
                                   <tfoot><tr><th colspan="2" class="text-end">Ledger totals / Current balance</th><th id="historyTotalDr" class="ledger-money ledger-dr">0.00</th><th id="historyTotalCr" class="ledger-money ledger-cr">0.00</th><th id="historyCurrentBalance" class="ledger-money ledger-balance">0.00</th></tr></tfoot>
                               </table>
                           </div>
                           <div class="text-center mt-3">
                               <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   </div>
</div>
@include('partials.receiptLedgerAssets')
  </div>

{{-- form default date set for today --}}
<script>
    document.getElementById('date').valueAsDate = new Date();
</script>

<script>
    document.getElementById('receipt_date').valueAsDate = new Date();
</script>

{{-- CSRF Token --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

{{-- search scripts --}}
<script>
    function loadPaymentHistory(receiptNo) {
        if (!receiptNo) {
            $('#CustomerDetails').html('');
            $('#paymentHistoryReceiptInfo').text('');
            return;
        }
        $('#CustomerDetails').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        $('#paymentHistoryReceiptInfo').text('Receipt / Ticket #' + receiptNo);

        $.ajax({
            url: "{{ route('view_dynamicCusDetailsView_details_ajax_partpayment') }}",
            method: 'GET',
            data: { search_receipt_no: receiptNo },
            success: function (response) {
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    let data = response.data;
                    let tableRows = '';

                    data.forEach(record => {
                        let type = (record.trans_type || '').toUpperCase();
                        let amountBadge = '';
                        let customerPaid = '-';
                        let paidCapital = '-';
                        let paidInterest = '-';
                        let repawnAmount = '-';

                        if (type === 'PAWN') {
                            let amt = Number(record.Cr_amount || record.trans_amount || record.Pawn_Amount || 0);
                            amountBadge = `<span class="badge bg-success text-white">Credit: ${amt.toFixed(2)}</span>`;
                            paidCapital = '-';
                            paidInterest = '-';
                        } else if (type === 'PART_PAYMENT' || type === 'REDEEM') {
                            let amt = Number(record.Dr_amount || record.payable_total || record.trans_amount || 0);
                            amountBadge = `<span class="badge bg-danger text-white">Debit: ${amt.toFixed(2)}</span>`;
                            customerPaid = record.payable_total ? Number(record.payable_total).toFixed(2) : (record.Dr_amount ? Number(record.Dr_amount).toFixed(2) : '-');
                            paidCapital = Number(record.Paided_Captional || (type === 'REDEEM' ? record.Pawn_Amount : 0)).toFixed(2);
                            paidInterest = Number(record.Paided_Interest || 0).toFixed(2);
                        } else if (type === 'REPAWNING') {
                            let amt = Number(record.Cr_amount || record.payable_total || record.trans_amount || 0);
                            amountBadge = `<span class="badge bg-success text-white">Credit: ${amt.toFixed(2)}</span>`;
                            repawnAmount = amt > 0 ? amt.toFixed(2) : '-';
                            customerPaid = record.payable_total ? Number(record.payable_total).toFixed(2) : '-';
                            paidCapital = Number(record.Paided_Captional || 0).toFixed(2);
                            paidInterest = Number(record.Paided_Interest || 0).toFixed(2);
                        } else if (type.includes('LETTER')) {
                            let amt = Number(record.trans_amount || record.Postage_charge || record.letter_charge || 0);
                            amountBadge = `<span class="badge bg-warning text-dark">Postal: Rs. ${amt.toFixed(2)}</span>`;
                        } else if (type.includes('CHARGE')) {
                            let amt = Number(record.trans_amount || record.Postage_charge || 0);
                            amountBadge = `<span class="badge bg-warning text-dark">Service: Rs. ${amt.toFixed(2)}</span>`;
                        } else {
                            let amt = Number(record.trans_amount || 0);
                            amountBadge = `<span>${amt.toFixed(2)}</span>`;
                        }

                        // Display letters only on letter charge rows (parallel timeframe where letter was actually sent)
                        let lettersDisplay = '-';
                        if (type.includes('LETTER') || record.letter_sent) {
                            let letterName = record.letter_sent || record.trans_type || 'Letter Sent';
                            let postalAmt = Number(record.trans_amount || record.Postage_charge || record.letter_charge || 0);
                            lettersDisplay = `<span class="badge bg-primary text-white">${letterName}</span><br><small class="text-muted">Postal: Rs. ${postalAmt.toFixed(2)}</small>`;
                        }
                        let remainingDisplay = record.remaining_display || '-';

                        tableRows += `
                            <tr>
                                <td class="text-center">${record.dDate ?? '-'}</td>
                                <td class="text-center"><strong>${record.trans_type || '-'}</strong></td>
                                <td class="text-center">${amountBadge}</td>
                                <td class="text-end" style="color: green; font-weight: bold;">${customerPaid}</td>
                                <td class="text-end">${paidCapital}</td>
                                <td class="text-end">${paidInterest}</td>
                                <td class="text-end">${repawnAmount}</td>
                                <td class="text-center small">${lettersDisplay}</td>
                                <td class="text-end">${remainingDisplay}</td>
                                <td class="text-center">${record.Extend_Date ?? '-'}</td>
                            </tr>
                        `;
                    });

                    tableRows = window.ReceiptHistoryLedger.renderRows(data);
                    window.ReceiptHistoryLedger.updateTotals(data);
                    $('#CustomerDetails').html(tableRows);
                } else {
                    window.ReceiptHistoryLedger.updateTotals([]);
                    $('#CustomerDetails').html('<tr><td colspan="5" class="text-center">No payment history found</td></tr>');
                }
            },
            error: function () {
                window.ReceiptHistoryLedger.updateTotals([]);
                $('#CustomerDetails').html('<tr><td colspan="5" class="text-center text-danger">Error fetching payment history</td></tr>');
            }
        });
    }

    function loadArticleDetails(receiptNo) {
        if (!receiptNo) {
            $('#articleDetails').html('');
            return;
        }
        $.ajax({
            url: "{{ route('view_article_details_ajax') }}",
            method: 'GET',
            data: { search_receipt_no: receiptNo },
            success: function (response) {
                if (response.status === 'success' && response.data) {
                    let tableRows = '';
                    response.data.forEach(record => {
                        tableRows += `
                            <tr>
                                <td>${record.Category}</td>
                                <td>${record.Articles}</td>
                                <td>${record.Condition}</td>
                                <td>${record.Karatage}</td>
                                <td>${record.Weight}</td>
                                <td>${record.QTY}</td>
                                <td>${record.Value}</td>
                                <td>${record.Date}</td>
                            </tr>`;
                    });
                    $('#articleDetails').html(tableRows);
                } else {
                    $('#articleDetails').html('');
                }
            }
        });
    }

    $(document).ready(function () {
        // Search by Receipt Number
        $('#search_receipt').on('keyup', function (e) {
            e.preventDefault();
            clearTimeout($(this).data('timer'));

            $(this).data('timer', setTimeout(function () {
                $('#redeem_discount').val('');
                $('#search_ticket').val('');
                $('#search_invoice').val('');

                let search_receipt_no = $('#search_receipt').val().trim();
                if (!search_receipt_no) {
                    $('.dynamic-area').html('');
                    $('#CustomerDetails').html('');
                    $('#articleDetails').html('');
                    $('#paymentHistoryReceiptInfo').text('');
                    return;
                }

                $.ajax({
                    url: "{{ route('search_part_payment_receipt_ajax') }}",
                    method: 'GET',
                    data: { search_receipt_no: search_receipt_no },
                    success: function (response) {
                        if (response.status === 'not_found') {
                            $('.dynamic-area').html('<span class="text-danger text-center">Receipt not found ...!</span>');
                            $('#CustomerDetails').html('<tr><td colspan="10" class="text-center">No payment history found</td></tr>');
                            $('#articleDetails').html('');
                            return;
                        }

                        $('.dynamic-area').html(response);

                        let t_number = $('#t_number').val();
                        let i_number = $('#i_number').val();
                        let r_number = $('#r_number').val() || search_receipt_no;
                        if (t_number) $('#search_ticket').val(t_number);
                        if (i_number) $('#search_invoice').val(i_number);

                        loadArticleDetails(r_number);
                        loadPaymentHistory(r_number);
                    }
                });
            }, 400));
        });

        // Search by Ticket Number
        $('#search_ticket').on('keyup', function (e) {
            e.preventDefault();
            clearTimeout($(this).data('timer'));

            $(this).data('timer', setTimeout(function () {
                $('#redeem_discount').val('');
                $('#search_receipt').val('');
                $('#search_invoice').val('');

                let search_ticket_no = $('#search_ticket').val().trim();
                if (!search_ticket_no) {
                    $('.dynamic-area').html('');
                    $('#CustomerDetails').html('');
                    $('#articleDetails').html('');
                    $('#paymentHistoryReceiptInfo').text('');
                    return;
                }

                $.ajax({
                    url: "{{ route('search_part_payment_ticket_ajax') }}",
                    method: 'GET',
                    data: { search_receipt_no: search_ticket_no },
                    success: function (response) {
                        if (response.status === 'not_found') {
                            $('.dynamic-area').html('<span class="text-danger text-center">Receipt not found ...!</span>');
                            $('#CustomerDetails').html('<tr><td colspan="10" class="text-center">No payment history found</td></tr>');
                            $('#articleDetails').html('');
                            return;
                        }

                        $('.dynamic-area').html(response);

                        let r_number = $('#r_number').val();
                        let i_number = $('#i_number').val();
                        if (r_number) $('#search_receipt').val(r_number);
                        if (i_number) $('#search_invoice').val(i_number);

                        let targetNo = r_number || search_ticket_no;
                        loadArticleDetails(targetNo);
                        loadPaymentHistory(targetNo);
                    }
                });
            }, 400));
        });

        // Search by Invoice Number
        $('#search_invoice').on('keyup', function (e) {
            e.preventDefault();
            clearTimeout($(this).data('timer'));

            $(this).data('timer', setTimeout(function () {
                $('#redeem_discount').val('');
                $('#search_ticket').val('');
                $('#search_receipt').val('');
                $('#redeem_total').val('');

                let search_invoice_no = $('#search_invoice').val().trim();
                if (!search_invoice_no) {
                    $('.dynamic-area').html('');
                    $('#CustomerDetails').html('');
                    $('#articleDetails').html('');
                    $('#paymentHistoryReceiptInfo').text('');
                    return;
                }

                $.ajax({
                    url: "{{ route('search_part_payment_invoice_ajax') }}",
                    method: 'GET',
                    data: { search_invoice_no: search_invoice_no },
                    success: function (response) {
                        if (response.status === 'not_found') {
                            $('.dynamic-area').html('<span class="text-danger text-center">Receipt not found ...!</span>');
                            $('#CustomerDetails').html('<tr><td colspan="10" class="text-center">No payment history found</td></tr>');
                            $('#articleDetails').html('');
                            return;
                        }

                        $('.dynamic-area').html(response);

                        let r_number = $('#r_number').val();
                        let t_number = $('#t_number').val();
                        if (r_number) $('#search_receipt').val(r_number);
                        if (t_number) $('#search_ticket').val(t_number);

                        let targetNo = r_number || search_invoice_no;
                        loadArticleDetails(targetNo);
                        loadPaymentHistory(targetNo);
                    }
                });
            }, 400));
        });
    });
</script>


<script>
document.getElementById('interest_Paid').addEventListener('input', function () {
    const redeemTotal = parseFloat(document.getElementById('redeem_total').value);
    const interestPaid = parseFloat(this.value);

    if (isNaN(redeemTotal) || isNaN(interestPaid)) {
        document.getElementById('interest_Paid_check').value = '';
        return;
    }

    const diff = redeemTotal - interestPaid;
    document.getElementById('interest_Paid_check').value = diff;

    let closestValue = null;
    let closestIndex = null;
    let smallestDiff = Infinity;

    for (let i = 1; i <= 12; i++) {
        const el = document.getElementById('Interest' + i);
        if (!el) continue;

        const value = parseFloat(el.value);
        if (isNaN(value)) continue;

        const currentDiff = Math.abs(value - diff);
        if (currentDiff < smallestDiff) {
            smallestDiff = currentDiff;
            closestValue = value;
            closestIndex = i;
        }
    }

    // Set the closest value and index to the respective fields
    document.getElementById('validyed_type').value = closestValue ?? '';
    document.getElementById('matched_interest_index').value = closestIndex ?? '';
});
</script>




{{-- FORM VALIDATION SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('partPaymentForm');

    form.addEventListener('submit', function(e) {
        // Get values
        const redeemTotal = parseFloat(document.getElementById('redeem_total').value) || 0;
        const payableTotal = parseFloat(document.getElementById('payable_total').value) || 0;

        // Clear any previous error messages
        const existingError = document.querySelector('.validation-error-message');
        if (existingError) {
            existingError.remove();
        }

        // Validation checks
        let errorMessage = '';
        let isValid = true;

        // Check if both values are entered
        if (redeemTotal === 0 || payableTotal === 0) {
            errorMessage = '⚠️ Error: Please ensure Redeem Total and Payable Total are filled!';
            isValid = false;
        }
        // Check if redeem_total is less than or equal to payable_total
        else if (redeemTotal <= payableTotal) {
            errorMessage = '❌ Error: Redeem Total must be greater than Payable Total!';
            isValid = false;
        }
        // Check if redeem_total has minimum balance of 1000
        else {
            const balance = redeemTotal - payableTotal;
            if (balance < 1000) {
                errorMessage = `❌ Error: Minimum balance of 1000 required! Current balance: ${balance.toFixed(2)}`;
                isValid = false;
            }
        }

        // If there's an error, prevent submission and show message
        if (!isValid) {
            e.preventDefault();

            // Create and display error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger text-center validation-error-message';
            errorDiv.style.marginTop = '20px';
            errorDiv.style.marginBottom = '20px';
            errorDiv.style.fontSize = '16px';
            errorDiv.style.fontWeight = 'bold';
            errorDiv.innerHTML = errorMessage;

            // Insert error message in the validation container
            const container = document.getElementById('validationErrorContainer');
            container.appendChild(errorDiv);

            // Scroll to error message
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Add shake animation
            errorDiv.style.animation = 'shake 0.5s';

            // Auto-remove error after 6 seconds
            setTimeout(() => {
                errorDiv.style.transition = 'opacity 0.5s';
                errorDiv.style.opacity = '0';
                setTimeout(() => errorDiv.remove(), 500);
            }, 6000);

            return false;
        }

        // If validation passes, show success message
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success text-center';
        successDiv.style.marginTop = '20px';
        successDiv.style.marginBottom = '20px';
        successDiv.innerHTML = '✅ Validation Passed! Submitting form...';

        const container = document.getElementById('validationErrorContainer');
        container.appendChild(successDiv);

        return true;
    });
});

// Add shake animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
        20%, 40%, 60%, 80% { transform: translateX(10px); }
    }
`;
document.head.appendChild(style);
</script>


<!-- ... up to Interest12 -->


    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/plugins/select2/js/select2.min.js"></script>
    <script src="assets/plugins/moment/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>

</body>
</html>
<script src="{{ asset('assets/js/payment-receipt-guard.js') }}"></script>
@endsection
