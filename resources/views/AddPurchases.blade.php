@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Purchases</title>
</head>
<body>

    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">Add Purchases</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="#">Purchases</a></li>
                                <li class="breadcrumb-item active">Add Purchases</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">

                                <form action="register_user" method="post">
                                    @csrf
                                    <div class="row">

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Supplier:</label>
                                                <input type="text" name="surname" class="form-control"
                                                    placeholder="Mr/Mrs">
                                            </div>


                                        </div>

                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>First Name:</label>
                                                <input type="text" name="first_name" class="form-control"
                                                    placeholder="First Name">
                                            </div>

                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Last Name:</label>
                                                <input type="text" name="last_name" class="form-control"
                                                    placeholder="Last Name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control"
                                                    placeholder="Email">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <br><br>
                                                <input type="checkbox">
                                                <label class="">
                                                    Is active ?
                                                </label>
                                                <i class=" ion-information-circled ">
                                                </i>
                                            </div>
                                        </div>
                                    </div>

                            </div>

                        </div>
                    </div>


                    {{-- Roles and Permissions section --}}
                    <div class="card">
                        <div class="card-hader">
                            <h5 class="card-title mt-2 ml-4">Roles and Permissions :</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>User name</label>
                                        <input type="text" name="user_name" class="form-control"
                                            placeholder="User name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Password:</label>
                                        <input type="password" name="password" class="form-control"
                                            placeholder="Password">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Confirm Password:</label>
                                        <input type="password" name="confirm_password" class="form-control"
                                            placeholder="Confirm Password">
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
                        </div>
                    </div>


                    {{-- sales section --}}
                    <div class="card">
                        <div class="card-hader">
                            <h5 class="card-title mt-2 ml-4">Sales :</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sales Commission Percentage (%):</label>
                                        <input type="text" name="surname" class="form-control"
                                            placeholder="Sales Commission Percentage (%)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Max sales discount percent</label>
                                        <input type="text" name="first_name" class="form-control"
                                            placeholder="Max sales discount percent">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox">
                                    <label class="">
                                        Allow Selected Contacts
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- More Informations section --}}
                    <div class="card">
                        <div class="card-hader">
                            <h5 class="card-title mt-2 ml-4">More Informations :</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Date of birth:</label>
                                        <input type="date" name="birthday" class="form-control"
                                            placeholder="Date of birth">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Gender:</label>
                                        <div class="form-group">
                                            <select class="select form-control" name="gender">
                                                <option value="male"> Male </option>
                                                <option value="female"> Female </option>
                                                <option value="other"> Other </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Marital Status:</label>
                                        <select class="select form-control" name="marital_status">
                                            <option value="married" class="form-control"> Married </option>
                                            <option value="unmarried" class="form-control"> Unmarried </option>
                                            <option value="divorced" class="form-control"> Divorced </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Blood Group:</label>
                                        <input type="text" name="blood_group" class="form-control"
                                            placeholder="Blood Group:">
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Mobile Number:</label>
                                        <input type="text" name="mobile" class="form-control"
                                            placeholder="Mobile Number">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Alternate contact number:</label>
                                        <input type="text" name="mobile_2" class="form-control"
                                            placeholder="Alternate contact number">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Family contact number:</label>
                                        <input type="text" name="mobile_3" class="form-control"
                                            placeholder="Family contact number">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Facebook Link:</label>
                                        <input type="text" name="facebook_link" class="form-control"
                                            placeholder="Facebook Link">
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Twitter Link:</label>
                                        <input type="text" name="twitter_link" class="form-control"
                                            placeholder="Twitter Link">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Social Media 1:</label>
                                        <input type="text" name="social_media_1" class="form-control"
                                            placeholder="Social Media 1">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Social Media 2:</label>
                                        <input type="text" name="social_media_2" class="form-control"
                                            placeholder="Social Media 2">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Social Media 3:</label>
                                        <input type="text" name="social_media_3" class="form-control"
                                            placeholder="Social Media 3">
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Custom field 1:</label>
                                        <input type="text" name="custom_field_1" class="form-control"
                                            placeholder="Custom field 1">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Guardian Name:</label>
                                        <input type="text" name="guardian_name" class="form-control"
                                            placeholder="Guardian Name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ID proof name:</label>
                                        <input type="text" name="id_proof_name" class="form-control"
                                            placeholder="ID proof name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ID proof number:</label>
                                        <input type="text" name="id_proof_number" class="form-control"
                                            placeholder="ID proof number">
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Permanent Address:</label>
                                        <textarea class="form-control" name="permanent_address"
                                            placeholder="Permanent Address" id="textAreaExample3" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Current Address:</label>
                                        <textarea class="form-control" name="current_address"
                                            placeholder="Current Address" id="textAreaExample3" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <hr size="5">

                            <h5 class="card-title mt-2 ml-4">Bank Details:</h5>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Account Holder's Name:</label>
                                        <input type="text" name="account_holder's_name" class="form-control"
                                            placeholder="Account Holder's Name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Account Number:</label>
                                        <input type="text" name="account_number" class="form-control"
                                            placeholder="Account Number">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Bank Name:</label>
                                        <input type="text" name="bank_name" class="form-control"
                                            placeholder="Bank Name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Bank Identifier Code:</label>
                                        <input type="text" name="bank_identifier_code" class="form-control"
                                            placeholder="Bank Identifier Code">
                                    </div>
                                </div>
                            </div>



                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Branch:</label>
                                        <input type="text" name="branch" class="form-control" placeholder="Branch">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tax Payer ID:</label>
                                        <input type="text" name="tax_payer_id" class="form-control"
                                            placeholder="Tax Payer ID">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="text-center mt-4 mb-5">
                    <button type="submit" class="btn btn-primary mb-5">Add User</button>
                </div>
                </form>
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
