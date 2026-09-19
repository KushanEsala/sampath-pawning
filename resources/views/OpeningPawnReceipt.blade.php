@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Opening Pawn Receipt</title>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!--<link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">-->
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card shadow">
                            <div class="col-md-9">
                                <h4 class="card-title m-3">Opening Pawn</h4>
                            </div>
                            <hr size="6" style="color: blue">

                            <div class="card-body">
                                {{-- alerts section --}}
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
                                @if ($errors->any())
                                <div class="alert alert-danger text-center" role="alert">
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

                                <form action="{{ route('storeOpeningPawnSum')}}" method="post">
                                    @csrf

                                <div class="row ">
                                     <div class="row mb-1 form-group">
                                            <div class="col-md-4">
                                                <label for="customer_nic">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <div class="input-group">
                                                    <input type="text" class="form-control  form-control-lg"
                                                    name="customer_nic" id="searchCustomer"
                                                    placeholder="Enter Customer NIC" required/>
                                                    <div class="input-group-append">
                                                         <button type="button" class="btn btn-success btn-lg form-control "
                                                          data-bs-toggle="modal" data-bs-target="#addCustomerModel">
                                                            <i class="fas fa-plus" style="color: white"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                </label>
                                            </div>
                                            <div class="col-md-3 payment-history">
                                            </div>
                                            <div class="col">
                                                <label for="receipt_no">Stock No:
                                                    <input class="form-control" value="" type="text"
                                                        placeholder="Stock Number:" id="Stock_No" name="Stock_No"
                                                        required>
                                                </label>
                                            </div>
                                            <div class="col">
                                                <label for="receipt_no">Receipt No:
                                                    <input class="form-control" value="{{ $maxReceipt+1}}" type="text"
                                                        placeholder="Receipt Number:" id="receipt_no" name="receipt_no"
                                                        required>
                                                </label>
                                            </div>
                                            <div class="col">
                                                <label for="receipt_no">Invoice No:
                                                    <input class="form-control" type="text"
                                                        placeholder="Invoice Number:" id="invoice_no" name="invoice_no" required
                                                        required>
                                                </label>
                                           </div>
                                           <input type="hidden" name="sum_total_weight" id="sum_total_weight">
                                           <input type="hidden" name="sum_pawn_weight" id="sum_pawn_weight">
                                        </div>

                                        <div class="row mb-1 form-group">
                                            <div class="col-md-8">
                                            </div>
                                            <div class="col">
                                                <div class=" form-group ">
                                                    <label for="receipt_type">Receipt Type:
                                                        <select class="form-select " id="receipt_type"
                                                            name="receipt_type" aria-hidden="true" required>
                                                            <option value="">Please Select</option>
                                                            @foreach($receiptType as $receiptData)
                                                            <option value="{{ $receiptData->receiptname }}"
                                                                data-rate="{{ $receiptData->rate1 }}">
                                                                {{ $receiptData->receiptname}}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>

                                                    @foreach($receiptType as $receiptData)
                                                    <input type="hidden" id="valid_period" name="valid_period" value="{{ $receiptData->validPeriod }}">
                                                    @endforeach

                                                </div>
                                            </div>
                                            <div class="col">
                                                <label for="date">Date:
                                                    <input class="form-control datetimepicker" type="date"
                                                        id="receipt_date" name="receipt_date" required>
                                                </label>
                                            </div>
                                           <input type="hidden" name="sum_total_weight" id="sum_total_weight">
                                           <input type="hidden" name="sum_pawn_weight" id="sum_pawn_weight">
                                        </div>

                                        {{-- heading inputs --}}
                                        <div class="row form-group">
                                            <div class="col-md-4 ">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 customer-data"></div>
                                                {{-- <div class="col-md-2">
                                                    <label for="date">Date :
                                                        <input class="form-control datetimepicker" type="date" id="receipt_date"
                                                        name="receipt_date"
                                                        required>
                                                    </label>
                                                </div> --}}
                                            </div>
                                        </div>

                                        {{-- dynamicAdded table --}}
                                        <table class="table table-bordered" >
                                            <thead>
                                                <tr style="background-color: rgb(20, 123, 192)">
                                                    <th style="width:8%; text-align: center;">Category</th>
                                                    <th style="width:8%; text-align: center;">Articles</th>
                                                    <th style="width:8%; text-align: center;">Condition</th>
                                                    <th style="width:8%; text-align: center;">Karatage</th>
                                                    <th style="width:8%; text-align: center;">Pawn Weight</th>
                                                    <th style="width:8%; text-align: center;">Total Weight</th>
                                                    <th style="width:8%; text-align: center;">QTY</th>
                                                    <th style="width:8%; text-align: center;">Value</th>
                                                    <th class="text-center" style="width:8%;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td>
                                                        <select class="select form-control"
                                                        {{-- name="inputs[0][category]" --}}
                                                        id="category" aria-hidden="true">
                                                            <option value="">Please Select</option>
                                                            @foreach($itemCategory as $categoryData)
                                                                <option value="{{ $categoryData->category	 }}">{{ $categoryData->category	}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="select form-control "
                                                        {{-- name="inputs[0][articles]" --}}
                                                        id="articles" aria-hidden="true">
                                                            <option value="">Please Select</option>
                                                            @foreach($itemSetup as $itemData)
                                                                <option value="{{ $itemData->ItemDesc	 }}">{{ $itemData->ItemDesc}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="select form-control "
                                                        {{-- name="inputs[0][condition]" --}}
                                                        id="condition" aria-hidden="true">
                                                            <option value="">Please Select</option>
                                                            @foreach($itemCondition as $conditionData)
                                                                <option value="{{ $conditionData->conditionsName}}">{{ $conditionData->conditionsName}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="select form-control "
                                                        {{-- name="inputs[0][karatage]" --}}
                                                         id="karatage" aria-hidden="true">
                                                            <option value="">Please Select</option>
                                                            @foreach($itemKaratage  as $karatageData)
                                                                <option value="{{ $karatageData->descrption }}" data-pawningrate="{{ $karatageData->pawningrate }}">{{ $karatageData->descrption }}</option>
                                                                {{-- <option value="{{ $karatageData->descrption}}">{{ $karatageData->descrption}}</option> --}}
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input class="form-control" type="text" placeholder="Weight" id="weight"
                                                        {{-- name="inputs[0][weight]" --}}
                                                        >
                                                    </td>
                                                    <td>
                                                        <input class="form-control" type="text" placeholder="Total Weight" id="total_weight" >
                                                    </td>
                                                    <td>
                                                        <input class="form-control" type="text" placeholder="QTY" id="qty"
                                                        {{-- name="inputs[0][qty]" --}}
                                                        >
                                                    </td>
                                                    <td>
                                                        <input class="form-control" type="text" placeholder="Value" id="value"
                                                        {{-- name="inputs[0][value]" --}}
                                                        >
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" name="add" class="btn dynamic-ar  btn-outline-info btn-lg shadow"> Add  <i class="fas fa-plus"></i></button>
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>
                                        <br>

                                        {{-- dynamicAdded table --}}
                                        <table class="table table-bordered" id="dynamicAdded">


                                        </table>

                                        {{-- table footer for total calculations --}}
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr class="table-success" >
                                                    <td style="width:8%;"> </td>
                                                    <td style="width:8%;"><strong><p>TOTAL :</p></strong> </td>
                                                    <td style="width:8%;"> </td>
                                                    <td style="width:8%;"></td>
                                                    <td class="total-weight text-center" style="width:8%;"><p><strong>0.00</strong></p> </td>
                                                    <td class="total-total_weight text-center" style="width:8%;"><p><strong>0.00</strong></p> </td>
                                                    <td class="total-qty text-center" style="width:8%;"><p><strong>0</strong></p> </td>
                                                    <td class="total-value text-center" style="width:8%;"><p><strong>0.00</strong></p> </td>
                                                    <td style="width:11%;"></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        {{-- bottom buttons  --}}
                                        <div class="row mt-3">
                                            <div class="col-md-6"> </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col">
                                                        <label for="amount">AMOUNT :
                                                            <input class="form-control form-control-lg" style="font-weight:bold;" type="text"
                                                            placeholder="AMOUNT:" id="amount" name="amount" required>
                                                        </label>
                                                    </div>
                                                    <div class="col">
                                                        <label for="amount">TOTAL VALUE :
                                                            <input style="font-weight:bold;" class="form-control form-control-lg" type="text"
                                                            placeholder="AMOUNT:" id="total_amount" name="total_amount" readonly required>
                                                        </label>
                                                    </div>

                                                    <input type="hidden" id="interest" name="interest" required>
                                                    <input type="hidden" id="operator" name="operator" value="{{ Auth::user()->name }}" required>
                                                    @foreach ( $branchDetails as $bc )
                                                    <input type="hidden" id="bc" name="bc" value="{{ $bc->bccode }}" required>
                                                    @endforeach

                                                </div>
                                            </div>

                                     
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-3"> </div>
                                            <div class="col-md-6">
                                                <br>
                                                <button type="submit" name="save"  class="btn btn-outline-info btn-lg shadow">SAVE</button>
                                                <button type="button" name="print" class="btn btn-outline-primary printReceipt btn-lg shadow">PRINT</button>
                                                <button type="button" name="pawn_delete" id="pawn_delete"  class="btn btn-outline-danger pawn_delete btn-lg shadow">DELETE</button>
                                                <button type="reset" name="reset"  class="btn btn-outline-secondary btn-lg shadow">RESET</button>
                                            </div>
                                            <div class="col-md-3">  </div>
                                        </div>
                                    </form>

                            {{-- ------------Add customer model----------------- --}}
                            <div class="modal fade" id="addCustomerModel" tabindex="-1" role="dialog"
                             aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                 <div class="modal-dialog modal-lg">
                                 <div class="modal-content">
                                     <div class="modal-header">
                                         <h4 class="modal-title m-2" id="myLargeModalLabel"> Add Customers </h4>
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
                                                                 <div class="row">
                                                                         <div class="col-md-4">
                                                                             <label>Code <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                             <input type="text" value="{{ $maxCustomer+1}}" name="code" id="code" class="form-control" placeholder="Customer Code" required>
                                                                         </div>
                                                                     <div class="col-md-4">
                                                                         <label>Title <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                             <div class=" form-group">
                                                                                 <select class="select form-control" name="title" id="title" aria-hidden="true" required>
                                                                                     <option value="" >Please Select</option>
                                                                                     <option value="Mr.">Mr.</option>
                                                                                     <option value="Mrs.">Mrs.</option>
                                                                                 </select>
                                                                             </div>
                                                                     </div>
                                                                     <div class="col-md-4">
                                                                         <label>Gender <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                             <div class=" form-group">
                                                                                 <select class="select form-control" name="gender" id="gender" aria-hidden="true" required>
                                                                                     <option value="" >Please Select</option>
                                                                                     <option value="Male">Male</option>
                                                                                     <option value="Female">Female</option>
                                                                                     <option value="Other">Other</option>
                                                                                 </select>
                                                                             </div>
                                                                     </div>
                                                                 </div>

                                                             {{-- row for full name --}}
                                                             <div class="row">
                                                                 <div class="form-group">
                                                                     <div class="row">
                                                                         <div class="col">
                                                                             <label>First Name <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                             <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" required>
                                                                         </div>
                                                                         <div class="col">
                                                                             <label>Middle Name :</label>
                                                                             <input type="text" name="middle_name" id="middle_name" class="form-control" placeholder="Middle Name" >
                                                                         </div>
                                                                         <div class="col">
                                                                             <label>Last Name <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                             <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name" required>
                                                                         </div>
                                                                     </div>
                                                                 </div>
                                                             </div>

                                                             {{-- row for Address --}}
                                                             <div class="row">
                                                                 <div class="col-md-6">
                                                                     <textarea class="form-control" name="address1" id="address1" rows="3" placeholder="Address-1:" required></textarea> <br>
                                                                     <textarea class="form-control" name="city1" id="city1" rows="1" placeholder="City-1 :" ></textarea>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                     <textarea class="form-control" name="address2" id="address2" rows="3" placeholder="Address-2:"></textarea> <br>
                                                                     <textarea class="form-control" name="city2" id="city2" rows="1" placeholder="City-2 :" ></textarea>

                                                                 </div>
                                                             </div>
                                                                     {{-- row for Contacts --}}
                                                                     <div class="row mt-4">
                                                                         <div class="col-md-6">
                                                                             <label>Contact-1 <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                             <input type="text" name="contact1" id="contact1" class="form-control" placeholder="Contact 1" required>

                                                                         </div>
                                                                         <div class="col-md-6">
                                                                             <label>Contact-2 :</label>
                                                                             <input type="text" name="contact2" id="contact2" class="form-control" placeholder="Contact 2">

                                                                         </div>
                                                                     </div>


                                                                     {{-- row for NIC and Driving Licence --}}
                                                                     <div class="row mt-4">
                                                                         <div class="col-md-6">
                                                                             <label>NIC <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                             <input type="text" name="nic" id="nic" class="form-control" placeholder="NIC" required>
                                                                         </div>
                                                                         <div class="col-md-6">
                                                                             <label>Email :</label>
                                                                             <input type="text" name="email" id="email" class="form-control" placeholder="Email">
                                                                         </div>

                                                                     </div>


                                                                         {{-- row for PASSPORT and other --}}
                                                                         <div class="row mt-4">
                                                                             <div class="col-md-6">
                                                                                 <label>Driving License :</label>
                                                                                 <input type="text" name="driving_license" id="driving_license" class="form-control" placeholder="Driving Licence">
                                                                             </div>
                                                                             <div class="col-md-6">
                                                                                 <label>Passport :</label>
                                                                                 <input type="text" name="passport" id="passport" class="form-control" placeholder="Passport">
                                                                             </div>

                                                                         </div>

                                                                     {{-- row for Blacklisted --}}
                                                                     <div class="row mt-4">
                                                                         <div class="col-md-6">
                                                                             <label>Other Identifications :</label>
                                                                             <input type="text" name="other_identifications" id="other_identifications" class="form-control" placeholder="Other Identifications">
                                                                         </div>
                                                                         <div class="col-md-6">
                                                                             <div class="row">                                                                                                            <div class="col-md-11">
                                                                                     <label>Mark as Active or Blacklisted <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                                     <div class=" form-group">
                                                                                         <select class="select form-control" name="status" id="status" aria-hidden="true" required>
                                                                                             <option value="" >Please Select</option>
                                                                                             <option value="1" selected>Active</option>
                                                                                             <option value="0">Blacklist</option>
                                                                                         </select>
                                                                                     </div>
                                                                                 </div>
                                                                             </div>
                                                                         </div>
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


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- show payment history model --}}
    <div class="modal fade" id="paymentHistoryModel" tabindex="-1" role="dialog"
        aria-labelledby="paymentHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title m-2" id="paymentHistoryLabel"> Payment History </h4>
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

                                            <table class="table table-bordered" style="font-size: 0.9em;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width:8%; font-size: 1.1em; text-align: center;">Customer Name</th>
                                                        <th style="width:4%; font-size: 1.1em; text-align: center;">Receipt Type</th>
                                                        <th style="width:4%; font-size: 1.1em; text-align: center;">Receipt No</th>
                                                        <th style="width:5%; font-size: 1.1em; text-align: center;">Receipt Date</th>
                                                        <th style="width:5%; font-size: 1.1em; text-align: center;">Amount</th>
                                                        <th style="width:6%; font-size: 1.1em; text-align: center;">Total Amount</th>
                                                        <th style="width:8%; font-size: 1.1em; text-align: center;">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dynamicCusDetailsView">
                                                </tbody>
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

<!-- JavaScript -->
{{-- form default date set for today --}}
<script>
    document.getElementById('receipt_date').valueAsDate = new Date();
</script>

{{-- CSRF token script --}}
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

{{-- value auto calculation --}}
<script>
    $(document).ready(function () {
        // Listen for changes in the Weight and QTY fields
        $('#weight, #qty, #delete-btn').on('input', function () {
            // Get values from the Weight and QTY fields
            let weight = parseFloat($('#weight').val()) || 0;
            let qty = parseInt($('#qty').val()) || 0;

            // Retrieve the karatage rate from the selected option
            let karatageRate = parseFloat($('#karatage').find(':selected').data('pawningrate')) || 0;

            // Calculate the value
            let value = ((karatageRate * weight) / 8).toFixed(2);

            // Update the Value field
            $('#value').val(value);
        });
    });
</script>


{{-- edit pawn script --}}
<script>
    $(document).ready(function () {
            //add new receipt
            $(document).on('click', '.edit_button', function (e) {
                e.preventDefault();
                let receipt_no = $('#receipt_no').val();
                let receipt_type = $('#receipt_type').val();
                let op =  "{{ Auth::user()->name }}";
                if(receipt_no && receipt_type && op){
                    if(confirm('Are you sure to edit receipt ?')){
                        $.ajax({
                            url: "{{ route('edit_opening_receipt_ajax') }}",
                            method: 'GET',
                            data: {
                                "_token": "{{ csrf_token() }}",
                                receipt_no: receipt_no,
                                receipt_type: receipt_type,
                                op: op,
                            },
                            success: function (res) {
                                // window.open(pdfPath, '_blank');
                            if (res.status == 'success') {

                                Command: toastr["success"]("Receipt Deleted ...!", "Success")
                                    toastr.options = {
                                        "closeButton": true,
                                        "debug": false,
                                        "newestOnTop": false,
                                        "progressBar": true,
                                        "positionClass": "toast-top-right",
                                        "preventDuplicates": false,
                                        "onclick": null,
                                        "showDuration": "300",
                                        "hideDuration": "1000",
                                        "timeOut": "5000",
                                        "extendedTimeOut": "1000",
                                        "showEasing": "swing",
                                        "hideEasing": "linear",
                                        "showMethod": "fadeIn",
                                        "hideMethod": "fadeOut"
                                    }
                            }
                            },
                            error: function (err) {
                                $('.errMsgContainer').html('');
                                let error = err.responseJSON;
                                $.each(error.errors, function (index, value) {
                                    $('.errMsgContainer').append(
                                        '<span class="text-danger">' + value +
                                        '<span>' + '<br>');
                                });
                            }
                        })
                    }
                }
            })
    });
</script>

{{-- print receipt script --}}
<script>
    $(document).ready(function () {
            //add new receipt
            $(document).on('click', '.printReceipt', function (e) {
                e.preventDefault();
                let receipt_no = $('#receipt_no').val();

                $.ajax({
                    url: "{{ route('print_opening_receipt_ajax') }}",
                    method: 'GET',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        receipt_no: receipt_no
                    },
                    success: function (res) {
                        // window.open(pdfPath, '_blank');
                    if (res.status == 'success') {
                         // Open the PDF in a new window
                         var newWindow = window.open(res.pdf_url, '_blank');
                            // Wait for the new window load, then trigger the print function
                            newWindow.onload = function () {
                                newWindow.print();
                            };
                    }
                    },
                    error: function (err) {
                        $('.errMsgContainer').html('');
                        let error = err.responseJSON;
                        $.each(error.errors, function (index, value) {
                            $('.errMsgContainer').append(
                                '<span class="text-danger">' + value +
                                '<span>' + '<br>');
                        });
                    }
                })
            })
    });
