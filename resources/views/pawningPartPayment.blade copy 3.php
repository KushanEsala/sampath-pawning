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

                                <form action="{{ route('Store_part_payment') }}" method="post">
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
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">Invoice Number :</span>
                                                <input class="form-control" type="text" placeholder="Type Invoice Number:"
                                                name="invoice_number" id="search_invoice" required>
                                              </div>
                                        </div>
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
                                                        <button class="btn btn-outline-info form-control" type="button">Payment History</button>
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

                             

                                        <div class="row">
                                            <div class="col-md-2"></div>
                                            <div class="col-md-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-lg btn-info  form-control" name="redeem" type="submit">SAVE</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-lg btn-warning  form-control" name="print" type="button">PRINT</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-lg btn-success  form-control" name="reset" type="reset">RESET</button>
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
   <div class="modal-dialog modal-xl">
   <div class="modal-content">
       <div class="modal-header">
           <h4 class="modal-title m-2" id="viewPaymentHistoryLabel"> Payment History </h4>
           <button type="button" class="btn-close" data-bs-dismiss="modal"
               aria-label="Close">
           </button>
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
                               <div class="table-responsive">
                               <table border="1" class="table table-bordered">
                                    <thead>
                                            <tr style="background-color: rgb(12, 119, 241); color: aliceblue;">
                                                <th style="width:4%; height:10px; text-align: center;">Mortgage No</th>
                                                <th style="width:8%; height:10px; text-align: center;">Mortgage Date</th>
                                                <th style="width:8%; height:10px; text-align: center;">Payment Type</th>
                                                <th style="width:8%; text-align: center;">Payment Amount</th>
                                                 <th style="width:8%; text-align: center;"> Total Interest </th>
                                                <th style="width:8%; text-align: center;">Paid Interest Amount</th>
                                                <th style="width:8%; text-align: center;">Balance Interest Amount</th>
                                                <th style="width:8%; text-align: center;">Paid Capital Amount</th>
                                                <th style="width:8%; text-align: center;">Re-Mortgage Amount</th>
                                                <th style="width:8%; text-align: center;">Balance Capital Amount</th>
                                                <th style="width:8%; text-align: center;">Extend Date</th>
                                            </tr>
                                        </thead>
                                       <tbody id="CustomerDetails">
                                   
                                       </tbody>
                                   </table>
                                    </div>

                                   {{-- dynamicAdded table --}}
                                   <table class="table table-bordered" id="dynamicCustomerView">


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

{{-- search receipt no --}}
<script>
    $(document).ready(function(){
        // search receipt data
        $('#search_receipt').on('keyup',function(e){
            e.preventDefault();
            setTimeout(function() {
            $('#redeem_discount').val('')
            $('#search_ticket').val('')
            $('#search_invoice').val('')
            let search_receipt_no =  $('#search_receipt').val();
            let branch =  $('#branch').val();

            //
            if(search_receipt_no != null){
            $.ajax({
                url:"{{ route('search_part_payment_receipt_ajax') }}",
                method:'GET',
                data:{search_receipt_no:search_receipt_no,
                    branch:branch},
                

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
                    }
                }
            });

            // // get t_pawn_details table data
            $.ajax({
                        url:"{{ route('view_article_details_ajax') }}",
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

            }
        }, 300);
        })
    });
</script>

