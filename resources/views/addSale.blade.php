@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Sale</title>
</head>
<body>

    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">Add Sale</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="#">Sell</a></li>
                                <li class="breadcrumb-item active">Add Sale</li>
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
                                        <div class="col-md-4">
                                            <div class="row">
                                                <label class="">Customer:</label>
                                                <div class="col-md-11">
                                                <input type="text" name="supplier" class="form-control"
                                                    placeholder="Supplier">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <label >Billing Address:</label>
                                                <div class="col-md-11">
                                                <label class="form-control" > Billing Address</label>
                                                </div>
                                            </div>

                                            <div class="row mt-4">
                                                <label >Shipping Address:</label>
                                                <div class="col-md-11">
                                                <label class="form-control" > Shipping Address</label>
                                             </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="row">
                                                <label class="">Pay term:</label>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                    <input type="text" name="pay_term" class="form-control"
                                                    placeholder="Pay term">
                                                    </div>
                                                    <div class="col-md-6">
                                                    <select class=" form-control " name="pay_term" aria-hidden="true">
                                                        <option value="" >Please Select</option>
                                                        <option value="Months">Months</option>
                                                        <option value="Days" >Days</option>
                                                    </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-md-4">
                                                <label class="form-control">Status:</label>
                                                </div>

                                                <div class="col-md-7">
                                                    <select class=" form-control " name="status" aria-hidden="true">
                                                        <option value="" >Please Select</option>
                                                        <option value="Final">Final</option>
                                                        <option value="Draft" >Draft</option>
                                                        <option value="Quotation">Quotation</option>
                                                        <option value="Proforma" >Proforma</option>
                                                    </select>
                                                </div>

                                            </div>
                                            <div class="row mt-4">
                                                <label class="">Invoice No.:</label>
                                                <div class="col-md-11">
                                                <input type="text" name="invoice_no" class="form-control"
                                                    placeholder="Invoice No">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="row">
                                                <div class="col-md-5">
                                                <label class="form-control">Sale Date:</label></div>
                                                <div class="col-md-7">
                                                    <input type="date" name="sale_date" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                <label class="form-control">Invoice scheme:</label></div>
                                                <div class="col-md-6">
                                                    <select class=" form-control " name="Invoice_scheme" aria-hidden="true">
                                                        <option value="" >Please Select</option>
                                                        <option value="defualt">default</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                    <label for="no">Attach Document:</label>
                                                    <div class="col-md-8">
                                                    <label class="form-control"></label></div>
                                                    <div class="col-md-3">
                                                        <div class="btn btn-primary btn-sm">
                                                            <label class="form-label text-white m-1" for="atach_document">Browse</label>
                                                            <input type="file" class="form-control d-none" id="atach_document" />
                                                        </div>
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
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Discount</th>
                                                <th>Warranty</th>
                                                <th>Subtotal </th>

                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Table</td>
                                                <td>10</td>
                                                <td>1000</td>
                                                <td>10%</td>
                                                <td>5</td>
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
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ......................3rd Section No and Date.............................. --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <hr size="6" style="color: blue">
                            <div class="card-body">

                                    <div class="row ">
                                        <div class="col-md-4">
                                            <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-control">Discount Type:</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                    <select class=" form-control " name="discount_type" aria-hidden="true">
                                                        <option value="" >Please Select</option>
                                                        <option value="Fixed">Fixed</option>
                                                        <option value="Precentage">Precentage</option>
                                                    </select>
                                                    </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <label class="form-control">Order Tax:</label>
                                                </div>
                                                <div class="col-md-6">
                                                <select class=" form-control " name="discount_type" aria-hidden="true">
                                                    <option value="" >Please Select</option>
                                                    <option value="Fixed">None</option>
                                                </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="row ">
                                                <label class="">Discount Amount:</label>
                                                <div class="col-md-11">
                                                <input type="text" name="invoice_no" class="form-control"
                                                    placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <label class="form-control">Discount Amount:</label></div>
                                                <div class="col-md-5">
                                                    <label class="form-control">(-) 0.00</label></div>
                                            </div>
                                            <div class="row">
                                                     <div class="col-md-7">
                                                        <label class="form-control">Order Tax:</label></div>
                                                    <div class="col-md-5">
                                                        <label class="form-control">(+) 0.00</label></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <label for="form-control">Sell note</label>
                                        <textarea class="form-control" id="description" rows="2"></textarea>

                                    </div>
                            </div>
                        </div>
                    </div>
                </div>


                 {{-- ......................4th Section No and Date.............................. --}}
                 <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <hr size="6" style="color: blue">
                            <div class="card-body">

                                    <div class="row ">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="row">
                                                    <label class="">Shipping Details</label>
                                                    <div class="col-md-11">
                                                        <textarea name="shipping_details" placeholder="Shipping Details" class="form-control" id="description" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="row">
                                                    <label class="">Shipping Address</label>
                                                    <div class="col-md-11">
                                                        <textarea name="shipping_details" placeholder="Shipping Address" class="form-control" id="description" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="row">
                                                    <label class="">Shipping Charges:</label>
                                                    <div class="col-md-11">
                                                    <input type="text" name="shipping_charges:" class="form-control"
                                                        placeholder="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="row mt-4">
                                                    <label >Shipping Status:</label>
                                                    <div class="col-md-11">
                                                        <select class=" form-control " name="shipping_status" aria-hidden="true">
                                                            <option value="" >Please Select</option>
                                                            <option value="Ordered">Ordered</option>
                                                            <option value="Packed" >Packed</option>
                                                            <option value="Shipped">Shipped</option>
                                                            <option value="Delivered" >Delivered</option>
                                                            <option value="Cancelled">Cancelled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="row mt-4">
                                                    <label class="">Delivered To:</label>
                                                    <div class="col-md-11">
                                                    <input type="text" name="delivered_to:" class="form-control"
                                                        placeholder="Delivered To">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="row mt-4">
                                                        <label for="no">Attach Document:</label>
                                                        <div class="col-md-8">
                                                        <label class="form-control"></label></div>
                                                        <div class="col-md-3">
                                                            <div class="btn btn-primary btn-sm">
                                                                <label class="form-label text-white m-1" for="atach_document">Browse</label>
                                                                <input type="file" class="form-control d-none" id="atach_document" />
                                                            </div>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <hr size="4">
                                            <div class="col-md-8"></div>
                                            <div class="col-md-4 text-right">
                                                <div class="row">
                                                    <div class="col-md-7">
                                                    <label class="form-control text-right">Total Payable:</label></div>
                                                    <div class="col-md-5">
                                                        <label class="form-control">0</label></div>
                                                </div>
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
