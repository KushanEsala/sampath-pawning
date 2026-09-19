@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <title>Chart Of Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
</head>
<style>
    p {
      font-weight: bold;
      font-family: "Noto Sans, sans-serif";
      size:"7"; 
      color: rgb(3, 80, 3)
    }

    h1{
      font-family: "Times New Roman", Times, serif;
      size:"6"; 
      color: rgb(4, 58, 13)
    }

    input::placeholder {
      font-weight: bold;
      opacity: 0.5;
      color: rgb(4, 58, 13)
    }

    input[type="text"]{
      background-color: rgb(206, 235, 219);
      padding: 10px 15px;
      border-radius: 3px;
    }

    hr{
      color: rgb(3, 31, 3)
    }
</style>


<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">

                <div class="row">
                    <div class="col-sm-12">
                        <div class="card shadow ">

                            <div class="col-md-9">
                                <h4 class="card-title m-3">Chart Of Account</h4>
                            </div>
                            <hr size="6" style="color: blue">

                            <div class="container mt-2">
                                <div class="row">
                                    <div class="col-lg-12 margin-tb">
                                        <div class="pull-left">

                                        </div>
                                        <div class="pull-right mb-2">
                                            <a class="btn btn-info card-body shadow p-3 mb-2" onClick="add()"
                                                href="javascript:void(0)">Add Chart Of Account</a>
                                        </div>
                                    </div>
                                </div>
                                @if ($message = Session::get('success'))
                                <div class="alert alert-success">
                                    <p>{{ $message }}</p>
                                </div>
                                @endif

                                <div class="card-body shadow p-3 mb-5 bg-body-tertiary rounded">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="MChartofAccount"
                                        >
                                            <thead>
                                                <tr style="background-color:hsl(204, 71%, 70%);">
                                                    <th>Account Type</th>
                                                    <th>Account Sub</th>
                                                    <th>Code</th>
                                                    <th>Description</th>
                                                    <th>Opening Balance</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
        </div>
    </div>


                <!-- add MChartofAccount model -->
                    <div class="modal fade" id="Item-modal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Chart Of Account</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                          
                                <div class="modal-body">
                                        <form action="javascript:void(0)" id="ItemForm" name="ItemForm"
                                            class="form-horizontal" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="id" id="id">
                                            <div class="row">
                                              <div class="col-sm-6">
                                                <label for="name" class="col-sm-6 control-label"> Account Type
                                                    {{-- <span style="color:#FF0000; font-weight: bold; ">*</span> --}}
                                                </label>
                                                <select class="select form-control" name="account" id="account"
                                                    aria-hidden="true">
                                                    <option value="">Please Select</option>
                                                    @foreach($AccountTypeData as $categoryData)
                                                    <option value="{{ $categoryData->description}}">
                                                        {{ $categoryData->description }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-sm-6">
                                                <label for="name" class="col-sm-6 control-label"> Account Sub Type
                                                    {{-- <span style="color:#FF0000; font-weight: bold; ">*</span> --}}
                                                </label>
                                                {{-- <input type="text" class="form-control" id="accountsub" 
                                                value="{{ $maxCustomer+1}}"
                                                 name="accountsub" placeholder="Code" maxlength="15" required=""> --}}
                                                <select class="select form-control" name="accountsub" id="accountsub"
                                                    aria-hidden="true">
                                                    <option value="">Please Select</option>
                                                    @foreach($MainCategoryData as $categoryData)
                                                    <option value="{{ $categoryData->category}}">
                                                        {{ $categoryData->category }}</option>
                                                    @endforeach
                                                </select> 
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col 6">  
                                                <div class="form-group">
                                                 <label for="name" class="col-sm-4 control-label">Code
                                                    {{-- <span style="color:#FF0000; font-weight: bold; ">*</span> --}}
                                                </label>
                                                  <div class="col-sm-12">
                                                  <input type="text" class="form-control" id="code" 
                                                  {{-- value="{{ $maxCustomer+1}}" --}}
                                                   name="code" placeholder="Code" maxlength="15" required="">
                                                   <span class="text-danger" id="image-input-error"></span>
                                              </div>
                                          </div>
                                         </div>
                                         <div class="col 6">  
                                            <div class="form-group">
                                             <label for="name" class="col-sm-4 control-label">Description
                                                {{-- <span style="color:#FF0000; font-weight: bold; ">*</span> --}}
                                            </label>
                                              <div class="col-sm-12">
                                              <input type="text" class="form-control" id="description" 
                                              {{-- value="{{ $maxCustomer+1}}" --}}
                                               name="description" placeholder="Description" maxlength="15" required="">
                                               <span class="text-danger" id="image-input-error"></span>
                                          </div>
                                      </div>
                                     </div>
                                   </div>
                                   <br>
                                   <div class="row">
                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">Opening Balance
                                            {{-- <span style="color:#FF0000; font-weight: bold; ">*</span> --}}
                                        </label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" id="opening_balance"
                                                name="opening_balance" placeholder="Opening Balance" maxlength="25"
                                                required="">
                                        </div>
                                    </div>
                              </div> 
                                <div class="row">
                                    <div class="col-sm-6">
                                      <div class="card">
                                        <div class="card-body">
                                            <h6>Options
                                                {{-- <span style="color:#FF0000; font-weight: bold; ">*</span> --}}
                                            </h6>
                                            <hr>
                                                <div class="col 6">
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="defaultCheck1">
                                                          Control Account
                                                        </label>
                                                        <input class="form-check-input" type="checkbox" value="1" id="controlaccount" name="controlaccount">
                                                      </div> 
                                                </div>
                                                <div class="col 6">
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="defaultCheck1">
                                                            Bank Account
                                                        </label>
                                                        <input class="form-check-input" type="checkbox" value="1" id="bankaccount" name="bankaccount">
                                                      </div> 
                                                </div>
                                            </div>
                                      </div>
                                    </div>
                                  </div>          
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <input type="hidden" class="form-control" id="OC"
                                                    name="OC" placeholder=""
                                                    value="{{ Auth::user()->username}}" readonly>                                   
                                                </div>
           
                                                <div class="col-md-6">
                                                   <input type="hidden" class="form-control" id="BC"
                                                   name="BC" placeholder=""
                                                   value="{{ Auth::user()->BC}}" readonly>
                                               </div>
                                           </div>

                                            <div class="col-sm-offset-2 col-sm-10 text-center"><br />
                                                <button type="submit" class="btn btn-info" id="btn-save">Save
                                                    changes</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <div class="modal-footer"></div>
                            </div>
                        </div>
                    </div>

                    <!-- end bootstrap model -->
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                            $('#MChartofAccount').DataTable({
                                processing: true,
                                serverSide: true,
                                ajax: "{{ url('chartofaccount') }}",
                                columns: [
                                    {
                                        data: 'account',
                                        name: 'account'
                                    },
                                    {
                                        data: 'accountsub',
                                        name: 'accountsub'
                                    },

                                    {
                                        data: 'code',
                                        name: 'code'
                                    },

                                    {
                                        data: 'description',
                                        name: 'description'
                                    },
                                    {
                                        data: 'opening_balance',
                                        name: 'opening_balance'
                                    },
                                    // {
                                    //     data: 'controlaccount',
                                    //     name: 'controlaccount'
                                    // },
                                    // {
                                    //     data: 'bankaccount',
                                    //     name: 'bankaccount'
                                    // },
                                    // {
                                    //     data: 'BC',
                                    //     name: 'BC'
                                    // },
                                    // {
                                    //     data: 'OC',
                                    //     name: 'OC'
                                    // },
                                    {
                                        data: 'action',
                                        name: 'action',
                                        orderable: false
                                    },
                                ],
                                order: [
                                    [0, 'desc']
                                ]
                            });
                        });

                        function add() {
                            $('#ItemForm').trigger("reset");
                            $('#ItemModal').html("Add Chart of Account");
                            $('#Item-modal').modal('show');
                            $('#id').val('');
                        }

                        function editFunc(id) {
                            $.ajax({
                                type: "POST",
                                url: "{{ url('ChartofAccount_edit') }}",
                                data: {
                                    id: id
                                },
                                dataType: 'json',
                                success: function (res) {
                                    $('#ItemModal').html("Edit Item");
                                    $('#Item-modal').modal('show');
                                    $('#id').val(res.id);
                                    $('#account').val(res.account);
                                    $('#accountsub').val(res.accountsub);
                                    $('#code').val(res.code);
                                    $('#description').val(res.description);
                                    $('#opening_balance').val(res.opening_balance);
                                    $('#controlaccount').val(res.controlaccount);
                                    $('#bankaccount').val(res.bankaccount);
                                    $('#BC').val(res.BC);
                                    $('#OC').val(res.OC);
                                }
                            });
                        }

                        function deleteFunc(id) {
                            if (confirm("Delete Record?") == true) {
                                var id = id;
                                // ajax
                                $.ajax({
                                    type: "POST",
                                    url: "{{ url('ChartofAccount_delete') }}",
                                    data: {
                                        id: id
                                    },
                                    dataType: 'json',
                                    success: function (res) {
                                        var oTable = $('#Item').dataTable();
                                        oTable.fnDraw(false);
                                    }
                                });
                            }
                        }

                        $('#ItemForm').submit(function (e) {
                            e.preventDefault();
                            var formData = new FormData(this);
                            $.ajax({
                                type: 'POST',
                                url: "{{ url('ChartofAccount_store')}}",
                                data: formData,
                                cache: false,
                                contentType: false,
                                processData: false,
                                success: (data) => {
                                    $("#Item-modal").modal('hide');
                                    var oTable = $('#Item').dataTable();
                                    oTable.fnDraw(false);
                                    $("#btn-save").html('Submit');
                                    $("#btn-save").attr("disabled", false);
                                },
                                error: function (data) {
                                    console.log(data);
                                }
                            });
                        });
                    </script>

                    <script>
                        data - cfasync = "false"
                        src = "../../../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js" >

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