</script>

{{-- delete open pawn receipt script --}}
<script>
    $(document).ready(function () {
        // Listen for changes in the delete button
        $(document).on('click', '.pawn_delete', function (e) {
            e.preventDefault();
            let receipt_no = $('#receipt_no').val();
            let receipt_type = $('#receipt_type').val();
            let cus_nic = $('#searchCustomer').val();
            let op =  "{{ Auth::user()->name }}";
            if(confirm('Are you sure to delete receipt ?')){
                if(receipt_no && receipt_type && cus_nic){
                var user_name = window.prompt('Please enter operator name:', '');

                if (user_name !== null) {
                var reason = window.prompt('Please enter the reason for deleting the receipt:', '');

                if (reason !== null) {

                    $.ajax({
                    url: "{{ route('delete_opening_pawn_receipt_ajax') }}",
                    method: 'GET',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        receipt_no: receipt_no,
                        receipt_type: receipt_type,
                        op: op,
                        user_name: user_name,
                        reason: reason,
                    },
                    success: function (res) {
                        // window.open(pdfPath, '_blank');
                        if (res.status == 'success') {

                            Command: toastr["success"]("Receipt Deleted ...!", "Success")
                            toastr.options = {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": false,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "5000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            }

                            function reloadPage() {
                                location.reload();
                            }
                            setTimeout(reloadPage, 1000);
                        }
                    },
                    error: function (err) {
                        $('.errMsgContainer').html('');
                        let error = err.responseJSON;
                        $.each(error.errors, function (index, value) {
                            $('.errMsgContainer').append(
                                '<span class="text-danger">' + value +
                                '<span>' + '<br>');
                        });
                    }
                })

                } else {alert('Deletion canceled. Reason not provided.');}
            } else {alert('Deletion canceled. Name not provided.');}
            } else {alert('Deletion Failed...! Receipt not found.');}

            }
        });
    });
