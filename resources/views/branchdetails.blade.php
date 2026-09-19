@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <title>Branches</title>
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                    <div class="card shadow ">

                        <div class="col-md-9">
                            <h4 class="card-title m-3">Branches</h4>
                        </div>
                        <hr size="6" style="color: blue">
                        <div class="row justify-content-md-center">
                                    {{-- error showing section --}}
                                    @if (session('delete'))
                                        <div class="alert alert-danger text-center" role="alert">
                                            {{ session('delete') }} &#10004;
                                        </div>
                                    @endif
                                    @if (session('added'))
                                    <div class="alert alert-success text-center" role="alert">
                                        {{ session('added') }} &#10004;
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

                                    {{-- --------------search and add branch button-------- --}}
                                        <div class="row ">
                                            {{-- ........search area.......... --}}
                                            <div class="col-md-6">
                                                <div class="top-nav-search">
                                                    <form>
                                                        <input type="text" name="search" id="search"
                                                            class="form-control" placeholder="Search here">
                                                        <button class="btn" type="button"><i
                                                                class="fas fa-search"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                            {{-- .............add branch button.......... --}}
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-success float-end m-2"
                                                    data-bs-toggle="modal" data-bs-target="#addBranchModel">
                                                    <i data-feather="plus"></i>
                                                    Add Branch
                                                </button>
                                            </div>
                                        </div>

                                    {{-- ------------- Table branch -------------- --}}
                                        <div class="card-body ">
                                            <div class="table-responsive ">
                                                <div class="table-data ">
                                                    <table class="table table-bordered table-center table-hover mt-3"
                                                        id="branchTable">
                                                        <thead>
                                                            <tr class="table-secondary">
                                                                <th>BC Code</th>
                                                                <th>Name</th>
                                                                <th>Address</th>
                                                                <th>Contact 1</th>
                                                                <th>Contact 2</th>
                                                                <th>Date</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($branchDel as $key=>$branch)
                                                            <tr>
                                                                <th>{{$branch->bccode }}</th>
                                                                <td>{{$branch->name }}</td>
                                                                <td>{{$branch->address}}</td>
                                                                <td>{{$branch->contact1}}</td>
                                                                <td>{{$branch->contact2}}</td>
                                                                <td>{{$branch->date}}</td>
                                                                <td>
                                                                    <a href=""
                                                                        class="btn btn-sm btn-success update_branch_form bg-success-light text-success me-2"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#updateBranchModel"
                                                                        data-id="{{$branch->id}}"
                                                                        data-bccode="{{$branch->bccode}}"
                                                                        data-name="{{$branch->name}}"
                                                                        data-address="{{$branch->address}}"
                                                                        data-contact1="{{$branch->contact1}}"
                                                                        data-contact2="{{$branch->contact2}}"
                                                                        data-date="{{$branch->date}}">
                                                                        <i class="far fa-edit me-1"></i> Edit
                                                                    </a>
                                                                    <a href=""
                                                                        class="btn btn-sm btn-danger delete_branch bg-danger-light text-danger me-2"
                                                                        data-id="{{$branch->id}}">
                                                                        <i class="far fa-trash-alt me-1"></i> Delete
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    <div class="ml-4 mb-3 mt-1">
                                                    {!! $branchDel->links() !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    {!! Toastr::message() !!}


     {{-- ------------- model for add branch -------------- --}}
    <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addBranchModelLabel"
        aria-hidden="true" id="addBranchModel">
        <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title" id="addBranchModelLabel">Add Branch </h4>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"
                     aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <div class="row">
                     <div class="col-md-12">
                         <div class="card">
                             <div class="card-body">
                                 <div class="errMsgContainer"></div>
                                 <form action="" id="addBranch" method="POST"
                                     enctype="multipart/form-data">
                                     @csrf
                                     <div class="mb-3">
                                         <label for="exampleFormControlInput1"
                                             class="form-label">
                                             BC Code </label>
                                         <input type="text" class="form-control" id="bccode"
                                             name="bccode" placeholder="Branch Code"
                                             value="{{ old('bccode') }}" required>
                                     </div>
                                     <div class="mb-3">
                                         <label for="exampleFormControlTextarea1"
                                             class="form-label">Name</label>
                                         <input type="text" class="form-control" id="name"
                                             name="name" placeholder="Branch Name"
                                             value="{{ old('name') }}" required>
                                     </div>
                                     <div class="mb-3">
                                         <label for="exampleFormControlTextarea1"
                                             class="form-label">Address</label>
                                         <input type="text" class="form-control" id="address"
                                             name="address" placeholder="Branch Address"
                                             value="{{ old('address') }}" required>
                                     </div>
                                     <label for="exampleFormControlInput1"
                                         class="form-label">
                                         Contact
                                     </label>
                                     <div class="row mb-3">
                                         <div class="col-md-6"><input type="text"
                                                 class="form-control" id="contact1"
                                                 name="contact1" placeholder="Contact 1"
                                                 value="{{ old('contact1') }}" required>
                                         </div>
                                         <div class="col-md-6"><input type="text"
                                                 class="form-control" id="contact2"
                                                 name="contact2" placeholder="Contact 2"
                                                 value="{{ old('contact2') }}"></div>
                                     </div>
                                     <div class="mb-3">
                                         <label for="exampleFormControlInput1"
                                             class="form-label">
                                             Date Of Joined
                                         </label>
                                         <input type="date" class="form-control" id="date"
                                             name="date" placeholder=""
                                             value="{{ old('date') }}">
                                     </div>
                             </div>
                         </div>
                     </div>
                     <div class="text-center mt-4">
                         <button class="btn btn-success add_branch"
                             type="button">Save</button>
                         <input class="btn btn-outline-warning" type="reset" value="Reset">
                         <button type="button" id="closeModel"
                             class="btn btn-outline-secondary" data-bs-dismiss="modal"
                             aria-label="Close">Close</button>
                     </div>
                     </form>
                 </div>
             </div>
         </div>
     </div>
    </div>

 {{-- ------------- model for update branch -------------- --}}
 <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="updateBranchModelLabel"
     aria-hidden="true" id="updateBranchModel">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title" id="updateBranchModelLabel">Update Branch </h4>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"
                     aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <div class="row">
                     <div class="col-md-12">
                         <div class="card">
                             <div class="card-body">
                                 <div class="errMsgContainer2"></div>
                                 <form action="" id="updateBranch" method="POST">
                                     @csrf
                                     <input type="hidden" id="up_id" name="up_id">
                                     <div class="mb-3">
                                         <label for="exampleFormControlInput1"
                                             class="form-label">
                                             BC Code </label>
                                         <input type="text" class="form-control"
                                             id="up_bccode" name="up_bccode"
                                             placeholder="Branch Code" required>
                                     </div>
                                     <div class="mb-3">
                                         <label for="exampleFormControlTextarea1"
                                             class="form-label">Name</label>
                                         <input type="text" class="form-control" id="up_name"
                                             name="up_name" placeholder="Branch Name"
                                             required>
                                     </div>
                                     <div class="mb-3">
                                         <label for="exampleFormControlTextarea1"
                                             class="form-label">Address</label>
                                         <input type="text" class="form-control"
                                             id="up_address" name="up_address"
                                             placeholder="Branch Address" required>
                                     </div>
                                     <label for="exampleFormControlInput1"
                                         class="form-label">Contact</label>
                                     <div class="row mb-3">
                                         <div class="col-md-6">
                                             <input type="text" class="form-control"
                                                 id="up_contact1" name="up_contact1"
                                                 placeholder="Contact 1" required>
                                         </div>
                                         <div class="col-md-6">
                                             <input type="text" class="form-control"
                                                 id="up_contact2" name="up_contact2"
                                                 placeholder="Contact 2">
                                         </div>
                                     </div>
                                     <div class="mb-3">
                                         <label for="exampleFormControlInput1"
                                             class="form-label">
                                             Date Of Joined
                                         </label>
                                         <input type="date" class="form-control" id="up_date"
                                             name="up_date">
                                     </div>
                             </div>
                         </div>
                     </div>
                     <div class="text-center mt-4">
                         <button class="btn btn-success update_branch"
                             type="button">Update</button>
                         <input class="btn btn-outline-warning" type="reset" value="Reset">
                         <button type="button" id="closeModel"
                             class="btn btn-outline-secondary" data-bs-dismiss="modal"
                             aria-label="Close">Close</button>
                     </div>
                     </form>
                 </div>
             </div>
         </div>
     </div>
 </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>

    {{-- CSRF token script --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            //add new branch
            $(document).on('click', '.add_branch', function (e) {
                e.preventDefault();
                let bccode = $('#bccode').val();
                let name = $('#name').val();
                let address = $('#address').val();
                let contact1 = $('#contact1').val();
                let contact2 = $('#contact2').val();
                let date = $('#date').val();
                $.ajax({
                    url: "{{ route('add_branch_ajax') }}",
                    method: 'post',
                    data: {
                        bccode: bccode,
                        name: name,
                        address: address,
                        contact1: contact1,
                        contact2: contact2,
                        date: date
                    },
                    success: function (res) {
                        if (res.status == 'success') {
                            $("#addBranchModel").modal('hide');
                            $('#addBranch')[0].reset();
                            $('.table').load(location.href + ' .table');
                            Command: toastr["success"]("Branch Added ...!", "Success")
                            toastr.options = {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": false,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "5000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            }
                        }
                    },
                    error: function (err) {
                        $('.errMsgContainer').html('');
                        let error = err.responseJSON;
                        $.each(error.errors, function (index, value) {
                            $('.errMsgContainer').append(
                                '<span class="text-danger">' + value +
                                '<span>' + '<br>');
                        });
                    }
                })
            })

            //show branch details in update form
            $(document).on('click', '.update_branch_form', function () {
                let id = $(this).data('id');
                let bccode = $(this).data('bccode');
                let name = $(this).data('name');
                let address = $(this).data('address');
                let contact1 = $(this).data('contact1');
                let contact2 = $(this).data('contact2');
                let date = $(this).data('date');

                $('#up_id').val(id);
                $('#up_bccode').val(bccode);
                $('#up_name').val(name);
                $('#up_address').val(address);
                $('#up_contact1').val(contact1);
                $('#up_contact2').val(contact2);
                $('#up_date').val(date);
            });

            //update branch data
            $(document).on('click', '.update_branch', function (e) {
                e.preventDefault();
                let up_id = $('#up_id').val();
                let up_bccode = $('#up_bccode').val();
                let up_name = $('#up_name').val();
                let up_address = $('#up_address').val();
                let up_contact1 = $('#up_contact1').val();
                let up_contact2 = $('#up_contact2').val();
                let up_date = $('#up_date').val();
                $.ajax({
                    url: "{{ route('update_branch_ajax') }}",
                    method: 'post',
                    data: {
                        up_id: up_id,
                        up_bccode: up_bccode,
                        up_name: up_name,
                        up_address: up_address,
                        up_contact1: up_contact1,
                        up_contact2: up_contact2,
                        up_date: up_date
                    },
                    success: function (res) {
                        if (res.status == 'success') {
                            $("#updateBranchModel").modal('hide');
                            $('#updateBranch')[0].reset();
                            $('.table').load(location.href + ' .table');
                            Command: toastr["success"]("Branch datails updated...",
                                "Success")
                            toastr.options = {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": false,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "5000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            }
                        }
                    },
                    error: function (err) {
                        $('.errMsgContaine2r').html('');
                        let error = err.responseJSON;
                        $.each(error.errors, function (index, value) {
                            $('.errMsgContainer2').append(
                                '<span class="text-danger">' + value +
                                '<span>' + '<br>');

                        });
                    }
                })
            })

            //delete branch data
            $(document).on('click', '.delete_branch', function (e) {
                e.preventDefault();
                let branch_id = $(this).data('id');

                if (confirm('Are you sure to delete branch ?')) {
                    $.ajax({
                        url: "{{ route('delete_branch_ajax') }}",
                        method: 'post',
                        data: {
                            branch_id: branch_id
                        },
                        success: function (res) {
                            if (res.status == 'success') {
                                $('.table').load(location.href + ' .table');
                                Command: toastr["success"]("Branch deleted...", "Success")
                                toastr.options = {
                                    "closeButton": true,
                                    "debug": false,
                                    "newestOnTop": false,
                                    "progressBar": true,
                                    "positionClass": "toast-top-right",
                                    "preventDuplicates": false,
                                    "onclick": null,
                                    "showDuration": "300",
                                    "hideDuration": "1000",
                                    "timeOut": "5000",
                                    "extendedTimeOut": "1000",
                                    "showEasing": "swing",
                                    "hideEasing": "linear",
                                    "showMethod": "fadeIn",
                                    "hideMethod": "fadeOut"
                                }
                            }
                        }
                    });
                }
            })

            // pagination
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                let page = $(this).attr('href').split('page=')[1]
                branchdetails(page)
            })

            function branchdetails(page) {
                $.ajax({
                    url: "/branch_pagination?page=" + page,
                    success: function (res) {
                        $('.table-data').html(res);
                    }
                })
            }

            // search branch data
            $(document).on('keyup', function (e) {
                e.preventDefault();
                let search_string = $('#search').val();
                // console.log(search_string);
                $.ajax({
                    url: "{{ route('search_branch_ajax') }}",
                    method: 'GET',
                    data: {
                        search_string: search_string
                    },
                    success: function (res) {
                        $('.table-data').html(res);
                        if (res.status == 'not_found') {
                            $('.table-data').html('<span class="text-danger">' +
                                'Nothing found...' + '</span>');
                        }
                    }
                });
            })
        });
    </script>
</body>
@endsection
</html>
