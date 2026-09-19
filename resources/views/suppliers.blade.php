@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Suppliers</title>

</head>

<body>

    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                {{-- <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="page-title">Suppliers</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                                <li class="breadcrumb-item active">Suppliers</li>
                            </ul>
                        </div>
                    </div>
                </div> --}}
                <div class="row">
                    <div class="col-sm-12">

                        <div class="card">
                            <div class="col-md-9">
                                <h4 class="card-title m-3">Suppliers</h4>
                            </div>
                            <hr size="6" style="color: blue">
                            <div class="card-body">



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


                                {{-- <a href=" {{route('add_supplier')}} " class="btn btn-success text-end">
                                    <i data-feather="plus"></i>
                                    Add Suppliers
                                </a> --}}

                            <button type="button" class="btn btn-success mt-1" data-bs-toggle="modal" data-bs-target="#bs-example-modal-lg">
                                <i class="fas fa-plus"></i>
                                Add Suppliers
                            </button>

                            <div class="modal fade" id="bs-example-modal-lg" tabindex="-1" role="dialog"
                                    aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myLargeModalLabel">Add Suppliers </h4>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="card">
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
                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>




                                            </div>
                                        </div>
                                    </div>
                                </div>




                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-stripped table-center table-hover datatable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Action</th>
                                                <th>Contact ID</th>
                                                <th>Business Name</th>
                                                <th>Name</th>
                                                <th>Tax number</th>
                                                <th>Pay term</th>
                                                <th>Opening Balance</th>
                                                <th>Advence Balance</th>
                                                <th>Added On</th>
                                                <th>Address</th>
                                                <th>Mobile</th>
                                                <th>Total Purchase Due</th>
                                                <th>Total Purchase Return Due</th>

                                            </tr>
                                        </thead>
                                        <tbody>

                                            {{-- @foreach ($users as $data ) --}}
                                            {{-- <tr>
                                                <td>{{$data->id}}</td>
                                                <td>{{$data->username}}</td>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="profile.html"> --}}
                                                            {{-- <img class="avatar avatar-sm me-2 avatar-img rounded-circle"
                                                                src="assets/img/profiles/avatar-01.jpg"
                                                                alt="User Image">  --}}
                                                                {{-- {{$data->name}}</a> --}}
                                                    {{-- </h2>
                                                </td>
                                                <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__"
                                                        data-cfemail="a5c6cdc4d7c9c0d6cdc4c3cbc0d7e5c0ddc4c8d5c9c08bc6cac8">{{$data->email}}</a>
                                                </td>
                                                <td><span class="text-success">{{$data->role}}</span></td> --}}
                                                {{-- <td>{{$data->created_at}}</td> --}}

                                                {{-- <td><span class="badge badge-pill bg-success-light">Active</span></td>
                                                <td class="text-end">
                                                    <a href="/edit_user/{{ $data->id }}"
                                                        class="btn btn-sm btn-success bg-success-light text-success me-2"><i
                                                            class="far fa-edit me-1"></i> Edit </a>

                                                            <a href="/edit_user/{{ $data->id }}"
                                                                class="btn btn-sm btn-primary bg-primary-light text-primary me-2"><i
                                                                    class="far fa-eye me-1"></i> View </a>

                                                    <a href="/delete_user/{{ $data->id }}"
                                                        class="btn btn-sm btn-danger bg-danger-light text-danger me-2"><i
                                                            class="far fa-trash-alt me-1"></i> Delete </a>
                                                </td>
                                            </tr> --}}

                                            {{-- @endforeach --}}

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


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
@endsection
</html>