</script>

{{-- add new row script --}}
<script type="text/javascript">
    var i = -1;
    let dataArray = [];
    let totalWeight = 0;
    let total_totalWeight = 0;
    let totalQty = 0;
    let totalValue= 0;
    $(".dynamic-ar,#dynamic-ar").click(function () {
        i++;
        let nic = $('#searchCustomer').val();
        let name = $('#customer_name').val();
        let address = $('#customer_address').val();
        let phone = $('#customer_contact_1').val();
        let receipt_type = $('#receipt_type').val();
        let receipt_no = $('#receipt_no').val();
        let invoice_no = $('#invoice_no').val();
        let receipt_date = $('#receipt_date').val();
        let category = $('#category').val();
        let articles = $('#articles').val();
        let condition = $('#condition').val();
        let karatage = $('#karatage').val();
        let weight = $('#weight').val();
        let total_weight = $('#total_weight').val();
        let qty = $('#qty').val();
        let value = $('#value').val();


        if ( receipt_type == "" || receipt_no == "" || receipt_date == "" || category == "" || articles == "" || condition == "" || karatage == "" || weight == "" || total_weight =="" || qty == "" || value == "") {
        alert("Please fill in all the fields.");
        return;
        }else{

        // Construct the newRowData object
        let newRowData = {
            receipt_no: receipt_no,
            receipt_type: receipt_type,
            receipt_date: receipt_date,
            category: category,
            articles: articles,
            condition: condition,
            karatage: karatage,
            weight: weight,
            total_weight: total_weight,
            qty: qty,
            value: value,
            invoice_no: invoice_no,
        };

        // Add data to the array
        dataArray.push(newRowData);
            $("#dynamicAdded").prepend(
                `
                <tr>
                    <td style="width:8%;">
                        <input type="hidden" name="inputs[`+i+`][receipt_no]" value="`+ receipt_no +`">
                        <input type="hidden" name="inputs[` + i + `][invoice_no]" value="` + invoice_no + `">
                        <input type="hidden" name="inputs[`+i+`][receipt_type]" value="`+ receipt_type +`">
                        <input type="hidden" name="inputs[`+i+`][receipt_date]" value="`+ receipt_date +`">

                        <select class="select form-control" style="text-align: center;" id="dy_category"
                        name="inputs[`+i+`][category]" aria-hidden="true" readonly>
                            <option value="">Please Select</option>
                            @foreach($itemCategory as $categoryData)
                                <option value="{{$categoryData->category}}" {{ $categoryData->category == "`+category+`"  ? 'selected' : '' ;}}>{{ $categoryData->category }}</option>
                            @endforeach
                        </select>
                    </td>

                    <td style="width:8%;">
                        <select class="select form-control" style="text-align: center;" id="dy_articles"
                        name="inputs[`+i+`][articles]" aria-hidden="true" readonly>
                            <option value="">Please Select</option>
                            @foreach($itemSetup as $itemData)
                                <option value="{{ $itemData->ItemDesc	 }}">{{ $itemData->ItemDesc}}</option>
                            @endforeach
                        </select>
                    </td>

                    <td style="width:8%;">
                        <select class="select form-control" style="text-align: center;" id="dy_condition"
                        name="inputs[`+i+`][condition]" aria-hidden="true" readonly>
                            <option value="">Please Select</option>
                            @foreach($itemCondition as $conditionData)
                                <option value="{{ $conditionData->conditionsName}}">{{ $conditionData->conditionsName}}</option>
                            @endforeach
                        </select>
                    </td>

                    <td style="width:8%;">
                        <select class="select form-control" style="text-align: center;" id="dy_karatage"
                        name="inputs[`+i+`][karatage]" aria-hidden="true" readonly>
                            <option value="">Please Select</option>
                            @foreach($itemKaratage  as $karatageData)
                                <option value="{{ $karatageData->descrption}}">{{ $karatageData->descrption}}</option>
                            @endforeach
                        </select>
                    </td>

                    <td style="width:8%;">
                        <input class="form-control" type="text" style="text-align: center;" placeholder="Weight"
                        id="dy_weight" name="inputs[`+i+`][weight]" value="`+weight+`" readonly>
                    </td>

                    <td style="width:8%;">
                        <input class="form-control" type="text" style="text-align: center;" placeholder="Total Weight"
                        id="dy_total_weight" name="inputs[`+i+`][total_weight]" value="`+total_weight+`" readonly>
                    </td>

                    <td style="width:8%;">
                        <input class="form-control" type="text" style="text-align: center;" placeholder="QTY"
                        id="dy_qty" name="inputs[`+i+`][qty]" value="`+qty+`" readonly>
                    </td>

                    <td style="width:8%;">
                        <input class="form-control" type="text" style="text-align: center;" placeholder="Value"
                        id="dy_value" name="inputs[`+i+`][value]" value="`+value+`" readonly>
                    </td>

                    <td style="width:8%;">
                        <center>
                            <button type="button" class="btn btn-outline-danger text-center shadow remove-input-field m-2"> <i class="far fa-trash-alt me-1"></i> Delete </button>
                        </center>
                    </td>
                </tr>
                `
            );
        }

        $("#dy_category").val(category);
        $('#dy_articles').val(articles);
        $('#dy_condition').val(condition);
        $('#dy_karatage').val(karatage);
        $('#dy_weight').val(weight);
        $('#dy_total_weight').val(total_weight);
        $('#dy_qty').val(qty);
        $('#dy_value').val(value);

        totalWeight = parseFloat(totalWeight) + parseFloat(weight);
        total_totalWeight = parseFloat(total_totalWeight) + parseFloat(total_weight);
        totalQty = parseInt(totalQty) + parseInt(qty);
        totalValue = parseFloat(totalValue) + parseFloat(value);
        setTotal();
        resetTableRow()
    });

    // Function to reset the input fields and dropdowns in the table row
    function resetTableRow() {
        document.getElementById("category").value = "";
        document.getElementById("articles").value = "";
        document.getElementById("condition").value = "";
        document.getElementById("karatage").value = "";
        document.getElementById("weight").value = "";
        document.getElementById("total_weight").value = "";
        document.getElementById("qty").value = "";
        document.getElementById("value").value = "";
    }

    // get total function
    function setTotal(){
        $('.total-weight').html(`<p><strong>`+totalWeight+`</strong></p>`);
        $('#sum_pawn_weight').val(totalWeight);

        $('.total-total_weight').html(`<p><strong>`+total_totalWeight+`</strong></p>`);
        $('#sum_total_weight').val(total_totalWeight);

        $('.total-qty').html(`<p><strong>`+totalQty+`</strong></p>`);
        $('.total-value').html(`<p><strong>`+totalValue+`</strong></p>`);

        document.getElementById("total_amount").value = totalValue;
    };

    // Function to calculate interest based on receipt type rate
    function interestCalculations() {
        let selectedOption = $("#receipt_type option:selected");
        let rate = parseFloat(selectedOption.data("rate")) || 0;

        // Calculate the interest
        let amount = $('#amount').val();
        let interest = ((amount / 100)* rate).toFixed(2);
        $('#interest').val(interest);

    }

    // run interest calculation when change the amount field
    $('#amount, #receipt_type ').on('keyup, change',function(e){
            e.preventDefault();
            let amount_value =  $('#amount').val();
            if(amount_value != null){
                interestCalculations();
            }
    })

