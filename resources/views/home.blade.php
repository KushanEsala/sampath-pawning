{{-- @extends('layouts.app') --}}
@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Dashboard</title>
<style>
  .custom-offcanvas-width {
    width: 50% !important;
  }
</style>
             <style>
    .card {
        background: linear-gradient(to right, #d0e5f1, #eaeaea);
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin: 10px 0;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .card h1 {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #ccc;
        padding-bottom: 10px;
        margin-bottom: 0;
    }
</style>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    thead {
        background-color: #e6eaee;
        color: #030303;
    }

    th, td {
        padding: 14px 20px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    tr:hover {
        background-color: #f5f5f5;
    }

    th {
        font-weight: 600;
        letter-spacing: 0.5px;
    }
</style>
</head>

<body class="nk-body bg-lighter npc-default has-sidebar no-touch nk-nio-theme">
    <div class="main-wrapper">

        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="row" >
                    <div class="col-md-8"> </div>
                    <div class="col-md-4">
                        <button class="btn btn-info" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                            Customer comment
                        </button>
                    </div>
                </div>


                <div class="row">
                     {{-- Showing Pawning Total and Receipt Count --}}
                    <div class="col-xl-6 col-sm-6 col-12">
                        <div class="card">
                        <div class="shadow p-3 mb-1 bg-body-tertiary rounded">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon bg-1">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                    <div class="dash-count">
                                        <div class="dash-title">TOTAL PAWNING</div>
                                        <div class="dash-counts">
                                            <div class="dash-title">Pawning Receipt: {{$TotalPawn}}</div>
                                        <h5>{{$Pawningpayemt}}</h5>
                                        </div>

                                    </div>
                                </div>
                                <div class="progress progress-sm mt-3">
                                    <div class="progress-bar bg-5" role="progressbar" style="width: 75%"
                                        aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                                   {{-- Showing Redeem Total and Receipt Count --}}
                    <div class="col-xl-6 col-sm-6 col-12">
                        <div class="card">
                        <div class="shadow p-3 mb-1 bg-body-tertiary rounded">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon bg-2">
                                        <i class="fas fa-shopping-cart"></i>
                                    </span>
                                    <div class="dash-count">
                                        <div class="dash-title">TOTAL REDEEM</div>
                                        <div class="dash-counts">
                                            <div class="dash-title">Redeem Receipt: {{$TotalRedeem}}</div>
                                        <h5>{{$Redeempayment }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-3">
                                    <div class="progress-bar bg-6" role="progressbar" style="width: 65%"
                                        aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    {{-- Showing Interest --}}
                    {{-- <div class="col-xl-4 col-sm-6 col-12">
                        <div class="card">
                        <div class="shadow p-3 mb-1 bg-body-tertiary rounded">
                            <div class="card-body">
                                <div class="dash-widget-header">
                                    <span class="dash-widget-icon bg-3">
                                        <i class="fas fa-exclamation"></i>
                                    </span>
                                    <div class="dash-count">
                                        <div class="dash-title">INTEREST</div>
                                        <div class="dash-counts">
                                            <br>
                                            <h5>{{$Interest}}</h5>
                                            <br>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-3">
                                    <div class="progress-bar bg-7" role="progressbar" style="width: 85%"
                                        aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div> --}}
                    
                </div>
                <div class="row">
                    <div class="col-xl-7 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title">Pawning & Redeem Analytics</h5>
                                    <div class="dropdown">
                                        <button class="btn btn-white btn-sm dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            Monthly
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Daliy</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Weekly</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Monthly</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Yearly</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between flex-wrap flex-md-nowrap">
                                    <div class="w-md-100 d-flex align-items-center mb-3 flex-wrap flex-md-nowrap">
                                        <div>
                                            <span>Pawning Total </span>
                                            <p class="h5 text-secondary me-5">Rs:{{$Pawningpayemt}}</p>
                                        </div>
                                        <div>

                                            <span>Redeem Total </span>
                                            <p class="h5 text-info me-5">Rs:{{$Redeempayment}}</p>
                                        </div>
                                        <div>
                                            <span>Interest</span>
                                            <p class="h5 text-success me-5">Rs:{{$Interest}}</p>
                                        </div>

                                    </div>
                                </div>
                                <div id="sales_chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title">Pawning & Redeem Daily Analytics</h6>
                                    <div class="dropdown">
                                        <button class="btn btn-white btn-sm dropdown-toggle" type="button"
                                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            Monthly
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Daliy</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Weekly</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Monthly</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item">Yearly</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="invoice_chart"></div>
                                <div class="text-center text-muted">
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="mt-4">
                                                <p class="mb-2 text-truncate"><i
                                                        class="fas fa-circle text-primary me-1"></i> Pawning</p>
                                                <h5>{{$Pawningpayemt | number_format(2)}}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="mt-4">
                                                <p class="mb-2 text-truncate"><i
                                                        class="fas fa-circle text-success me-1"></i> Redeem</p>
                                                <h5>{{$Redeempayment}}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="mt-4">
                                                <p class="mb-2 text-truncate"><i
                                                        class="fas fa-circle text-danger me-1"></i> Interest</p>
                                                <h5>{{$Interest | number_format(2)}}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title">Pawning payment</h5>
                                    </div>
                                    <div class="col-auto">
                                        <a href="invoices.html" class="btn-right btn btn-sm btn-outline-primary">
                                            View All
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="progress progress-md rounded-pill mb-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 47%"
                                            aria-valuenow="47" aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 28%"
                                            aria-valuenow="28" aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 15%"
                                            aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 10%"
                                            aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-auto">
                                            <i class="fas fa-circle text-success me-1"></i> Paid
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-circle text-warning me-1"></i> Unpaid
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-circle text-danger me-1"></i> Overdue
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-circle text-info me-1"></i> Draft
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-stripped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Receipt Number</th>
                                                <th>Customer NIC</th>
                                                <th>Customer Name</th>
                                                <th>Customer Address</th>
                                                <th>Customer Phone</th>
                                                <th>Receipt Type</th>
                                                
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Total Amount</th>
                                                <th>Interest</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customerdetails as $CustomerData)
                                            <tr>
                                                 <td>{{ $CustomerData->Receipt_Number}}</td>
                                                <td>{{ $CustomerData->Customer_NIC}}" </td>
                                                <td>{{ $CustomerData->Customer_Name}}</td>
                                                <td>{{ $CustomerData->Customer_Address}}</td>
                                                <td>{{ $CustomerData->Customer_Phone}} </td>
                                                <td>{{ $CustomerData->Receipt_Type}}</td>
                                               
                                                <td>{{ $CustomerData->Receipt_Date}}</td>
                                                <td>{{ $CustomerData->Amount}}</td>
                                                <td>{{ $CustomerData->Total_Amount}}</td>
                                                <td>{{ $CustomerData->Interest}}</td>
                                                <td class="text-right">
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle"
                                                            data-bs-toggle="dropdown" aria-expanded="false"><i
                                                                class="fas fa-ellipsis-h"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="edit-invoice.html"><i
                                                                    class="far fa-edit me-2"></i>Edit</a>
                                                            <a class="dropdown-item" href="view-invoice.html"><i
                                                                    class="far fa-eye me-2"></i>View</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-trash-alt me-2"></i>Delete</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-check-circle me-2"></i>Mark as
                                                                sent</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-paper-plane me-2"></i>Send Invoice</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-copy me-2"></i>Clone Invoice</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title">Redeem Payment</h5>
                                    </div>
                                    <div class="col-auto">
                                        <a href="estimates.html" class="btn-right btn btn-sm btn-outline-primary">
                                            View All
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="progress progress-md rounded-pill mb-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 39%"
                                            aria-valuenow="39" aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 35%"
                                            aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 26%"
                                            aria-valuenow="26" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-auto">
                                            <i class="fas fa-circle text-success me-1"></i> Sent
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-circle text-warning me-1"></i> Draft
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-circle text-danger me-1"></i> Expired
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Receipt_Number</th>
                                                <th>Redeem_Date</th>
                                                <th>Redeem_Number</th>
                                                <th>Original_Pawn_Amount</th>
                                                <th>Payable_Pawn_Amount</th>
                                                <th>Paid_Interest</th>
                                                <th>Payable_Interest</th>
                                                <th>Stamp_Fee</th>
                                                <th>Document_Charges</th>
                                                <th>Advance_Balance</th>
                                                <th>Discount</th>
                                                <th>Payable_Total</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($RedeemCustomer as $RadeemData)
                                            <tr>
                                                <td>{{ $RadeemData->Receipt_Number}}</td>
                                                <td>{{ $RadeemData->Redeem_Date}}</td>
                                                <td>{{ $RadeemData->Redeem_Number}}</td>
                                                <td>{{ $RadeemData->Original_Pawn_Amount}}</td>
                                                <td>{{ $RadeemData->Payable_Pawn_Amount}}</td>
                                                <td>{{ $RadeemData->Paid_Interest}}</td>
                                                <td>{{ $RadeemData->Payable_Interest}}</td>
                                                <td>{{ $RadeemData->Stamp_Fee}}</td>
                                                <td>{{ $RadeemData->Document_Charges}}</td>
                                                <td>{{ $RadeemData->Advance_Balance}}</td>
                                                <td>{{ $RadeemData->Discount}}</td>
                                                <td>{{ $RadeemData->Payable_Total}}</td>
                                                <td class="text-right">
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle"
                                                            data-bs-toggle="dropdown" aria-expanded="false"><i
                                                                class="fas fa-ellipsis-h"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="edit-invoice.html"><i
                                                                    class="far fa-edit me-2"></i>Edit</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-trash-alt me-2"></i>Delete</a>
                                                            <a class="dropdown-item" href="view-estimate.html"><i
                                                                    class="far fa-eye me-2"></i>View</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-file-alt me-2"></i>Convert to
                                                                Invoice</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-check-circle me-2"></i>Mark as
                                                                sent</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-paper-plane me-2"></i>Send
                                                                Estimate</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-check-circle me-2"></i>Mark as
                                                                Accepted</a>
                                                            <a class="dropdown-item" href="javascript:void(0);"><i
                                                                    class="far fa-times-circle me-2"></i>Mark as
                                                                Rejected</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

    @if(Auth::check() && (Auth::user()->role == 'Head_Officer' || Auth::user()->role == 'developer'))  
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="card">
                        <h2 style="text-align:center;">Redeem Summary by Branch</h2>
                            <div style="overflow-x: auto;">
                   <table border="1" cellpadding="5">
                    <thead>
                        <tr>
                            <th>Branch Code</th>
                            <th>Branch Name</th>
                            <th>Redeemed Amount</th>
                            <th>Redeemed Count</th>
                            <th>Not Redeemed Amount</th>
                            <th>Not Redeemed Count</th>
                            <th>Total Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pawnData as $data)
                            <tr>
                                <td>{{ $data->BC }}</td>
                                <td>{{ $data->name }}</td>
                                <td>{{ number_format($data->redeemed_amount, 2) }}</td>
                                <td>{{ $data->redeemed_count }}</td>
                                <td>{{ number_format($data->not_redeemed_amount, 2) }}</td>
                                <td>{{ $data->not_redeemed_count }}</td>
                                <td>{{ $data->total_records }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
                    </div>
                    <div>
                
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="card">
                        <h2 style="text-align:center;">Redeem Summary by Branch</h2>

                        <div style="overflow-x: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Branch Code (BC)</th>
                                        <th>Branch Name</th>
                                        <th>Total Redeem Amount</th>
                                        <th>Total Paid Interest</th>
                                        <th>Total Records</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($RedeemData as $item)
                                        <tr>
                                            <td>{{ $item->BC }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ number_format($item->total_redeem_amount, 2) }}</td>
                                            <td>{{ number_format($item->total_Paid_Interest, 2) }}</td>
                                            <td>{{ $item->total_records }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">No data found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                    
                    </div>
                </div>
            </div>
    @endif



<div class="offcanvas offcanvas-end custom-offcanvas-width" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasRightLabel">Customer comment</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">

    <!-- Username Field -->
<form id="feedbackForm">
  @csrf <!-- Laravel CSRF token -->
    <div class="mb-3">
    <div class="input-group">
        <span class="input-group-text" id="basic-addon1">Tictet Number</span>
        <input type="text" class="form-control" id="Customer_NIC" name="Customer_NIC" placeholder="Enter your Tictet Number" aria-describedby="basic-addon1">
    </div>
    </div>

    <!-- Result Section -->
    <div id="customerDetails" class="mt-3"></div>


   

    <!-- Feedback Textarea -->
    <div class="mb-3">
      <label for="feedback" class="form-label"> Comments</label>
      <textarea class="form-control"  id="feedback" name="feedback"  rows="4" placeholder="Write your comments here..."></textarea>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-success w-80">Submit Feedback</button>
</form>
  </div>
</div>
                </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#Customer_NIC').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault(); // Prevent default form action

            let nic = $(this).val();
            if(nic !== '') {
                $.ajax({
                    url: '/get-customer-data',
                    method: 'GET',
                    data: { nic: nic },
                    success: function(response) {
                        let html = '';

                        if (response.length > 0) {
                            response.forEach(function(item, index) {
                                html += `
                                         <div class="card p-3 mb-2">
                    <h5 style="align-content: center">Customer Receipt Details</h5>
                        <p>Customer NIC: ${item.Customer_NIC} </p>
                        <input type="hidden" value="${item.Customer_NIC} " name="Customer_Code" id="Customer_Code">
                        <p>Customer Name:  ${item.Customer_Name} 
                        <input type="hidden" value="${item.Customer_Name} " name="Customer_Name" id="Customer_Name">
                        </p>
                        <p>Tictet No: ${item.Receipt_Number }</p>
                        <p>Tictet Date: ${item.Receipt_Date}</p>
                        <input type="hidden" value="${item.Receipt_Date} " name="Receipt_Date" id="Receipt_Date">
                         <p>Final Date: ${item.Final_date}</p>
                         <p>Duration (Months)  ${item.Valid_Period} Months</p>
                         <p>Mortgaged Amount: ${item.Amount}</p>
                           <p>Pawn Status: ${item.IsRedeemed == 1 ? 'Redeemed' : 'Not Redeemed'}</p>
                    </div>
                           
                                `;
                            });
                        } else {
                            html = '<div class="text-warning">No records found.</div>';
                        }

                        $('#customerDetails').html(html);
                    },
                    error: function() {
                        $('#customerDetails').html('<div class="text-danger">Customer not found or server error.</div>');
                    }
                });
            }
        }
    });
});
</script>


<script>
    $(document).ready(function () {
        $('#feedbackForm').on('submit', function (e) {
            e.preventDefault();

            let formData = {
                Customer_NIC: $('#Customer_NIC').val(),
                feedback: $('#feedback').val(),
                Customer_Name: $('#Customer_Name').val(),
                Customer_Code: $('#Customer_Code').val(),
                Receipt_Date: $('#Receipt_Date').val(),
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                url: "{{ route('feedback.store') }}", // Replace with actual route
                method: "POST",
                data: formData,
                success: function (response) {
                    alert(response.message);  // Optional alert
                    location.reload();        // Refresh the page
                },
                error: function (xhr) {
                    alert(xhr.responseJSON.message || 'Submission failed');
                }
            });
        });
    });
</script>



</body>
</html>
@endsection