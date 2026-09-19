@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <title>Users</title>
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="card shadow">
                    <div class="col-md-9">
                        <h4 class="card-title m-3">All Users</h4>
                    </div>
                    <hr size="6" style="color: blue">
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

                            <div class="card-header">
                                <div class="row">
                                    <div class="col-sm-10">

                                    </div>

                                  @if (Auth::check() && (Auth::user()->role == 'Admin' 
                                     ||Auth::user()->role == 'Head_Officer'
                                      ||Auth::user()->role == 'Manager'))
                                        <div class="col-sm-2">
                                            <a href=" {{route('add_user')}} " class="btn btn-success text-center">
                                                <i data-feather="plus"></i>
                                                Add User</a>
                                        </div>
                                    @endif

                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">

                                    <table class="table table-stripped table-center table-hover datatable">
                                        <thead class="thead-light">
                                            <tr>
                                                {{-- <th>User Id</th> --}}
                                                <th>User Name</th>
                                                <th>Name</th>
                                                <th>Role</th>
                                                <th>Email</th>
                                                <th>Branch</th>
                                                <th>Branch Code</th>
                                                {{-- <th>Status</th> --}}
                                                {{-- <th>Registered On</th> --}}
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($users as $data )
                                            <tr>
                                                {{-- <td>{{$data->id}}</td> --}}
                                                <td>{{$data->username}}</td>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="profile.html">
                                                            {{-- <img class="avatar avatar-sm me-2 avatar-img rounded-circle"
                                                                src="assets/img/profiles/avatar-01.jpg"
                                                                alt="User Image">  --}}
                                                            {{$data->name}}</a>
                                                    </h2>
                                                </td>
                                                <td><span class="">{{$data->role}}</span></td>
                                                <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__"
                                                        data-cfemail="a5c6cdc4d7c9c0d6cdc4c3cbc0d7e5c0ddc4c8d5c9c08bc6cac8">{{$data->email}}</a>
                                                </td>
                                                <td><span class="">{{$data->Branch}}</span></td>
                                                <td><span class="">{{$data->BC}}</span></td>

                                                {{-- <td>{{$data->created_at}}</td> --}}

                                                {{-- <td><span class="badge badge-pill bg-success-light">Active</span></td> --}}
                                                <td>
                                                    
                                                    <a href=""
                                                        class="btn btn-sm btn-success update_user_form bg-success-light text-success me-2"
                                                        data-bs-toggle="modal" data-bs-target="#updateUserModel"
                                                        data-id="{{$data->id}}" data-username="{{$data->username}}"
                                                        data-name="{{$data->name}}" data-role="{{$data->role}}"
                                                        data-email="{{$data->email}}" data-branch="{{$data->Branch}}" data-bc="{{$data->BC}}">
                                                        <i class="far fa-edit me-1"></i> Edit
                                                    </a>

                                                    <a href="#"
                                                        class="btn btn-sm btn-primary bg-primary-light text-primary me-2">
                                                        <i class="far fa-eye me-1"></i> View
                                                    </a>

                                                    <!--@if (Auth::check() && (Auth::user()->username == 'Admin' ||Auth::user()->username == 'developer'))-->
                                                        <button type="button"
                                                            class="btn btn-sm delete_user btn-danger bg-danger-light text-danger me-2"
                                                            data-id="{{$data->id}}">
                                                            <i class="far fa-trash-alt me-1"></i> Delete
                                                        </button>
                                                    <!--@endif-->

                                                </td>
                                            </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                    
                                </div>
                            </div>
                        </div>

                        {{-- ............Update user model................................. --}}
                        <div class="modal fade" id="updateUserModel" tabindex="-1" role="dialog"
                            aria-labelledby="updateUserModelLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title m-2" id="updateUserModelLabel"> Edit User </h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="errMsgContainer2"></div>
                                                        <form action="" method="post" id="updateUser">
                                                            @csrf
                                                            <div class="row">

                                                                {{-- row for full name --}}
                                                                <div class="row">
                                                                    <div class="form-group">
                                                                        <div class="row">
                                                                            <input type="hidden" id="up_id" name="up_id">
                                                                            <div class="col">
                                                                                <label>User Name :</label>
                                                                                <input type="text" name="up_user_name"
                                                                                    id="up_user_name"
                                                                                    class="form-control"
                                                                                    placeholder="First Name" required>
                                                                            </div>
                                                                            <div class="col">
                                                                                <label> Name :</label>
                                                                                <input type="text" name="up_name"
                                                                                    id="up_name" class="form-control"
                                                                                    placeholder="Middle Name" required>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="form-group">
                                                                        <div class="row">
                                                                            <div class="col">
                                                                                <label>Email :</label>
                                                                                <input type="text" name="up_email"
                                                                                    id="up_email" class="form-control"
                                                                                    placeholder="First Name" required>
                                                                            </div>
                                                                            <div class="col">
                                                                                <label>Role :</label>
                                                                                <input type="text" name="up_role"
                                                                                    id="up_role" class="form-control"
                                                                                    placeholder="Last Name" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                    <label>Branch :<span style="color:#FF0000; font-weight: bold; ">*</span></label>
                                                                    <div class="form-group">
                                                                        <select class="select form-control" name="up_Branch" id="up_Branch"
                                                                                aria-hidden="true" disabled>
                                                                                 <option value="">Please Select</option>
                                                                                 @foreach($Branch as $BranchData)
                                                                                 <option value="{{ $BranchData->name}}">
                                                                                {{ $BranchData->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <select hidden name="up_BC" id="up_BC"
                                                                                aria-hidden="true">
                                                                                 <option value="">Please Select</option>
                                                                                 @foreach($Branch as $BranchData)
                                                                                 <option value="{{ $BranchData->bccode}}">
                                                                                {{ $BranchData->bccode }}
                                                                             </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                </div>
                                          
                                                    <!--@if (Auth::check() && (Auth::user()->role == 'admin' ||Auth::user()->username == 'developer'
                                                    ||Auth::user()->role == 'Head_Officer'
                                                    ||Auth::user()->role == 'Manager'))-->
                                                                <div class="text-center mt-4">
                                                                    <button type="button"
                                                                        class="btn btn-success update_user bg-success-light text-success me-2">Update</button>
                                                                    <button type="button"
                                                                        class="btn btn-outline-secondary"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close">Close</button>
                                                                </div>
                                                                   <!--@endif-->
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

    {!! Toastr::message() !!}

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

<script>
    $(document).ready(function(){

        //delete customer data
        $(document).on('click','.delete_user',function(e){
                    e.preventDefault();
                    let user_id = $(this).data('id');

                    if(confirm('Are you sure to delete user ?')){
                        $.ajax({
                            url:"{{ route('delete_user_ajax') }}",
                            method: 'post',
                            data:{"_token": "{{ csrf_token() }}",user_id:user_id},
                            success:function(res){
                                if(res.status=='success'){
                                    $('.table').load(location.href+' .table');
                                    Command: toastr["success"]("User deleted...", "Success")
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

         //show customer details in update form
        $(document).on('click','.update_user_form',function(){
            let id = $(this).data('id');
            let name = $(this).data('name');
            let user_name = $(this).data('username');
            let role = $(this).data('role');
            let email = $(this).data('email');
            let branch = $(this).data('branch');
            let bc = $(this).data('bc');

            $('#up_id').val(id);
            $('#up_user_name').val(user_name);
            $('#up_name').val(name);
            $('#up_role').val(role);
            $('#up_email').val(email);
            $('#up_Branch').val(branch);
            $('#up_BC').val(bc);

        });

         //update customer details
        $(document).on('click','.update_user',function(e){
                    e.preventDefault();
                    let up_id = $('#up_id').val();
                    let up_name = $('#up_name').val();
                    let up_user_name = $('#up_user_name').val();
                    let up_role = $('#up_role').val();
                    let up_email = $('#up_email').val();
                    let up_Branch = $('#up_Branch').val();
                    let up_BC = $('#up_BC').val();


                    $.ajax({
                        url:"{{ route('update_user_ajax') }}",
                        method: 'post',
                        data:{"_token": "{{ csrf_token() }}",
                        up_id:up_id,
                        up_name:up_name,
                        up_user_name:up_user_name,
                        up_role:up_role,
                        up_email:up_email,
                        up_Branch:up_Branch,
                        up_BC:up_BC,
                       },

                        success:function(res){
                            if(res.status=='success'){
                                $("#updateUserModel").modal('hide');
                                $('#updateUser')[0].reset();
                                $('.table').load(location.href+' .table');
                                Command: toastr["success"]("User Datails Updated...", "Success")
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
                        },error:function(err){
                            $('.errMsgContainer2').html('');
                            let error = err.responseJSON;
                            $.each(error.errors,function(index, value){
                                $('.errMsgContainer2').append('<span class="text-danger">'+value+'<span>'+'<br>');

                            });
                        }
                    })
        })

        // pagination
        $(document).on('click','.pagination a', function(e){
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1]
            customerdetails(page)
        })
        function customerdetails(page){
            $.ajax({
                url:"/customer_pagination?page="+page,
                success:function(res){
                    $('.table-data').html(res);
                }
            })
        }

        // search customer data
        $(document).on('keyup',function(e){
                    e.preventDefault();
                    let search_string =  $('#search').val();
                    // console.log(search_string);
                    $.ajax({
                        url:"{{ route('search_user_ajax') }}",
                        method:'GET',
                        data:{search_string:search_string},
                        success: function(res) {
                            $('.table-data').html(res);
                            if(res.status=='not_found'){
                                $('.table-data').html('<span class="text-danger">'+'Nothing found...'+'</span>');
                            }
                        }
                    });
        })

    });
</script>

<script>
    $(document).ready(function () {
        // Listen for changes in the Weight and QTY fields
        $('#up_Branch').on('change', function () {
            let category = $('#up_Branch').val();
            $.ajax({
                        url: "{{ route('show_select_up_user_ajax') }}",
                        method: 'GET',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            category: category
                        },
                        success: function (res) {
                            if (res.status == 'success') {

                                var itemsCodeSelected = $('#up_BC');
                                // var itemDescriptionSelected = $('#item_description');
                                // var itemUnit_price = $('#unit_price');
                                // Change this to match your items select element
                                // Clear existing options
                                itemsCodeSelected.empty();
                                // itemDescriptionSelected.empty();
                                // itemUnit_price.empty();

                                $.each(res.data, function (index, item) {
                                    itemsCodeSelected.append($('<option>', {
                                        value: item.bccode,
                                        text: item.bccode
                                    }));
                                });

                                // // Add a default option for item description
                                // itemDescriptionSelected.append($('<option>', {
                                //     value: '',
                                //     text: 'Select an item'
                                // }));

                                // $.each(res.data, function (index, item) {
                                //     itemDescriptionSelected.append($('<option>', {
                                //         value: item.Item_description,
                                //         text: item.Item_description
                                //     }));
                                // });

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
                    });
        });
    });
</script>



    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
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