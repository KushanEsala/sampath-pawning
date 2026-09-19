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

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #3498db;
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


  {{-- Search Form --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('stock.search') }}">
                @csrf

                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Stock / Receipt Number</label>
                        <input
                            type="text"
                            name="Stock_Number"
                            class="form-control"
                            placeholder="Enter Receipt Number"
                            value="{{ old('Stock_Number') }}"
                            required
                            autofocus
                        >
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            🔍 Search
                        </button>
                    </div>
                </div>

                @error('Stock_Number')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </form>
        </div>
    </div>

    {{-- Results from t_pawn_sums --}}
    <div class="card">
        <div class="card-body">
            <h5 class="section-title">Pawn Summary </h5>

            @if($results->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Receipt No</th>
                                <th>Customer Name</th>
                                <th>Receipt Date</th>
                                <th>Pawn Weight</th>
                                <th>Total Weight</th>
                                <th>Amount (Rs.)</th>
                                <th>Interest</th>
                                <th>Status</th>
                                <th>final date </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $row)
                                <tr>
                                    <td>{{ $row->Invoice_Number }}</td>
                                    <td>{{ $row->Customer_Name ?? '-' }}</td>
                                    <td>{{ $row->Receipt_Date }}</td>
                                    <td class="text-end">{{ $row->Pawn_Weight }}</td>
                                    <td class="text-end">{{ $row->Total_Weight }}</td>
                                    <td class="text-end">
                                        {{ number_format($row->Amount, 2) }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($row->Interest, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($row->IsRedeemed)
                                            <span class="badge bg-success">Redeemed</span>
                                        @else
                                            <span class="badge bg-warning">Active</span>
                                        @endif
                                    </td>
                                     <td>{{ $row->Final_date }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted">
                    No records found in Pawn Summary.
                </div>
            @endif

        </div>
    </div>

    {{-- Results from t_pawn_details --}}
    <div class="card">
        <div class="card-body">
            <h5 class="section-title">Pawn Artical Details </h5>

            @if($results_Details->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Receipt No</th>
                                <th>Category</th>
                                <th>Articles</th>
                                <th>Condition</th>
                                <th>Karatage</th>
                                <th>Weight</th>
                                <th>QTY</th>
                                <th>Value (Rs.)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results_Details as $row)
                                <tr>
                                    <td>{{ $row->Receipt_Number }}</td>
                                    <td>{{ $row->Category ?? '-' }}</td>
                                    <td>{{ $row->Articles ?? '-' }}</td>
                                    <td>{{ $row->Condition ?? '-' }}</td>
                                    <td class="text-end">{{ $row->Karatage ?? '-' }}</td>
                                    <td class="text-end">{{ $row->Weight ?? '-' }}</td>
                                    <td class="text-center">{{ $row->QTY ?? '-' }}</td>
                                    <td class="text-end">
                                        {{ isset($row->Value) ? number_format($row->Value, 2) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if($row->IsRedeemed)
                                            <span class="badge bg-success">Redeemed</span>
                                        @elseif($row->isForfeit)
                                            <span class="badge bg-danger">Forfeited</span>
                                        @else
                                            <span class="badge bg-warning">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted">
                    No records found in Pawn Details.
                </div>
            @endif

        </div>
    </div>

    {{-- Results from t_pawn_trans --}}
    <div class="card">
        <div class="card-body">
@php
    $capital = optional($results->first())->RePawning_amount ?? 0;
    $rate1   = optional($Receipt_Type->first())->rate1 ?? 0;
    $rate2   = optional($Receipt_Type->first())->rate3 ?? 0;

    // Pawning interest calculation
    $interest10 = $capital * ($rate1 / 100);
    $interest20 = $capital * ($rate2 / 100);
@endphp

<h5 class="section-title">
    Pawn Transactions - (Current Pawning Capital Balance - {{ number_format($capital, 2) }})
</h5>


<p>
    10 Days Interest({{($rate1) }}%):
    <strong>{{ number_format($interest10, 2) }}</strong>
</p>
<p>
    30 Days Interest({{($rate2) }}%): =
    <strong>{{ number_format($interest20, 2) }}</strong>
</p>
            @if($results_transtion->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Pawn Amount (Rs.)</th>
                                <th>Date</th>
                                <th>Paid Interest (Rs.)</th>
                                <th>Paid Capital (Rs.)</th>
                                <th>Trans Type</th>
                                <th>Credit Amount</th>
                                <th>Debit Amount</th>
                                <th>Trans Amount (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results_transtion as $row)
                                <tr>

                                    <td class="text-end">
                                        {{ isset($row->Pawn_Amount) ? number_format($row->Pawn_Amount, 2) : '-' }}
                                    </td>
                                    <td>{{ $row->dDate ?? '-' }}</td>
                                    <td class="text-end">
                                        {{ isset($row->Paided_Interest) ? number_format($row->Paided_Interest, 2) : '-' }}
                                    </td>
                                    <td class="text-end">
                                        {{ isset($row->Paided_Captional) ? number_format($row->Paided_Captional, 2) : '-' }}
                                    </td>
                                    <td>{{ $row->trans_type ?? '-' }}</td>
                                      <td class="text-end">
                                        {{ isset($row->Cr_amount) ? number_format($row->Cr_amount, 2) : '-' }}
                                    </td>
                                      <td class="text-end">
                                        {{ isset($row->Dr_amount) ? number_format($row->Dr_amount, 2) : '-' }}
                                    </td>
                                              <td class="text-end">
                                        {{ isset($row->trans_amount) ? number_format($row->trans_amount, 2) : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted">
                    No records found in Pawn Transactions.
                </div>
            @endif

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