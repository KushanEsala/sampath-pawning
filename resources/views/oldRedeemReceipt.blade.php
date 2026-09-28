@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- jQuery (Only One Version) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- Toastr CSS (For Notifications) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <title>Redeem Receipt</title>
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
                            <div class="col-md-9">
                                <h4 class="card-title m-3">Old Redeem Receipt</h4>
                            </div>
                            <hr size="6" style="color: blue">
                            <div class="card-body">

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

                                <form action="{{ route('store_redeem_old') }}" method="post"  id="redeemForm">
                                    @csrf
                                    <div class="row form-group ">
                                        <div class="col-md-4 mb-1">
                                            <label for="receipt_no">Receipt Number :
                                                <input class="form-control" type="text" placeholder="Type Receipt Number:"
                                                name="receipt_number" id="search_receipt" required>
                                            </label>
                                        </div>
                                        <div class="col-md-4 mb-1">
                                            <label for="receipt_no">Ticket Number :
                                                <input class="form-control" type="text" placeholder="Type Ticket Number:"
                                                name="ticket_number" id="search_ticket">
                                            </label>
                                        </div>
                                                                                                           @if(auth()->user()->role === 'Admin')
    <div class="col-md-4 mb-1">
        <label>Stock Number :</label>
        <input class="form-control"
               type="text"
               name="invoice_number"
               id="search_invoice"
               required placeholder="Type Stock Number:">
    </div>
@else
    <input type="hidden" name="invoice_number" value="AUTO" id="search_invoice">
@endif


                                        <div class="dynamic-area">
                                            <div class="col ">
                                                <br>
                                                <div class="row">
                                                    <div class="col">
                                                        <label for="receipt_type">Receipt Type :
                                                            <input class="form-control"  type="text" placeholder="Receipt type"
                                                                name="receipt_type"
                                                                id="receipt_type" readonly/>
                                                        </label>

                                                    </div>
                                                    <div class="col">
                                                            <label for="date">Date :
                                                                <input class="form-control" id="date" type="date" placeholder="Date" name="date">
                                                            </label>
                                                    </div>
                                                    <div class="col">
                                                        <label for="redeem_no">Redeem Number :
                                                            <input class="form-control" type="text" placeholder="Redeem Number:" id="redeem_no" name="redeem_no">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h5 class="mt-3">Receipt Info</h5>
                                            <table class="table table-bordered text-center" id="dynamicAdded">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Final Date</th>
                                                        <th>Receipt Date Time</th>
                                                        <th>Period (Months)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <p>..</p>
                                                        </td>
                                                        <td>
                                                            <p>..</p>
                                                        </td>
                                                        <td>
                                                            <p>..</p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <br>

                                            <h5 class="mt-3">Customer Info</h5>
                                            <table class="table table-bordered text-center" id="dynamicAdded">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Customer address and contact number</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <h4>Customer name and ID</h4>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
                                            <br>

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

                                    <div class="row ml-3 mb-4">

                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td></td>
                                                        <th><label for="amount">TOTAL : </label></th>
                                                        <td>
                                                            <input class="form-control" type="text" placeholder="TOTAL :" id="redeem_total" name="redeem_total">
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td style="width: 50%"></td>
                                                        <th style="width: 15%">
                                                            <label for="amount">DISCOUNT : </label>
                                                        </th>
                                                        <td>
                                                            <input class="form-control" type="text" placeholder="DISCOUNT :" id="redeem_discount" name="redeem_discount">
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td></td>
                                                        <th><label for="amount">PAYABLE TOTAL : </label></th>
                                                        <td>
                                                            <input class="form-control" type="text" placeholder="AMOUNT :" id="payable_total" name="payable_total" required>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                    </div>


                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button class="btn btn-lg btn-outline-info  form-control" name="redeem" type="submit">REDEEM</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button class="btn btn-lg btn-outline-primary  form-control" name="print" type="button">PRINT</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button class="btn btn-lg btn-outline-danger  form-control" name="cancel" type="button">CANCEL</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button class="btn btn-lg btn-outline-secondary  form-control" name="reset" type="reset">RESET</button>
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
        <div class="modal-dialog modal-xl">
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

                                            <table class="table table-bordered" id="CustomerTable" >
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
<div class="modal fade" id="viewPaymentHistoryModel" tabindex="-1" aria-labelledby="viewPaymentHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title m-2" id="viewPaymentHistoryLabel">Payment History</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 receipt-ledger-table">
                        <thead><tr><th class="ledger-date">Date</th><th class="ledger-description">Description</th><th class="ledger-money">DR</th><th class="ledger-money">CR</th><th class="ledger-money">Balance</th></tr></thead>
                        <tbody id="CustomerDetails"></tbody>
                        <tfoot><tr><th colspan="2" class="text-end">Ledger totals / Current balance</th><th id="historyTotalDr" class="ledger-money ledger-dr">0.00</th><th id="historyTotalCr" class="ledger-money ledger-cr">0.00</th><th id="historyCurrentBalance" class="ledger-money ledger-balance">0.00</th></tr></tfoot>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a class="btn btn-outline-dark d-none" data-payment-history-print target="_blank" rel="noopener" href="#">Print History</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.receiptLedgerAssets')

{{-- form default date set for today --}}
<script>
    document.getElementById('date').valueAsDate = new Date();
</script>