</script>

{{-- Items show acording to category --}}
<script>
    $(document).ready(function () {
        // Listen for changes in the Weight and QTY fields
        $('#category').on('change', function () {
            let category = $('#category').val();
            $.ajax({
                        url: "{{ route('show_select_category_item_ajax') }}",
                        method: 'GET',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            category: category
                        },
                        success: function (res) {
                            if (res.status == 'success') {

                                var itemsSelect = $('#articles');
                                // Change this to match your items select element
                                // Clear existing options
                                itemsSelect.empty();

                                // Add a default option
                                itemsSelect.append($('<option>', {
                                    value: '',
                                    text: 'Select an item'
                                }));

                                $.each(res.data, function (index, item) {
                                    itemsSelect.append($('<option>', {
                                        value: item.ItemDesc,
                                        text: item.ItemDesc // Assuming 'name' is the property you want to display
                                    }));
                                });

                            }
                        },
                        error: function (err) {
                            $('.errMsgContainer').html('');
                            let error = err.responseJSON;
                            $.each(error.errors, function (index, value) {
                                $('.errMsgContainer').append(
                                    '<span class="text-danger">' + value +
                                    '<span>' + '<br>');
                            });
                        }
                    });
        });
    });
</script>

{{-- delete added pawn rows script --}}
<script>
    $(document).on('click', '.remove-input-field', function(){
        var row = $(this).parents('tr');
        var re_weight = row.find('#dy_weight');
        var re_total_weight = row.find('#dy_total_weight');
        var re_qty = row.find('#dy_qty');
        var re_value = row.find('#dy_value');
        var re_weightValue = re_weight.val();
        var re_total_weightValue = re_total_weight.val();
        var re_qtyValue = re_qty.val();
        var re_valueValue = re_value.val();

        totalWeight = parseInt(totalWeight) - parseInt(re_weightValue);
        total_totalWeight = parseInt(total_totalWeight) - parseInt(re_total_weightValue);
        totalQty = parseInt(totalQty) - parseInt(re_qtyValue);
        totalValue = parseInt(totalValue) - parseInt(re_valueValue);

        // Remove data from the array
        var index = row.index();
        dataArray.splice(index, 1);

        $(this).parents('tr').remove();
        setTotal();
    });
