@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Edit Customers</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-datetimepicker.min.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card ">

                            <div class="col-md-9">
                                <h4 class="card-title m-3">Edit Customers</h4>
                            </div>
                            <hr size="6" style="color: blue">

                            <div class="card-body">
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
                                        @if ($errors->any())
                                        <div class="alert alert-danger" role="alert">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif
                                        <div class="card card-table">
                                            <div class="card-body">


                                            <button type="button" class="btn btn-success mt-1" data-bs-toggle="modal" data-bs-target="#bs-example-modal-lg">
                                                <i class="fas fa-plus"></i>
                                                Add Customers
                                            </button>
                                            <br>

                                            <div class="modal fade" id="bs-example-modal-lg" tabindex="-1" role="dialog"
                                                    aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title m-2" id="myLargeModalLabel"> Add Customers </h4>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>

                                                            </div>
                                                            <div class="modal-body">

                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="card">
                                                                            <div class="card-body">

                                                                                <form action="{{route('addCustomer')}}" method="post">
                                                                                    @csrf
                                                                                    <div class="row">

                                                                                        <div class="row">
                                                                                                <div class="col-md-4">
                                                                                                    <label>Code :</label>
                                                                                                    <input type="text" name="code" class="form-control" placeholder="Customer Code" required>

                                                                                                </div>
                                                                                            <div class="col-md-4">
                                                                                                <label>Title :</label>
                                                                                                    <div class=" form-group">
                                                                                                        <select class="select form-control" name="title" aria-hidden="true" required>
                                                                                                            <option value="" >Please Select</option>
                                                                                                            <option value="Mr.">Mr.</option>
                                                                                                            <option value="Mrs.">Mrs.</option>
                                                                                                        </select>
                                                                                                    </div>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <label>Gender :</label>
                                                                                                    <div class=" form-group">
                                                                                                        <select class="select form-control" name="gender" aria-hidden="true" required>
                                                                                                            <option value="" >Please Select</option>
                                                                                                            <option value="Male">Male</option>
                                                                                                            <option value="Female">Female</option>
                                                                                                            <option value="Other">Other</option>
                                                                                                        </select>
                                                                                                    </div>
                                                                                            </div>
                                                                                       </div>

                                                                                       {{-- row for full name --}}
                                                                                       <div class="row"> <div class="form-group">
                                                                                            <label>Full name :</label>
                                                                                            <input type="text" name="name" class="form-control" placeholder="Full name" required>
                                                                                        </div>
                                                                                       </div>

                                                                                       {{-- row for Address --}}
                                                                                       <div class="row">
                                                                                        <div class="col-md-6">
                                                                                            <textarea class="form-control" name="address1" id="address1" rows="3" placeholder="Address 1" required></textarea>

                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <textarea class="form-control" name="address2" id="address2" rows="3" placeholder="Address 2"></textarea>

                                                                                        </div>
                                                                                       </div>

                                                                                               {{-- row for Contacts --}}
                                                                                               <div class="row mt-3">
                                                                                                <div class="col-md-6">
                                                                                                    <label>Contact 1 :</label>
                                                                                                    <input type="text" name="contact1" class="form-control" placeholder="Contact 1" required>

                                                                                                </div>
                                                                                                <div class="col-md-6">
                                                                                                    <label>Contact 2 :</label>
                                                                                                    <input type="text" name="contact2" class="form-control" placeholder="Contact 2">

                                                                                                </div>
                                                                                               </div>

                                                                                        {{-- row for Email --}}
                                                                                       <div class="row mt-4"> <div class="form-group">
                                                                                        <label>Email :</label>
                                                                                        <input type="text" name="email" class="form-control" placeholder="Email" required>
                                                                                            </div>
                                                                                        </div>



                                                                                               {{-- row for NIC and Driving Licence --}}
                                                                                               <div class="row mt-2">
                                                                                                <div class="col-md-6">
                                                                                                    <label>NIC :</label>
                                                                                                    <input type="text" name="nic" class="form-control" placeholder="NIC" required>
                                                                                                </div>
                                                                                                <div class="col-md-6">
                                                                                                    <label>Driving License :</label>
                                                                                                    <input type="text" name="driving_license" class="form-control" placeholder="Driving Licence">
                                                                                                </div>
                                                                                               </div>


                                                                                                {{-- row for PASSPORT and other --}}
                                                                                                <div class="row mt-4">
                                                                                                    <div class="col-md-6">
                                                                                                        <label>Passport :</label>
                                                                                                        <input type="text" name="passport" class="form-control" placeholder="Passport">
                                                                                                    </div>
                                                                                                    <div class="col-md-6">
                                                                                                        <label>Other Identifications :</label>
                                                                                                        <input type="text" name="other_identifications" class="form-control" placeholder="Other Identifications">
                                                                                                    </div>
                                                                                                   </div>

                                                                                            {{-- row for Blacklisted --}}
                                                                                            <div class="row mt-4">
                                                                                                <div class="col-md-6">
                                                                                                    <div class="row">
                                                                                                        <div class="col-md-1">
                                                                                                            <input type="checkbox" value="1" name="blacklisted" >
                                                                                                        </div>
                                                                                                        <div class="col-md-11">
                                                                                                            <label>Mark as Blacklisted</label>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>



                                                                                    <div class="text-center mt-4">
                                                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
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

                                        </div>
                                    </div>
                                </div>




                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <script> data-cfasync="false" src="../../../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js">
    </script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    {{-- <script src="assets/plugins/select2/js/select2.min.js"></script> --}}
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="assets/js/script.js"></script>

    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>






</body>
@endsection
</html>

