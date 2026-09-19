@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Add Suppliers</title>


</head>

<body>

    <div class="main-wrapper">

        <div class="page-wrapper">
            <div class="content container-fluid">

                {{-- <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">Add Suppliers</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{route('users')}}">Suppliers</a></li>
                                <li class="breadcrumb-item active">Add Suppliers</li>
                            </ul>
                        </div>
                    </div>
                </div> --}}

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="col-md-9">
                                <h4 class="card-title m-3">Add Suppliers</h4>
                            </div>
                            <hr size="6" style="color: blue">
                            <div class="card-body">

                                <form action="register_user" method="post">
                                    @csrf
                                    <div class="row">
                                       <h4 >Add a new contact</h4>
                                        <div class="col-md-4">


                                                <label>Contact type:</label>
                                                <div class=" form-group">
                                                    <select class="select form-control" name="Contact_type">
                                                        <option selected>Please Select</option>
                                                        <option>Suppliers</option>
                                                        <option>Customers</option>
                                                        <option>Both ( Suppliers / Customers )</option>
                                                    </select>
                                                </div>


                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input class="form-check-input" type="radio" name="flexRadioDefault">
                                                <label class="">
                                                    Individual
                                                </label>
                                                <input class="form-check-input" type="radio" name="flexRadioDefault">
                                                <label class="">
                                                    Business
                                                </label>
                                            </div>


                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Contact ID:</label>
                                                <input type="text" name="last_name" class="form-control" placeholder="Last Name">
                                            </div>

                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-4">
                                        <label>Customer Group:</label>
                                        <div class=" form-group">
                                            <select class="select form-control" name="Contact_type">
                                                <option selected>Please Select</option>
                                                <option>None</option>

                                            </select>
                                        </div>
                                        </div>
                                    </div>






                                    <h4 class="card-title mt-4">Roles and Permissions</h4>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-control">
                                                <label>User name</label>
                                                <input type="text" name="user_name" class="form-control" placeholder="User name">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Password</label>
                                                <input type="password" name="password" class="form-control" placeholder="Password">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Role</label>
                                            <div class="form-group">
                                                <select class="select form-control" name="role">
                                                    <option selected>Admin</option>
                                                    <option>Cashier</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Access locations</label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="checkbox">
                                            <label class="">
                                                All Locations
                                            </label>
                                        </div>
                                    </div>


                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">Add User</button>
                                    </div>

                            </form>


                        {{-- <h4 class="card-title mt-4">Shipping Address</h4> --}}
                        {{-- <form action="#">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Copy from
                                        Billing</button>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name:</label>
                                            <input type="text" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>State:</label>
                                            <input type="text" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Address:</label>
                                            <textarea rows="5" cols="5" class="form-control"
                                                placeholder="Enter Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Country:</label>
                                            <select class="select">
                                                <option>Select Country</option>
                                                <option value="Afganistan">Afghanistan</option>
                                                <option value="Albania">Albania</option>
                                                <option value="Algeria">Algeria</option>
                                                <option value="American Samoa">American Samoa</option>
                                                <option value="Andorra">Andorra</option>
                                                <option value="Angola">Angola</option>
                                                <option value="Anguilla">Anguilla</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>City:</label>
                                            <input type="text" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Phone:</label>
                                            <input type="text" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Zip Code:</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary">Add Customer</button>
                                </div>
                            </form> --}}
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

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/js/script.js"></script>


    <script data-cfasync="false" src="../../../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js">
    </script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>

    <script src="assets/js/script.js"></script>
</body>

</html>

@endsection
