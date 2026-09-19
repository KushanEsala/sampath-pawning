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
    <title>Make a Payment</title>

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
                                <h4 class="card-title m-3">Make a Payment</h4>
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
                                @endif

                                <form action="{{ route('store_redeem') }}" method="post">
                                    @csrf
                                    <div class="row form-group ">
                                        <div class="col-md-4 mb-1">
                                            <label for="receipt_no">Receipt Number :
                                                <input class="form-control" type="text" placeholder="Type Receipt Number:"
                                                name="receipt_number" id="search_receipt" required>
                                            </label>
                                        </div>

                                        <div class="dynamic-area">
                                            <div class="col ">
                                                <div class="row">
                                                    <div class="col">
                                                        <label for="receipt_type">Receipt Type :
                                                            <input class="form-control"  type="text" placeholder="Receipt type"
                                                                name="receipt_type"
                                                                id="receipt_type" readonly/>
                                                        </label>
                                                        {{-- <label for="receipt_type">Receipt Type
                                                            <select class="select form-control" id="receipt_type" name="receipt_type" aria-hidden="true" required>
                                                                <option value="" >Please Select</option>
                                                                <option value="Male">Male</option>
                                                                <option value="Female">Female</option>
                                                                <option value="Other">Other</option>
                                                            </select>
                                                        </label> --}}
                                                    </div>
                                                    <div class="col">
                                                            <label for="date">Date :
                                                                <input class="form-control" id="date" type="date" placeholder="Date" name="date">
                                                            </label>
                                                    </div>
                                                    <div class="col">
                                                        <label for="redeem_no">Payment Number :
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
                                                        <button class="btn btn-outline-info form-control" type="button">Payment History</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>

                                            <h5 class="mt-3">Payment Details</h5>
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
                                                            Document Charges
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



                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label for="amount">DISCOUNT : </label>
                                                <input class="form-control" type="text" placeholder="DISCOUNT :" id="redeem_discount" name="redeem_discount">

                                        </div>
                                        <div class="col-md-4">
                                            <label for="amount">TOTAL : </label>
                                                <input class="form-control" type="text" placeholder="TOTAL :" id="redeem_total" name="redeem_total">

                                        </div>
                                        <div class="col-md-4">
                                            <label for="amount">PAYABLE TOTAL : </label>
                                                <input class="form-control" type="text" placeholder="AMOUNT :" name="payable_total" required>

                                        </div>
                                    </div>

                                    {{-- form buttons --}}
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
        <div class="modal-dialog modal-lg">
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

{{-- CSRF Token --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

{{-- search receipt data --}}
<script>
    $(document).ready(function(){
        // search receipt data
        $('#search_receipt').on('keyup',function(e){
            e.preventDefault();
            let search_receipt_no =  $('#search_receipt').val();
            //
            if(search_receipt_no != null){
            $.ajax({
                url:"{{ route('search_receipt_ajax') }}",
                method:'GET',
                data:{search_receipt_no:search_receipt_no},
                success: function(response) {
                    $('.dynamic-area').html(response);

                    if(response.status=='not_found'){
                        $('.dynamic-area').html('<span class="text-danger">'+'Receipt not found ...!'+'</span>');
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
