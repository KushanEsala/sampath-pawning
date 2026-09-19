@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Karatage Rate Setup</title>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="card shadow">
                    <div class="col-md-9">
                        <h4 class="card-title m-3">Karatage Rate Setup </h4>
                    </div>
                    <hr size="6" style="color: blue">

                    {{-- error section --}}
                    <div class="row">
                        <div class="col-sm-12">
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
                        </div>
                    </div>

                    {{-- Receipt Form  --}}
                    <div class="row">
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-8">
                            {{-- add new karatage form --}}
                            <div class="card shadow p-3 mb-3 bg-body-tertiary rounded">
                                <div class="errMsgContainer"></div>
                                <form action="" method="POST" id="addKaratage">
                                    @csrf
                                    <label for="Description">Description <span style="color:#FF0000; font-weight: bold; ">*</span> :

                                    </label>
                                    <div class=" form-group">
                                        <input type="text" class="form-control" placeholder="" id="descrption"
                                            name="descrption" maxlength="100">
                                    </div>
                                    <div class="col-md-3">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                        </div>
                                        <label for="">Pawninng Rate <span style="color:#FF0000; font-weight: bold; ">*</span> :
                                        </label>
                                        <div class=" form-group">
                                            <input type="text" class="form-control" placeholder="" id="pawningrate"
                                                name="pawningrate" maxlength="15">
                                        </div>
                                        <label for="">Assess Rate <span style="color:#FF0000; font-weight: bold; ">*</span> :
                                        </label>
                                        <div class=" form-group">
                                            <input type="text" class="form-control" placeholder="" id="assesrate"
                                                name="assesrate" maxlength="15">
                                        </div>
                                        <label for="">Market Rate <span style="color:#FF0000; font-weight: bold; ">*</span> :
                                        </label>
                                        <div class=" form-group">
                                            <input type="text" class="form-control" placeholder="" id="marketrate"
                                                name="marketrate" maxlength="15">
                                        </div>
                                             <label for="">Sales Rate Rate <span style="color:#FF0000; font-weight: bold; ">*</span> :
                                        </label>
                                        <div class=" form-group">
                                            <input type="text" class="form-control" placeholder="" id="salepriceRate"
                                                name="salepriceRate" maxlength="15">
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-primary add_karatage" type="button">Save</button>
                                        </div>
                                </form>
                                <br>
                            </div>

                        {{-- Receipt Table --}}
                        <div class="card shadow ">
                            <div class="table">
                                <table class="table table-center table-hover ">
                                    <thead>
                                        <tr class="table-secondary">
                                            <th>Description</th>
                                            <th>Pawning Rate</th>
                                            <th>Access Rate</th>
                                            <th>Market Rate</th>
                                            <th>Sales Rate</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($karatageData as $key=>$karatage)
                                        <tr>
                                            <td>{{$karatage->descrption }}</td>
                                            <td>{{$karatage->pawningrate }}</td>
                                            <td>{{$karatage->assesrate}}</td>
                                            <td>{{$karatage->marketrate}}</td>
                                            <td>{{$karatage->salepriceRate}}</td>
                                            <td>
                                                <a href=""
                                                    class="btn btn-sm btn-success update_karatage_form bg-success-light text-success me-2"
                                                    data-bs-toggle="modal" data-bs-target="#updateKaratageModel"
                                                    data-id="{{$karatage->id}}"
                                                     data-descrption="{{$karatage->descrption}}"
                                                    data-pawningrate="{{$karatage->pawningrate}}"
                                                    data-assesrate="{{$karatage->assesrate}}"
                                                    data-marketrate="{{$karatage->marketrate}}"
                                                    data-salepricerate="{{$karatage->salepriceRate}}"
                                                    >
                                                    <i class="far fa-edit me-1"></i> Edit
                                                </a>
                                                <a href=""
                                                    class="btn btn-sm btn-danger delete_karatage bg-danger-light text-danger me-2"
                                                    data-id="{{$karatage->id}}">
                                                    <i class="far fa-trash-alt me-1"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                            <div class="m-1">
                                {!! $karatageData->links() !!}
                            </div>
                        </div>

                        </div>
                    </div>



                    {{-- ............Update karatage model................................. --}}
                    <div class="modal fade" id="updateKaratageModel" tabindex="-1" role="dialog"
                        aria-labelledby="updateKaratageModelLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title m-2" id="updateKaratageModelLabel"> Edit Karatage </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="errMsgContainer2"></div>
                                                    <form method="post" id="updateKaratage">
                                                        @csrf
                                                        <div class="row">

                                                            <label for="">Description</label>
                                                            <input type="hidden" id="up_id" name="up_id">
                                                            <div class=" form-group">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Karatage Description"
                                                                    name="up_descrption" id="up_descrption" required />
                                                            </div>

                                                            <label for="">Pawning Rate</label>
                                                            <div class=" form-group">
                                                                <input type="text" class="form-control"
                                                                    placeholder="First Months interest rate here"
                                                                    id="up_pawningrate" name="up_pawningrate"
                                                                    required />
                                                            </div>
                                                            <label for="">Access Rate</label>
                                                            <div class=" form-group">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Other Months interest rate here"
                                                                    id="up_assesrate" name="up_assesrate" required />
                                                            </div>
                                                            <label for="">Market Rate</label>
                                                            <div class=" form-group">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Market Rate" id="up_marketrate"
                                                                    name="up_marketrate" required />
                                                            </div>
                                                                 <label for="">Sales Rate Rate <span style="color:#FF0000; font-weight: bold; ">*</span> :
                                                                </label>
                                                                <div class=" form-group">
                                                                    <input type="text" class="form-control" placeholder="" id="up_salepriceRate"
                                                                        name="up_salepriceRate" maxlength="15">
                                                                </div>
                                                        </div>
                                                        <div class="text-center mt-4">
                                                            <button type="button"
                                                                class="btn btn-success update_karatage bg-success-light text-success me-2">Update</button>
                                                            <button type="button" class="btn btn-outline-secondary"
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
    {!! Toastr::message() !!}

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            //add new karatage
            $(document).on('click', '.add_karatage', function (e) {
                e.preventDefault();
                let descrption = $('#descrption').val();
                let pawningrate = $('#pawningrate').val();
                let assesrate = $('#assesrate').val();
                let marketrate = $('#marketrate').val();
                let salepriceRate = $('#salepriceRate').val();

                $.ajax({
                    url: "{{ route('add_karatage_ajax') }}",
                    method: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        descrption: descrption,
                        pawningrate: pawningrate,
                        assesrate: assesrate,
                        marketrate: marketrate,
                        salepriceRate,salepriceRate
                    },
                    success: function (res) {
                        if (res.status == 'success') {
                            $('#addKaratage')[0].reset();
                            $('.table').load(location.href + ' .table');
                            Command: toastr["success"]("Karatage Added ...!", "Success")
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

            //delete karatage data
            $(document).on('click', '.delete_karatage', function (e) {
                e.preventDefault();
                let karatage_id = $(this).data('id');

                if (confirm('Are you sure to delete receipt ?')) {
                    $.ajax({
                        url: "{{ route('delete_karatage_ajax') }}",
                        method: 'post',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            karatage_id: karatage_id
                        },
                        success: function (res) {
                            if (res.status == 'success') {
                                $('.table').load(location.href + ' .table');
                                Command: toastr["success"]("Karatage deleted...", "Success")
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

            //show karatage details in update form
            $(document).on('click', '.update_karatage_form', function () {
                let id = $(this).data('id');
                let descrption = $(this).data('descrption');
                let pawningrate = $(this).data('pawningrate');
                let assesrate = $(this).data('assesrate');
                let marketrate = $(this).data('marketrate');
                let salepriceRate = $(this).data('salepricerate'); // NOT salepriceRate


                $('#up_id').val(id);
                $('#up_descrption').val(descrption);
                $('#up_pawningrate').val(pawningrate);
                $('#up_assesrate').val(assesrate);
                $('#up_marketrate').val(marketrate);
                $('#up_salepriceRate').val(salepriceRate);

            });

            //update karatage details
            $(document).on('click', '.update_karatage', function (e) {
                e.preventDefault();
                let up_id = $('#up_id').val();
                let up_descrption = $('#up_descrption').val();
                let up_pawningrate = $('#up_pawningrate').val();
                let up_assesrate = $('#up_assesrate').val();
                let up_marketrate = $('#up_marketrate').val();
                let up_salepriceRate = $('#up_salepriceRate').val();

                $.ajax({
                    url: "{{ route('update_karatage_ajax') }}",
                    method: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        up_id: up_id,
                        up_descrption: up_descrption,
                        up_pawningrate: up_pawningrate,
                        up_assesrate: up_assesrate,
                        up_marketrate: up_marketrate,
                        up_salepriceRate:up_salepriceRate
                    },

                    success: function (res) {
                        if (res.status == 'success') {
                            $("#updateKaratageModel").modal('hide');
                            $('#updateKaratage')[0].reset();
                            $('.table').load(location.href + ' .table');
                            Command: toastr["success"]("Karatage Datails Updated...",
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