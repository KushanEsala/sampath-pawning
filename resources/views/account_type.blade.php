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
    <title>Account Type</title>
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="card shadow">
                    <div class="col-md-9">
                        <h4 class="card-title m-3">Account Type</h4>
                    </div>
                    <hr size="6" style="color: blue">
                    {{-- alert section --}}
                    <div class="row">
                        <div class="col-sm-12">
                            @if (session('delete'))
                            <div class="alert alert-danger text-center" role="alert">
                                {{ session('delete') }} &#10004;
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

                            @if (session('added'))
                            <div class="alert alert-success text-center" role="alert">
                                {{ session('added') }} &#10004;
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2"></div>

                        <div class="col-md-8">
                            {{-- Condition Form  --}}
                            <div class="card shadow p-3 mb-5 bg-body-tertiary rounded">
                                <div class="errMsgContainer"></div>
                                <form action="" id="addCategory" method="POST">
                                    @csrf
                                    <label for="">Code<span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                    <div class=" form-group">
                                    <input type="text" class="form-control" placeholder="code"
                                    id="code" name="code" required maxlength="80">
                                    </div>
                                    <label for="">Account Category <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                    <select class="select form-control" id="categoryName" name="categoryName"
                                    aria-hidden="true" required>
                                    <option value="">Please select</option>
                                    @foreach($Category as $categoryData)
                                    <option value="{{ $categoryData->category}}">
                                    {{ $categoryData->category}}</option>
                                    @endforeach
                                    </select>
                                     <br>
                                    <label for="">Description <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                    <div class=" form-group">
                                        <input type="text" class="form-control" placeholder="description"
                                        id="description" name="description" required maxlength="80">
                                    </div>

                                    <div class="col-md-3">
                                        <button class="btn btn-primary add_category" type="submit">Save</button>
                                    </div>
                                </form>
                                <br>

                                {{-- Receipt Table --}}
                                <div class="card">
                                    <table class="table table-hover ">
                                        <thead>
                                            <tr class="table-secondary">
                                                <th>Code</th>
                                                <th>Account Category</th>
                                                <th>Description</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($CategoryData as $data)
                                            <tr>
                                                <td>{{  $data['code'] }}</td>
                                                <td>{{  $data['category'] }}</td>
                                                <td>{{  $data['description'] }}</td>
                                                <td class="text-center">
                                                    <a href=""
                                                        class="btn btn-sm btn-success update_category_form bg-success-light text-success me-2"
                                                        data-bs-toggle="modal" data-bs-target="#updateCategoryModel"
                                                        data-id="{{$data->id}}"
                                                        data-code="{{$data['code']}}"
                                                        data-category="{{$data['category']}}"
                                                        data-description="{{$data['description']}}"
                                                        >
                                                        <i class="far fa-edit me-1"></i> Edit
                                                    </a>

                                                    <a href=""
                                                        class="btn btn-sm btn-danger delete_category bg-danger-light text-danger me-2"
                                                        data-id="{{$data->id}}">
                                                        <i class="far fa-trash-alt me-1"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ............Update Item condition................................. --}}
                            <div class="modal fade" id="updateCategoryModel" tabindex="-1" role="dialog"
                                aria-labelledby="updateCategoryModelLabel" aria-hidden="true">
                                <div class="modal-dialog ">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="errMsgContainer2"></div>
                                                            <form action="" method="post" id="updateItemCategory">
                                                                @csrf
                                                                <input type="hidden" id="up_id" name="up_id">
                                                                <label for="">Code<span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                <div class=" form-group">
                                                                    <input type="text" class="form-control" placeholder="code"
                                                                    id="up_code" name="up_code" required maxlength="80">
                                                                </div>

                                                                <label for="">Account Category <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                <div class=" form-group">
                                                                    <select class="select form-control" class="form-control"
                                                                        placeholder="Receipt Type Name"
                                                                        name="up_CategoryName" id="up_CategoryName" aria-hidden="true" required>
                                                                        <option value="">Please select</option>
                                                                        @foreach($Category as $categoryData)
                                                                        <option value="{{ $categoryData->category}}">
                                                                            {{ $categoryData->category}}</option>
                                                                        @endforeach
                                                                        </select>
                                                                        <br>
                                                                <label for="">Description <span style="color:#FF0000; font-weight: bold; ">*</span> :</label>
                                                                <div class=" form-group">
                                                                    <input type="text" class="form-control"
                                                                        placeholder=""
                                                                        name="up_description" id="up_description"
                                                                        required>
                                                                </div>

                                                                <div class="text-center mt-4">
                                                                    <button type="button"
                                                                        class="btn btn-success update_category bg-success-light text-success me-2">Update</button>
                                                                    <button type="button"
                                                                        class="btn btn-outline-secondary"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close">Close</button>
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

    {!! Toastr::message() !!}

   {{-- SCRF token --}}
   <script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

<script>
    $(document).ready(function () {
        //add new condition
        $(document).on('click', '.add_category', function (e) {
            e.preventDefault();
            let code = $('#code').val();
            let categoryName = $('#categoryName').val();
            let description = $('#description').val();

            $.ajax({
                url: "{{ route('add_Account_Type_ajax') }}",
                method: 'post',
                data: {
                    "_token": "{{ csrf_token() }}",
                    code: code,
                    categoryName: categoryName,
                    description: description,

                },
                success: function (res) {
                    if (res.status == 'success') {
                        $('#addCategory')[0].reset();
                        $('.table').load(location.href + ' .table');
                        Command: toastr["success"]("Account Type Added ...!", "Success")
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

        //show item details in update form
        $(document).on('click','.update_category_form',function(){
                let id = $(this).data('id');
                let code = $(this).data('code');
                let categoryName = $(this).data('category');
                let description = $(this).data('description');

                $('#up_id').val(id);
                $('#up_code').val(code);
                $('#up_CategoryName').val(categoryName);
                $('#up_description').val(description);
        });

        //update receipt details
        $(document).on('click','.update_category',function(e){
                        e.preventDefault();
                        let up_id = $('#up_id').val();
                        let up_code = $('#up_code').val();
                        let up_categoryName = $('#up_CategoryName').val();
                        let up_description = $('#up_description').val();

                        $.ajax({
                            url:"{{ route('update_Account_Type_ajax') }}",
                            method: 'post',
                            data:{"_token": "{{ csrf_token() }}",
                            up_id:up_id,
                            up_code:up_code,
                            up_categoryName:up_categoryName,
                            up_description:up_description,

                            },

                            success:function(res){
                                if(res.status=='success'){
                                    $("#updateCategoryModel").modal('hide');
                                    $('#updateItemCategory')[0].reset();
                                    $('.table').load(location.href+' .table');
                                    Command: toastr["success"]("Account Type Updated...", "Success")
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
                                $('.errMsgContaine2r').html('');
                                let error = err.responseJSON;
                                $.each(error.errors,function(index, value){
                                    $('.errMsgContainer2').append('<span class="text-danger">'+value+'<span>'+'<br>');

                                });
                            }
                        })
        })

        //delete item data
        $(document).on('click', '.delete_category', function (e) {
            e.preventDefault();
            let id = $(this).data('id');

            if (confirm('Are you sure to delete Account Type ?')) {
                $.ajax({
                    url: "{{ route('delete_Account_Type_ajax') }}",
                    method: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: id
                    },
                    success: function (res) {
                        if (res.status == 'success') {
                            $('.table').load(location.href + ' .table');
                            Command: toastr["success"]("Account Type deleted...", "Success")
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

    });
</script>


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

</body>
@endsection
</html>
