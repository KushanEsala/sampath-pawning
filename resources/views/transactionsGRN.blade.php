@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>GRN</title>
</head>
<body>

    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">GRN</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="#">Transactions</a></li>
                                <li class="breadcrumb-item active">GRN</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- ......................Top Section No and Date.............................. --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <hr size="6" style="color: blue">
                            <div class="card-body">
                                <form action="register_user" method="post">
                                    @csrf
                                    <div class="row ">
                                        <div class="col-md-7">
                                            <div class="row">
                                                <div class="col-md-3">
                                                <label class="form-control">Supplier:</label></div>
                                                <div class="col-md-6">
                                                    <input type="text" name="supplier" class="form-control"
                                                    placeholder="Supplier">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-5">
                                                <label class="form-control">Outstand Amount:</label></div>
                                                <div class="col-md-4">
                                                    <input type="text" name="supplier" class="form-control"
                                                    placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="row">
                                                <div class="col-md-4">
                                                <label class="form-control">No:</label></div>
                                                <div class="col-md-8">
                                                    <input type="text" name="no" class="form-control"
                                                    placeholder="Number">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                <label class="form-control">Date:</label></div>
                                                <div class="col-md-8">
                                                    <input type="date" name="date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                <label class="form-control">Invoice No:</label></div>
                                                <div class="col-md-8">
                                                    <input type="text" name="invoice_no" class="form-control"
                                                    placeholder="Number">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>


                 {{-- ......................Middle Section .............................. --}}
                 <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <hr size="6" style="color: blue">
                            <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-2">
                                                <label class="form-control"><i class="fa fa-search"></i></label></div>
                                                <div class="col-md-10">
                                                    <input type="text" name="search" class="form-control"
                                                    placeholder="Enter Product Name / SKU / Scan Bar code">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                    <div class="row mb-3">
                                        <table class="table table-bordered border-success text-center ">
                                            <thead class="table-success">
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th>QTY</th>
                                                <th>Amount</th>
                                                <th>Discount Percent </th>
                                                <th>Discount Value </th>
                                                <th>Net Amount </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>12</td>
                                                <td>EX</td>
                                                <td>1000</td>
                                                <td>10</td>
                                                <td>10000</td>
                                                <td>10%</td>
                                                <td>1000</td>
                                                <td>9000</td>
                                            </tr>
                                        </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <hr size="4">
                                        <div class="col-md-8"></div>
                                        <div class="col-md-4 text-right">
                                            <div class="row">
                                                <div class="col-md-7">
                                                <label class="form-control text-right">Total Items:</label></div>
                                                <div class="col-md-5">
                                                    <label class="form-control">0</label></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-7">
                                                <label class="form-control text-right">Total Amount:</label></div>
                                                <div class="col-md-5">
                                                    <label class="form-control">0.00</label></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-7">
                                                <label class="form-control text-right">Total Discount:</label></div>
                                                <div class="col-md-5">
                                                    <label class="form-control">0.00</label></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-7">
                                                <label class="form-control text-right">Net Total Amount:</label></div>
                                                <div class="col-md-5">
                                                    <label class="form-control">0.00</label></div>
                                                </div>
                                            </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>


                 {{-- ......................Bottom Section .............................. --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <hr size="6" style="color: blue">
                            <div class="card-body">
                                <form action="register_user" method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">

                                            <div class="row">
                                                <div class="col-md-4">
                                                <label class="form-control">Description:</label></div>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" id="description" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mt-4">
                                                    <label class="form-control">Operator:</label></div>
                                                <div class="col-md-8 mt-4">
                                                    <label class="form-control ">OperatorName</label></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6"> </div>
                                        <div class="row">
                                            <div class="col-md-6"></div>
                                            <div class="col-md-6">
                                                <div class="text-center mt-4 mb-5">
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                    <a href="#" class="btn btn-danger">  Delete</a>
                                                    <a href="#" class="btn btn-success">  Create</a>
                                                    <a href="#" class="btn btn-warning">  Exit</a>
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



    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>
    <script src="assets/plugins/select2/js/select2.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
</body>
</html>
@endsection
