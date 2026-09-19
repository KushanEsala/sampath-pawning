@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <title>Add Expenses</title>
    <style>
        /* style.css */
        body {
            margin: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 60px;
            border-radius: 8px;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 6px 12px -2px, rgba(0, 0, 0, 0.3) 0px 3px 7px -3px;
        }

        h2 {
            text-align: center;
            font-size: 20px;

        }

        form {
            justify-content: center;
            margin-bottom: 20px;
        }

        label {
            color: rgb(46, 38, 38);
            font-weight: bold;
            padding: 4px;
            font-size: 18px;
        }

    </style>
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
                                <h4 class="card-title m-3">Add Expenses</h4>
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

                                <div>
                                <form name="add-blog-post-form" id="add-blog-post-form" method="post"
                                action="{{route('add_expense')}}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8"></div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text" id="btnGroupAddon2">No :
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                            <input type="text" id="expense_no" name="expense_no" value="{{$maxReceipt+1}}"
                                                class="form-control" placeholder="Expense Number:"
                                                aria-label="Expense Number:" aria-describedby="btnGroupAddon2">
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-8"></div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text" id="btnGroupAddon2">Date :
                                                &nbsp;&nbsp;
                                            </div>
                                            <input type="date" id="date" name="date" value=""
                                                class="form-control" placeholder="Expense date"
                                                aria-label="Expense date" aria-describedby="btnGroupAddon2">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="form_need">Expense For </label>
                                        <select id="expense_type" name="expense_type" class="form-control"
                                            required="required" data-error="Please specify your need.">
                                            <option value="" selected disabled>--Select Your Expense For--</option>
                                            <option value="Request order status">Request order status</option>
                                            <option value="Haven't received cashback yet">Haven't received cashback yet
                                            </option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="form_need">Expense Note</label>
                                            <textarea name="expense_note" id="expense_note" class="form-control"
                                                required="" placeholder="Enter the Expense Note "></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="form_need">Amount </label>
                                            <input type="number" min=0 step=0.01 id="amount" name="amount" value=""
                                                class="form-control form-control-lg" placeholder="Amount:"
                                                aria-label="Amount:" aria-describedby="btnGroupAddon2" required>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-4"></div>
                                    <div class="col">
                                        <br>
                                        <button type="submit" name="save" id="save"
                                            class="btn btn-outline-info btn-lg shadow">SAVE</button>
                                        <button type="button" name="pawn_delete" id="pawn_delete"
                                            class="btn btn-outline-danger btn-lg shadow pawn_delete">DELETE</button>
                                        <button type="button" name="pawn_cancel" id="pawn_cancel"
                                            class="btn btn-outline-warning btn-lg shadow pawn_cancel">CANCEL</button>
                                    </div>
                                     <div class="col-md-3"></div>
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
                var dateObj = new Date();
                document.getElementById('date').value = dateObj.toISOString().slice(0, 10);
            </script>

            <script src="assets/js/jquery-3.6.0.min.js"></script>
            <script src="assets/js/feather.min.js"></script>
            {{-- <script src="assets/plugins/select2/js/select2.min.js"></script> --}}
            <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
            <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
            <script src="assets/plugins/datatables/datatables.min.js"></script>
            <script src="assets/js/script.js"></script>
            <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
            <script src="assets/plugins/apexchart/chart-data.js"></script>
            <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
                integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
                crossorigin="anonymous"></script>
</body>
@endsection

</html>