</script>

{{--  get customer data inserting NIC --}}
<script>
    $(document).ready(function(){
                // search customer data
                $(document).on('keyup',function(e){
                            e.preventDefault();
                                var search_string =  $('#searchCustomer').val();
                                // console.log(search_string);
                                if(search_string != null){
                                    if(e.keyCode == 13 || e.keyCode == 10){
                                        $.ajax({
                                            url:"{{ route('get_customer_ajax') }}",
                                            method:'GET',
                                            data:{search_string:search_string},
                                            success: function(res) {
                                                $('.customer-data').html(res);
                                                $('.payment-history').html(
                                                    `
                                                    <label for="receipt_type">History :
                                                    <br>
                                                        <button type="button" class="btn btn-outline-info btn-block " data-bs-toggle="modal" data-bs-target="#paymentHistoryModel">
                                                            <i class="far fa-eye me-1"></i> History
                                                        </button>
                                                    </label>

                                                    `
                                                    );

                                                if(res.status=='not_found'){
                                                    $('.customer-data').html(
                                                    `
                                                    <label for="customer_name" class="text-danger">  Customer Not Found ..!!
                                                    </label>
                                                    `
                                                    );
                                                    $('.payment-history').html(``);
                                                }
                                            }
                                        });

                                        // get customer details history t_pawn_sums table data
                                        $.ajax({
                                                    url:"{{ route('view_customer_history_details_ajax') }}",
                                                    method:'GET',
                                                    data:{search_string:search_string},
                                                    success: function(response) {
                                                        if (response.status == 'success') {
                                                            let data = response.data;
                                                            let tableRows = '';
                                                            var status;

                                                            data.forEach(record => {

                                                                if(record.IsRedeemed==0){
                                                                    status = '<span class="text-warning">Pending</span>';
                                                                }else{
                                                                    status = '<span class="text-success">Redeemed</span>';
                                                                }

                                                                tableRows += `
                                                                    <tr>
                                                                        <td>${record.Customer_Name}</td>
                                                                        <td>${record.Receipt_Type}</td>
                                                                        <td>${record.Receipt_Number}</td>
                                                                        <td>${record.Receipt_Date}</td>
                                                                        <td>${record.Amount}</td>
                                                                        <td>${record.Total_Amount}</td>
                                                                        <td>${status}</td>
                                                                    </tr>`;
                                                            });

                                                            $('#dynamicCusDetailsView').html(tableRows);
                                                        }
                                                    }
                                        });


                                    }
                                }
                })
    });
