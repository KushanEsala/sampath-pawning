@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Products</title>
</head>
<body>

    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                
                {{-- ......................Top Section No and Date.............................. --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="col-md-9">
                                <h4 class="card-title m-3">Add New Product</h4>
                            </div>
                            <hr size="6" style="color: blue">

                            <div class="card-body">
                                <form action="register_user" method="post">
                                    @csrf
                                    <div class="row ">
                                        <div class="col-md-4">
                                                <div class="col-md-5">
                                                <label for="no">Product Name:</label></div>
                                                <div class="col">
                                                    <input type="text" name="product_name" class="form-control"
                                                    placeholder="Product Name">
                                                </div>
                                        </div>
                                        <div class="col-md-4">
                                                <div class="col-md-4">
                                                <label for="no">Code:</label></div>
                                                <div class="col">
                                                    <input type="text" name="no" class="form-control"
                                                    placeholder="Number">
                                                </div>
                                        </div>
                                        <div class="col-md-4">
                                                <div class="col-md-4">
                                                <label for="no">Barcode:</label></div>
                                                <div class="col">
                                                    <input type="text" name="barcode" class="form-control"
                                                    placeholder="Barcode">
                                                </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4 ">
                                        <div class="col-md-4">
                                                <label for="receipt_type">Unit:</label>
                                                <select class=" form-control " name="unit" aria-hidden="true">
                                                    <option value="" >Please Select</option>
                                                    <option value="">Pieces (Pc(s))</option>
                                                    <option value="Suppliers" >Suppliers</option>
                                                    <option value="Customers">Customers</option>
                                                </select>
                                        </div>

                                        <div class="col-md-4">
                                                <div class="col">
                                                <label for="no">Alert Quantity: </label></div>
                                                <div class="col">
                                                    <input type="text" name="alert" class="form-control"
                                                    placeholder="Alert Quantity">
                                                </div>
                                        </div>
                                        <div class="col-md-4">
                                                <div class="col">
                                                <label for="no">Product image:</label></div>
                                                <div class="col">
                                                    <div class="btn btn-primary ">
                                                        <label class="form-label text-white m-1" for="customFile1">Browse</label>
                                                        <input type="file" class="form-control d-none" id="customFile1" />
                                                    </div>
                                                </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4 ">
                                        <div class="col-md-6">
                                                <label for="receipt_type">Department:</label>
                                                <select class=" form-control " name="department" aria-hidden="true">
                                                    <option value="" >Please Select</option>
                                                    <option value="Department1">Department1</option>
                                                    <option value="Department2" >Department2</option>
                                                    <option value="Department3">Department3</option>
                                                </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="receipt_type">Category:</label>
                                            <select class=" form-control " name="category" aria-hidden="true">
                                                <option value="" >Please Select</option>
                                                <option value="Category1">Category1</option>
                                                <option value="Category2" >Category2</option>
                                                <option value="Category3">Category3</option>
                                            </select>
                                        </div>

                                    <div class="row mt-4">
                                        <div class="col-md-4">
                                                <div class="col-md-5">
                                                <label for="no">Brand: (optional)</label></div>
                                                <div class="col">
                                                    <input type="text" name="brand" class="form-control"
                                                    placeholder="Brand Name">
                                                </div>
                                        </div>
                                        <div class="col-md-4">
                                                <div class="col-md-5">
                                                <label for="no">Color: (optional)</label></div>
                                                <div class="col">
                                                    <input type="text" name="color" class="form-control"
                                                    placeholder="Color">
                                                </div>
                                        </div>
                                        <div class="col-md-4">
                                                <div class="col-md-6">
                                                <label for="no">Material: (optional)</label></div>
                                                <div class="col">
                                                    <input type="text" name="material" class="form-control"
                                                    placeholder="Material">
                                                </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-5">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                        <label class="form-control">GRN Price</label>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <input type="text" name="GRN_Price" class="form-control" placeholder="GRN Price">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                        <label class="form-control">Sale Price</label>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <input type="text" name="sale_price" class="form-control" placeholder="Sale Price">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                        <label class="form-control">Arg Price</label>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <input type="text" name="arg_price" class="form-control" placeholder="Arg Price">
                                                        </div>
                                                    </div>



                                        <div class="col-md-2"></div>
                                        <div class="col-md-5"></div>
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
