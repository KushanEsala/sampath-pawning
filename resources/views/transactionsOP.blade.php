@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Opening Stock</title>
</head>
<body>

    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                {{-- <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">Opening Stock</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="#">Stock</a></li>
                                <li class="breadcrumb-item active">Opening Stock</li>
                            </ul>
                        </div>
                    </div>
                </div> --}}

                {{-- ......................Top Section No and Date.............................. --}}
                <div class="row">

                    <div class="col-md-12">
                        <div class="card">

                            <div class="col-md-9">
                                <h4 class="card-title m-3">Opening Stock</h4>
                            </div>
                            <hr size="6" style="color: blue">
                            <div class="card-body">
                                <form action="register_user" method="post">
                                    @csrf
                                    <div class="row ">
                                        <div class="col-md-8"></div>
                                        <div class="col-md-4">
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
                                                <label class="form-control datepicker">Date:</label></div>
                                                <div class="col-md-8">
                                                    <input type="date" name="date" id="date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                <label class="form-control">Ref No:</label></div>
                                                <div class="col-md-8">
                                                    <input type="text" name="no" class="form-control"
                                                    placeholder="Reference No">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 mt-2">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-2">
                                                <label class="form-control "><i class="fa fa-search"></i></label></div>
                                                <div class="col-md-10">
                                                    <input type="text" name="search" class="form-control"
                                                    placeholder="Enter Product Name / SKU / Scan Bar code">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                    <div class="row mb-3">
                                        <table class="table table-striped table-bordered border-success text-center ">
                                            <thead class="table-success ">
                                            <tr>
                                                <th style="width: 10%">Code</th>
                                                <th>Name</th>
                                                <th style="width: 10%">Price</th>
                                                <th style="width: 10%">QTY</th>
                                                <th style="width: 10%">Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>12</td>
                                                <td>EX</td>
                                                <td>1200</td>
                                                <td>10</td>
                                                <td>12000</td>
                                            </tr>
                                            <tr>
                                                <td>12</td>
                                                <td>EX</td>
                                                <td>1200</td>
                                                <td>10</td>
                                                <td>12000</td>
                                            </tr>
                                            <tr>
                                                <td>12</td>
                                                <td>EX</td>
                                                <td>1200</td>
                                                <td>10</td>
                                                <td>12000</td>
                                            </tr>
                                            <tr>
                                                <td>12</td>
                                                <td>EX</td>
                                                <td>1200</td>
                                                <td>10</td>
                                                <td>12000</td>
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
                                                    <a href="#" class="btn btn-success">  Print</a>
                                                    <button type="reset" class="btn btn-warning">  Reset</button>
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

    <script>
        $( document ).ready(function() {
            var date = Date();
        $("#date").attr("placeholder",date);
        });
    </script>

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