</script>

{{-- search and get pawn data using receipt_no  --}}
<script>
    $(document).ready(function(){
        // search receipt data
        $('#receipt_no').on('input',function(e){
            e.preventDefault();
            let search_receipt_no = $('#receipt_no').val();
            $('#searchCustomer').val('');
            $('#amount').val('');
            $('#total_amount').val('');
            $('#receipt_type').val('');
            $('#invoice_no').val('');
            document.getElementById('receipt_date').valueAsDate = new Date();

            if(search_receipt_no > 0){

                // get t_pawn_details table data
                    $.ajax({
                        url:"{{ route('search_opening_pawn_ajax') }}",
                        method:'GET',
                        data:{search_receipt_no:search_receipt_no},
                        success: function(res) {
                            $('#dynamicAdded').html(res);
                            if(res.status=='not_found'){
                                $('#dynamicAdded').html(
                                    `
                                    <div  style ="text-align:center;">
                                    <label class="text-danger mt-3 mb-3">Receipt Not Found ...!!</label>
                                    </div>
                                    `
                                );
                            }
                        }
                    });

                // get t_pawn_sum table data
                    $.ajax({
                        url:"{{ route('get_customer_opening_receipt_no_ajax') }}",
                        method:'GET',
                        data:{search_receipt_no:search_receipt_no},
                            success: function(response) {

                                if(response.status=='success'){
                                    let records = response.data;
                                    let id, Customer_NIC, Customer_Name, Customer_Address, Customer_Phone,
                                    Receipt_Type, Receipt_Number, Invoice_Number,
                                    Receipt_Date, Amount, Total_Amount,
                                    LateRedim, Pending, Redempt;

                                    records.forEach(record => {
                                        id = record.id;
                                        Customer_NIC = record.Customer_NIC;
                                        Customer_Name = record.Customer_Name;
                                        Customer_Address = record.Customer_Address;
                                        Customer_Phone = record.Customer_Phone;
                                        Receipt_Type = record.Receipt_Type;
                                        Receipt_Number = record.Receipt_Number;
                                        Invoice_Number = record.Invoice_Number;
                                        Receipt_Date = record.Receipt_Date;
                                        Amount = record.Amount;
                                        Total_Amount =  record.Total_Amount;
                                        LateRedim = record.LateRedim;
                                        Pending =  record.Pending;
                                        Redempt = record.Redempt;

                                         // Update the input values
                                        $('#searchCustomer').val(Customer_NIC);
                                        $('#customer_name').val(Customer_Name);
                                        $('#customer_address').val(Customer_Address);
                                        $('#customer_contact_1').val(Customer_Phone);
                                        $('#receipt_type').val(Receipt_Type);
                                        $('#receipt_number').val(Receipt_Number);
                                        $('#invoice_no').val(Invoice_Number);
                                        $('#receipt_date').val(Receipt_Date);
                                        $('#amount').val(Amount);
                                        $('#total_amount').val(Total_Amount);
                                        $('#late_redeem').val(Total_Amount);
                                        $('#pending_redeem').val(Total_Amount);
                                        $('#redeem_pt').val(Total_Amount);
                                    });

                                    $('.customer-data').html(
                                        `
                                        <div class="row">
                                            <input class="form-control" type="hidden"
                                                value= "${id}"name="customer_id" />
                                            <div class="col">
                                                <label for="late_redeem"><span class="text-danger">L :</span>
                                                    <input class="form-control form-control-lg text-danger " type="text"
                                                        value= "${LateRedim}"
                                                        placeholder="Customer Address"
                                                        name="late_redeem"
                                                        id="late_redeem" readonly/>
                                                </label>
                                            </div>
                                            <div class="col">
                                                <label for="pending_redeem"><span class="text-warning">P :</span>
                                                    <input class="form-control form-control-lg text-warning" type="text"
                                                        value= "${Pending}"
                                                        placeholder="Customer Address"
                                                        name="pending_redeem"
                                                        id="pending_redeem" readonly/>
                                                </label>
                                            </div>
                                            <div class="col">
                                                <label for="redeem_pt"><span class="text-success">R :</span>
                                                    <input class="form-control form-control-lg text-success" type="text"
                                                        value= "${Redempt}"
                                                        placeholder="Customer Address"
                                                        name="redeem_pt"
                                                        id="redeem_pt" readonly/>
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                            <label for="customer_id">Customer Name :
                                                <input class="form-control form-control-lg" type="text"
                                                    value= "${Customer_Name}"
                                                    id="customer_name"
                                                    placeholder="Customer Name:"
                                                    name="customer_name"
                                                    readonly/>
                                            </label>
                                            </div>
                                            <div class="col-md-3">
                                            <label for="customer_address">Customer Address :
                                                <input class="form-control form-control-lg" type="text"
                                                    value= "${Customer_Address}"
                                                    placeholder="Customer Address"
                                                    name="customer_address"
                                                    id="customer_address" readonly/>
                                            </label>
                                            </div>
                                            <div class="col-md-2">
                                            <label for="customer_contact_1">Phone Number :
                                                <input class="form-control form-control-lg" type="text"
                                                    value= "${Customer_Phone}"
                                                    placeholder="Customer Phone"
                                                    name="customer_contact_1"
                                                    id="customer_contact_1" readonly/>
                                            </label>
                                            </div>
                                            </div>
                                        `
                                    );

                                    $('.payment-history').html(
                                        `
                                        <label for="receipt_type">Customer History :
                                            <br>
                                            <button type="button" class="btn btn-outline-info btn-block " data-bs-toggle="modal" data-bs-target="#paymentHistoryModel">
                                                <i class="far fa-eye me-1"></i>  See Customer History
                                            </button>
                                        </label>
                                        `
                                    );
                                }
                                else if(response.status=='not_found'){
                                    $('.customer-data').html(
                                        `
                                        <label for="customer_name" class="text-danger">  Customer Not Found ..!!
                                        </label>
                                        `
                                    );
                                    $('.payment-history').html(``);
                                }
                            }
                    });

                    // get customer details history t_pawn_sums table data
                    $.ajax({
                                                    url:"{{ route('view_customer_history_details_ajax') }}",
                                                    method:'GET',
                                                    data:{search_string:search_string},
                                                    success: function(response) {
                                                        if (response.status == 'success') {
                                                            let data = response.data;
                                                            let tableRows = '';

                                                            data.forEach(record => {
                                                                tableRows += `
                                                                    <tr>
                                                                        <td>${record.Customer_Name}</td>
                                                                        <td>${record.Receipt_Type}</td>
                                                                        <td>${record.Receipt_Number}</td>
                                                                        <td>${record.Receipt_Date}</td>
                                                                        <td>${record.Amount}</td>
                                                                        <td>${record.Total_Amount}</td>

                                                                    </tr>`;
                                                            });

                                                            $('#dynamicCusDetailsView').html(tableRows);
                                                        }
                                                    }
                    });
            }else{
                $('#dynamicAdded').html('');
                $('.customer-data').html('');
                $('.payment-history').html('');
            }
        })
    });