{{-- CSRF Token --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

{{-- search receipt no --}}
<script>
    $(document).ready(function(){
        // search receipt data
        $('#search_receipt').on('keyup',function(e){
            e.preventDefault();
            clearTimeout($(this).data('timer'));
            $(this).data('timer', setTimeout(function() {
            $('#redeem_discount').val('')
            $('#search_ticket').val('')
            $('#search_invoice').val('')
            let search_receipt_no =  $('#search_receipt').val();
            //
            if(search_receipt_no){
            $.ajax({
                url:"{{ route('search_old_receipt_ajax') }}",
                method:'GET',
                data:{search_receipt_no:search_receipt_no},
                success: function(response) {
                    $('.dynamic-area').html(response);

                    //set ticket no
                    let t_number = $('#t_number').val();
                    $('#search_ticket').val(t_number);

                    //set invoice no
                    let i_number = $('#i_number').val();
                    $('#search_invoice').val(i_number);

                    if(response.status=='not_found'){
                        $('.dynamic-area').html('<span class="text-danger text-center">'+'Receipt not found ...!'+'</span>');
                        $('#CustomerDetails').empty();
                        window.ReceiptHistoryLedger.updateTotals([]);
                        window.ReceiptHistoryLedger.setPrintReceipt(null);
                    } else {
                        window.loadOldRedeemPaymentHistory($('#r_number').val());
                    }
                }
            });

            // // get t_pawn_details table data
            $.ajax({
                        url:"{{ route('view_article_details_ajax_old') }}",
                        method:'GET',
                        data:{search_receipt_no:search_receipt_no},
                        success: function(response) {
                            if (response.status == 'success') {
                                let data = response.data;
                                let tableRows = '';

                                data.forEach(record => {
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
                            }
                        }
            });

            } else {
                $('.dynamic-area').empty();
                $('#articleDetails, #CustomerDetails').empty();
                window.ReceiptHistoryLedger.updateTotals([]);
                window.ReceiptHistoryLedger.setPrintReceipt(null);
            }
        }, 300));
        })
    });
</script>

<script>
    window.loadOldRedeemPaymentHistory = function (receiptNumber) {
        window.ReceiptHistoryLedger.setPrintReceipt(null);
        if (!receiptNumber) {
            $('#CustomerDetails').empty();
            window.ReceiptHistoryLedger.updateTotals([]);
            return;
        }

        $('#CustomerDetails').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        $.ajax({
            url: "{{ route('view_dynamicCusDetailsView_details_ajax') }}",
            method: 'GET',
            data: { search_receipt_no: receiptNumber },
            success: function (response) {
                const rows = response.status === 'success' ? response.data || [] : [];
                window.ReceiptHistoryLedger.updateTotals(rows);
                window.ReceiptHistoryLedger.setPrintReceipt(response.receipt_number || null);
                $('#CustomerDetails').html(rows.length
                    ? window.ReceiptHistoryLedger.renderRows(rows)
                    : '<tr><td colspan="5" class="text-center">No payment history found</td></tr>');
            },
            error: function () {
                window.ReceiptHistoryLedger.updateTotals([]);
                $('#CustomerDetails').html('<tr><td colspan="5" class="text-center text-danger">Error fetching payment history</td></tr>');
            }
        });
    };
</script>

<script>
$(document).ready(function () {
    $('#CustomerTable').DataTable({
        "processing": true,
        "serverSide": false,
        "paging": true,
        "searching": true,
        "ordering": true
    });
});
</script>

<script>
$(document).ready(function () {

    function showAlert(message) {
        // Create alert div dynamically
        const alertDiv = $(`
            <div class="custom-alert">
                <span>${message}</span>
                <button class="closeAlert">&times;</button>
            </div>
        `);

        // Append to body
        $('body').append(alertDiv);

        // Fade in
        alertDiv.hide().fadeIn();

        // Close button
        alertDiv.find('.closeAlert').click(function() {
            alertDiv.fadeOut(function() {
                $(this).remove();
            });
        });

        // Auto remove after 5 seconds
        setTimeout(function() {
            alertDiv.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    $('#redeemForm').on('submit', function (e) {
        let redeemTotal  = parseFloat($('#redeem_total').val()) || 0;
        let payableTotal = parseFloat($('#payable_total').val()) || 0;

        if (payableTotal < redeemTotal) {
            e.preventDefault(); // stop form submit
            showAlert('Payable Total cannot be less than Redeem Total!');
            return false;
        }
    });

});
</script>

<style>
/* Centered medium alert box */
.custom-alert {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%); /* Center horizontally and vertically */
    background-color: #f44336; /* red */
    color: white;
    padding: 25px 40px; /* medium size */
    border-radius: 12px;
    box-shadow: 0 8px 12px rgba(0,0,0,0.3);
    z-index: 9999;
    font-family: Arial, sans-serif;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 300px; /* medium width */
    max-width: 500px;
    text-align: center;
}

.custom-alert button {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    margin-left: 10px;
}
</style>

<!-- jQuery -->
<script src="assets/js/jquery-3.6.0.min.js"></script>

<!-- Bootstrap -->
<script src="assets/js/bootstrap.bundle.min.js"></script>

<!-- Feather Icons -->
<script src="assets/js/feather.min.js"></script>

<!-- Slimscroll (for scroll effects) -->
<script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- DataTables (Only One) -->
<script src="assets/plugins/datatables/datatables.min.js"></script>

<!-- Select2 (Dropdown Styling) -->
<script src="assets/plugins/select2/js/select2.min.js"></script>

<!-- Moment.js (Before DateTime Picker) -->
<script src="assets/plugins/moment/moment.min.js"></script>

<!-- Bootstrap DateTime Picker -->
<script src="assets/js/bootstrap-datetimepicker.min.js"></script>

<!-- ApexCharts (For Graphs) -->
<script src="assets/plugins/apexchart/apexcharts.min.js"></script>
<script src="assets/plugins/apexchart/chart-data.js"></script>

<!-- Custom Scripts -->
<script src="assets/js/script.js"></script>


</body>
</html>
<script src="{{ asset('assets/js/payment-receipt-guard.js') }}"></script>
@endsection