{{-- search ticket data --}}
<script>
    $(document).ready(function(){
        // search receipt data
        $('#search_ticket').on('keyup',function(e){
            e.preventDefault();
            setTimeout(function() {
            $('#redeem_discount').val('')
            $('#search_receipt').val('')
            $('#search_invoice').val('')
            let search_receipt_no =  $('#search_ticket').val();
            //
            if(search_receipt_no.length > null){
            $.ajax({
                url:"{{ route('search_ticket_ajax') }}",
                method:'GET',
                data:{search_receipt_no:search_receipt_no},
                success: function(response) {
                    $('.dynamic-area').html(response);

                    //set receipt no
                    let r_number = $('#r_number').val();
                    $('#search_receipt').val(r_number);

                    //set invoice no
                    let i_number = $('#i_number').val();
                    $('#search_invoice').val(i_number);

                    if(response.status=='not_found'){
                        $('.dynamic-area').html('<span class="text-danger text-center">'+'Receipt not found ...!'+'</span>');
                    }
                }
            });

            // // get t_pawn_details table data
            $.ajax({
                        url:"{{ route('view_article_details_ajax') }}",
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

            }
        }, 300);
        })
    });
</script>

{{-- search invoice data --}}
<script>
    $(document).ready(function(){
        // search receipt data
        $('#search_invoice').on('keyup',function(e){
            e.preventDefault();
            $('#search_ticket').val('')
            $('#search_receipt').val('')
            let search_invoice_no =  $('#search_invoice').val();
            $('#redeem_total').val('');
            //
            if(search_invoice_no != null){
            $.ajax({
                url:"{{ route('search_invoice_ajax') }}",
                method:'GET',
                data:{search_invoice_no:search_invoice_no},
                success: function(response) {
                    $('.dynamic-area').html(response);

                    //set receipt no
                    let r_number = $('#r_number').val();
                    $('#search_receipt').val(r_number);


                    if(response.status=='not_found'){
                        $('#search_receipt').val("");
                        $('.dynamic-area').html('<span class="text-danger text-center">'+'Receipt not found ...!'+'</span>');
                    }
                }
            });

            // // get t_pawn_details table data
            $.ajax({
                        url:"{{ route('view_article_details_ajax') }}",
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

            }
        })
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


<script>
    $(document).ready(function(){
        $('#search_receipt').on('keyup', function(e){
            e.preventDefault();
            clearTimeout($.data(this, 'timer')); // Prevent rapid firing

            $(this).data('timer', setTimeout(function() {
                $('#redeem_discount').val('');
                $('#search_ticket').val('');
                $('#search_invoice').val('');
                
                let search_receipt_no = $('#search_receipt').val();

                if(search_receipt_no !== ''){
                    $.ajax({
                        url: "{{ route('view_dynamicCusDetailsView_details_ajax') }}",
                        method: 'GET',
                        data: { search_receipt_no: search_receipt_no },
                        success: function(response) {
                            if (response.status === 'success') {
                                let data = response.data;
                                let tableRows = '';

                     data.forEach(record => {
                        let balance = 0;
                        
                                if (record.trans_type === "REPAWNING") {
                                    balance = (
                                        Number(record.trans_amount ?? 0) -
                                        Number(record.Paided_Captional ?? 0) -
                                        Number(record.Paided_Interest ?? 0) +
                                        Number(record.Cr_amount ?? 0) +
                                        Number(record.Paided_Interest ?? 0)
                                    ).toFixed(2);
                                } else {
                                    balance = (
                                        Number(record.trans_amount ?? 0) -
                                        Number(record.Paided_Captional ?? 0) -
                                        Number(record.Paided_Interest ?? 0) +
                                        Number(record.interest_Balance ?? 0)
                                    ).toFixed(2);
                                }

                                tableRows += `
                                    <tr>
                                        <td>${record.code ?? '0'}</td>
                                        <td>${record.dDate ?? '0'}</td>
                                        <td>${record.trans_type}</td>
                                        <td>${record.trans_amount ?? '0'}</td>
                                         <td>${record.Paided_Interest ?? '0'}</td>
                                        <td>${(record.Paided_Interest ?? 0) - (record.interest_Balance ?? 0)}</td>
                                        <td>${record.interest_Balance ?? '0'}</td>
                                        <td>${record.Paided_Captional ?? '0'}</td>
                                        <td>${record.Cr_amount ?? '0'}</td>
                                        <td>${balance}</td>
                                        <td>${record.Extend_Date ?? '-'}</td>
                                    </tr>
                                `;
                                 });


                                $('#CustomerDetails').html(tableRows);
                            } else {
                                $('#CustomerDetails').html('<tr><td colspan="9" class="text-center">No data found</td></tr>');
                            }
                        },
                        error: function() {
                            $('#CustomerDetails').html('<tr><td colspan="9" class="text-center text-danger">Error fetching data</td></tr>');
                        }
                    });
                }
            }, 300));
        });
    });
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
@endsection