</script>

{{-- add new customer script --}}
<script>
    $(document).ready(function(){
        //add new customer
        $(document).on('click','.add_customer', function(e){
            e.preventDefault();
            let code = $('#code').val();
            let title = $('#title').val();
            let gender = $('#gender').val();
            let name = $('#name').val();
            let first_name = $('#first_name').val();
            let middle_name = $('#middle_name').val();
            let last_name = $('#last_name').val();
            let address1 = $('#address1').val();
            let city1 = $('#city1').val();
            let address2 = $('#address2').val();
            let city2 = $('#city2').val();
            let contact1 = $('#contact1').val();
            let contact2 = $('#contact2').val();
            let email = $('#email').val();
            let nic = $('#nic').val();
            let driving_license = $('#driving_license').val();
            let passport = $('#passport').val();
            let other_identifications = $('#other_identifications').val();
            let status = $('#status').val();

            $.ajax({
                url:"{{ route('add_customer_ajax') }}",
                method: 'post',
                data:{"_token": "{{ csrf_token() }}",
                code:code,
                title:title,
                gender:gender,
                name:name,
                first_name:first_name,
                middle_name:middle_name,
                last_name:last_name,
                address1:address1,
                city1:city1,
                address2:address2,
                city2:city2,
                contact1:contact1,
                contact2:contact2,
                email:email,
                nic:nic,
                driving_license:driving_license,
                passport:passport,
                other_identifications:other_identifications,
                status:status},

                success:function(res){
                    if(res.status=='success'){
                        $("#addCustomerModel").modal('hide');
                        $('#addCustomer')[0].reset();
                        // $('.table').load(location.href+' .table');
                        Command: toastr["success"]("Customer Added ...!", "Success")
                            toastr.options = {
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": false,
                            "progressBar": true,
                            "positionClass": "toast-top-right",
                            "preventDuplicates": false,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "timeOut": "5000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut"
                            }
                    }
                },error:function(err){
                    $('.errMsgContainer').html('');
                    let error = err.responseJSON;
                    $.each(error.errors,function(index, value){
                        $('.errMsgContainer').append('<span class="text-danger">'+value+'<span>'+'<br>');
                    });
                }
            })
        })
    });
</script>


    {{-- <script src="assets/js/jquery-3.6.0.min.js"></script> --}}
    {{-- <script src="assets/js/bootstrap.bundle.min.js"></script> --}}
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="public/assets/js/toastr.min.js"></script>
    {{-- <script src="assets/plugins/moment/moment.min.js"></script> --}}
    {{-- <script src="assets/js/bootstrap-datetimepicker.min.js"></script> --}}
    {{-- <script src="assets/plugins/apexchart/apexcharts.min.js"></script> --}}
    <script src="assets/plugins/apexchart/chart-data.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <!--<script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>

</body>
</html>
@endsection