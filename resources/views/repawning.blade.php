@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <title>Repawning Receipt</title>
</head>

<body>

    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">

                <div class="row">
                    <div class="col-sm-12">

                        {{-- Session alerts --}}
                        @if (session('delete'))
                            <div class="alert alert-danger text-center" role="alert">
                                {{ session('delete') }} &#10004;
                            </div>
                        @endif

                        @if (session('added'))
                            <div class="alert alert-success text-center" role="alert">
                                {{ session('added') }} &#10004;
                            </div>
                        @endif

                        <div class="card shadow">
                            <div class="col-md-9">
                                <h4 class="card-title m-3">Repawning Receipt</h4>
                            </div>
                            <hr size="6" style="color: blue">
                            <div class="card-body">

                                {{-- Validation errors --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger" role="alert">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Success after repawning: open PDFs automatically --}}
                                @if (Session::has('done'))
                                    <div class="alert alert-success text-center">
                                        <p>{{ Session::get('done') }}</p>
                                    </div>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            var pdfLink1 = "{{ Session::get('pdfLink1') }}";
                                            var pdfLink2 = "{{ Session::get('pdfLink2') }}";

                                            var window1 = window.open(pdfLink1, '_blank');
                                            if (window1) {
                                                window1.onload = function() {
                                                    window1.print();
                                                    setTimeout(function() {
                                                        var window2 = window.open(pdfLink2, '_blank');
                                                        if (window2) {
                                                            window2.onload = function() {
                                                                window2.print();
                                                            };
                                                        }
                                                    }, 1000);
                                                };
                                            }
                                        });
                                    </script>
                                @endif

                                <form action="{{ route('Store_RepawningSum') }}" method="post">
                                    @csrf

                                    {{-- ── Search inputs ───────────────────────────────────── --}}
                                    <div class="row form-group">

                                        <div class="col-md-4 mb-1">
                                            <label>Receipt Number :
                                                <input class="form-control" type="text"
                                                    placeholder="Type Receipt Number:"
                                                    name="receipt_number" id="search_receipt" required>
                                            </label>
                                        </div>

                                        <div class="col-md-4 mb-1">
                                            <label>Ticket Number :
                                                <input class="form-control" type="text"
                                                    placeholder="Type Ticket Number:"
                                                    name="ticket_number" id="search_ticket">
                                            </label>
                                        </div>

                                        @if(auth()->user()->role === 'Admin')
                                            <div class="col-md-4 mb-1">
                                                <label>Invoice Number :
                                                    <input class="form-control" type="text"
                                                        name="invoice_number" id="search_invoice" required>
                                                </label>
                                            </div>
                                        @else
                                            <input type="hidden" name="invoice_number" value="AUTO" id="search_invoice">
                                        @endif

                                        {{-- ── Dynamic search result area ─────────────────── --}}
                                        <div class="dynamic-area">
                                            <div class="col">
                                                <br>
                                                <div class="row">
                                                    <div class="col">
                                                        <label>Receipt Type :
                                                            <input class="form-control" type="text"
                                                                placeholder="Receipt type"
                                                                name="receipt_type" id="receipt_type" readonly />
                                                        </label>
                                                    </div>
                                                    <div class="col">
                                                        <label>Date :
                                                            <input class="form-control" id="date" type="date"
                                                                placeholder="Date" name="date">
                                                        </label>
                                                    </div>
                                                    <div class="col">
                                                        <label>Redeem Number :
                                                            <input class="form-control" type="text"
                                                                placeholder="Redeem Number:"
                                                                id="redeem_no" name="redeem_no">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h5 class="mt-3">Receipt Info</h5>
                                            <table class="table table-bordered text-center">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Final Date</th>
                                                        <th>Receipt Date Time</th>
                                                        <th>Period (Months)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><p>..</p></td>
                                                        <td><p>..</p></td>
                                                        <td><p>..</p></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <br>

                                            <h5 class="mt-3">Customer Info</h5>
                                            <table class="table table-bordered text-center">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Customer address and contact number</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><h4>Customer name and ID</h4></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <br>

                                            <div class="row" style="align-content: center;">
                                                <div class="col-md-6">
                                                    <button class="btn btn-outline-info form-control" type="button">
                                                        View Article Details
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <button class="btn btn-outline-info form-control" type="button" data-bs-toggle="modal" data-bs-target="#viewPaymentHistoryModel">
                                                        Payment History
                                                    </button>
                                                </div>
                                            </div>
                                            <br>

                                            <h5 class="mt-3">Repawning Details</h5>
                                            <table class="table table-bordered text-center">
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
                                                </tbody>
                                            </table>
                                            <br>
                                        </div>
                                        {{-- end .dynamic-area --}}

                                    </div>

                                    {{-- ── Amount row ──────────────────────────────────────── --}}
                                    <div class="row ml-3 mb-4">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td style="width: 50%"></td>
                                                    <th><label>REDEEM AMOUNT :</label></th>
                                                    <td>
                                                        <input class="form-control" type="text"
                                                            placeholder="TOTAL :"
                                                            id="redeem_total" name="redeem_total">
                                                    </td>
                                                </tr>
                                                <tr id="REPAWNING">
                                                    <td></td>
                                                    <th><label>REPAWNING AMOUNT :</label></th>
                                                    <td>
                                                        <input type="hidden" id="totalvalueinterst" readonly>
                                                        <input class="form-control" type="text"
                                                            placeholder="AMOUNT :"
                                                            id="payable_total" name="payable_total" required>
                                                        <input type="hidden" id="validyed_type">
                                                        <br>
                                                        <input type="hidden" id="matched_interest_index"
                                                            name="validyed_type">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- ── Action buttons ──────────────────────────────────── --}}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <button class="btn btn-lg btn-outline-info form-control"
                                                name="redeem" type="submit">REDEEM</button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-lg btn-outline-primary form-control"
                                                name="print" type="button">PRINT</button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-lg btn-outline-danger form-control"
                                                name="cancel" type="button">CANCEL</button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-lg btn-outline-secondary form-control"
                                                name="reset" type="reset">RESET</button>
                                        </div>
                                    </div>

                                </form>
                            </div>{{-- card-body --}}
                        </div>{{-- card --}}
                    </div>{{-- col-sm-12 --}}
                </div>{{-- row --}}
            </div>{{-- container-fluid --}}
        </div>{{-- page-wrapper --}}
    </div>{{-- main-wrapper --}}


    {{-- ── Article Details Modal ───────────────────────────────── --}}
    <div class="modal fade" id="viewArticleDetailsModel" tabindex="-1" role="dialog"
        aria-labelledby="viewArticleDetailsLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title m-2">Article Details History</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="text-align:center;">Category</th>
                                <th style="text-align:center;">Articles</th>
                                <th style="text-align:center;">Condition</th>
                                <th style="text-align:center;">Karatage</th>
                                <th style="text-align:center;">Weight</th>
                                <th style="text-align:center;">QTY</th>
                                <th style="text-align:center;">Value</th>
                                <th style="text-align:center;">Date</th>
                            </tr>
                        </thead>
                        <tbody id="articleDetails"></tbody>
                    </table>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Payment History Modal ───────────────────────────────── --}}
    <div class="modal fade" id="viewPaymentHistoryModel" tabindex="-1" role="dialog"
        aria-labelledby="viewPaymentHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title m-2">Payment History <span id="paymentHistoryReceiptInfo" class="badge bg-primary ms-2 fs-6"></span></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead>
                                <tr style="background-color: rgb(12,119,241); color: aliceblue;">
                                    <th style="text-align: center;">Date</th>
                                    <th style="text-align: center;">Payment Type</th>
                                    <th style="text-align: center;">Payment Amount</th>
                                    <th style="text-align: center;">Customer Paid</th>
                                    <th style="text-align: center;">Paid Capital</th>
                                    <th style="text-align: center;">Paid Interest</th>
                                    <th style="text-align: center;">Repawn Amount</th>
                                    <th style="text-align: center;">Letters Sent</th>
                                    <th style="text-align: center;">Remaining Amount</th>
                                    <th style="text-align: center;">Extend Date</th>
                                </tr>
                            </thead>
                            <tbody id="CustomerDetails"></tbody>
                        </table>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- ── Set today's date ────────────────────────────────────── --}}
    <script>
        document.getElementById('date').valueAsDate = new Date();
    </script>

    {{-- ── CSRF for AJAX ───────────────────────────────────────── --}}
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>

    {{-- ── Search by Receipt Number ────────────────────────────── --}}
    <script>
        $(document).ready(function () {

            $('#search_receipt').on('keyup', function (e) {
                e.preventDefault();
                clearTimeout($(this).data('timer'));

                $(this).data('timer', setTimeout(function () {
                    $('#search_ticket').val('');
                    $('#search_invoice').val('');

                    let search_receipt_no = $('#search_receipt').val();
                    if (!search_receipt_no) return;

                    // Load dynamic area (receipt + customer info + repawning details)
                    $.ajax({
                        url: "{{ route('search_repawning_receipt_ajax') }}",
                        method: 'GET',
                        data: { search_receipt_no: search_receipt_no },
                        success: function (response) {
                            if (response.status === 'not_found') {
                                $('.dynamic-area').html(
                                    '<span class="text-danger text-center">Receipt not found ...!</span>'
                                );
                                return;
                            }
                            $('.dynamic-area').html(response);

                            // Sync ticket / invoice fields
                            $('#search_ticket').val($('#t_number').val());
                            $('#search_invoice').val($('#i_number').val());
                        }
                    });

                    // Load article details table
                    $.ajax({
                        url: "{{ route('view_article_details_ajax') }}",
                        method: 'GET',
                        data: { search_receipt_no: search_receipt_no },
                        success: function (response) {
                            if (response.status === 'success') {
                                let rows = '';
                                response.data.forEach(r => {
                                    rows += `<tr>
                                        <td>${r.Category}</td>
                                        <td>${r.Articles}</td>
                                        <td>${r.Condition}</td>
                                        <td>${r.Karatage}</td>
                                        <td>${r.Weight}</td>
                                        <td>${r.QTY}</td>
                                        <td>${r.Value}</td>
                                        <td>${r.Date}</td>
                                    </tr>`;
                                });
                                $('#articleDetails').html(rows);
                            }
                        }
                    });

                    // Load payment history table
                    loadPaymentHistory(search_receipt_no);

                }, 300));
            });
        });

        function loadPaymentHistory(search_receipt_no) {
            if (!search_receipt_no) {
                $('#CustomerDetails').html('');
                $('#paymentHistoryReceiptInfo').text('');
                return;
            }
            $('#CustomerDetails').html('<tr><td colspan="10" class="text-center">Loading...</td></tr>');
            $('#paymentHistoryReceiptInfo').text('Receipt / Ticket #' + search_receipt_no);

            $.ajax({
                url: "{{ route('view_dynamicCusDetailsView_details_ajax') }}",
                method: 'GET',
                data: { search_receipt_no: search_receipt_no },
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

                        $('#CustomerDetails').html(tableRows);
                    } else {
                        $('#CustomerDetails').html('<tr><td colspan="10" class="text-center">No payment history found</td></tr>');
                    }
                },
                error: function () {
                    $('#CustomerDetails').html('<tr><td colspan="10" class="text-center text-danger">Error fetching payment history</td></tr>');
                }
            });
        }
    </script>

    {{-- ── Search by Ticket Number ─────────────────────────────── --}}
    <script>
        $(document).ready(function () {
            $('#search_ticket').on('keyup', function (e) {
                e.preventDefault();
                clearTimeout($(this).data('timer'));

                $(this).data('timer', setTimeout(function () {
                    $('#search_receipt').val('');
                    $('#search_invoice').val('');

                    let search_receipt_no = $('#search_ticket').val();
                    if (!search_receipt_no) return;

                    $.ajax({
                        url: "{{ route('search_ticket_ajax') }}",
                        method: 'GET',
                        data: { search_receipt_no: search_receipt_no },
                        success: function (response) {
                            if (response.status === 'not_found') {
                                $('.dynamic-area').html(
                                    '<span class="text-danger text-center">Receipt not found ...!</span>'
                                );
                                return;
                            }
                            $('.dynamic-area').html(response);
                            $('#search_receipt').val($('#r_number').val());
                            $('#search_invoice').val($('#i_number').val());
                        }
                    });

                    $.ajax({
                        url: "{{ route('view_article_details_ajax') }}",
                        method: 'GET',
                        data: { search_receipt_no: search_receipt_no },
                        success: function (response) {
                            if (response.status === 'success') {
                                let rows = '';
                                response.data.forEach(r => {
                                    rows += `<tr>
                                        <td>${r.Category}</td><td>${r.Articles}</td>
                                        <td>${r.Condition}</td><td>${r.Karatage}</td>
                                        <td>${r.Weight}</td><td>${r.QTY}</td>
                                        <td>${r.Value}</td><td>${r.Date}</td>
                                    </tr>`;
                                });
                                $('#articleDetails').html(rows);
                            }
                        }
                    });

                    loadPaymentHistory(search_receipt_no);
                }, 300));
            });
        });
    </script>

    {{-- ── Search by Invoice Number (Admin only) ───────────────── --}}
    <script>
        $(document).ready(function () {
            $('#search_invoice').on('keyup', function (e) {
                e.preventDefault();
                $('#search_ticket').val('');
                $('#search_receipt').val('');
                $('#redeem_total').val('');

                let search_invoice_no = $(this).val();
                if (!search_invoice_no) return;

                $.ajax({
                    url: "{{ route('search_invoice_ajax') }}",
                    method: 'GET',
                    data: { search_invoice_no: search_invoice_no },
                    success: function (response) {
                        if (response.status === 'not_found') {
                            $('#search_receipt').val('');
                            $('.dynamic-area').html(
                                '<span class="text-danger text-center">Receipt not found ...!</span>'
                            );
                            return;
                        }
                        $('.dynamic-area').html(response);
                        $('#search_receipt').val($('#r_number').val());
                    }
                });
            });
        });
    </script>

    {{-- ── Payable total validation ────────────────────────────── --}}
    <script>
        document.getElementById('payable_total').addEventListener('input', function () {
            let totalValue  = parseFloat(document.getElementById('totalvalueinterst').value) || 0;
            let payableValue = parseFloat(this.value) || 0;

            if (payableValue > totalValue) {
                alert('❌ Payable total cannot be greater than Total Value Interest!');
                this.value = '';
                this.focus();
            }
        });
    </script>

    {{-- ── Match payable_total to closest Interest(1-12) ─────────── --}}
    <script>
        document.getElementById('payable_total').addEventListener('input', function () {
            const targetValue = parseFloat(this.value);
            if (isNaN(targetValue)) return;

            let closestValue = null;
            let closestIndex = null;
            let smallestDiff  = Infinity;

            for (let i = 1; i <= 12; i++) {
                const el    = document.getElementById('Interest' + i);
                if (!el) continue;
                const value = parseFloat(el.value);
                if (isNaN(value)) continue;

                const diff = Math.abs(value - targetValue);
                if (diff < smallestDiff) {
                    smallestDiff  = diff;
                    closestValue  = value;
                    closestIndex  = i;
                }
            }

            document.getElementById('validyed_type').value         = closestValue;
            document.getElementById('matched_interest_index').value = closestIndex;
        });
    </script>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="assets/js/script.js"></script>

</body>
</html>
@endsection