@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
          <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" >
                <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
                {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script> --}}
                <link  href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
                <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
                <title> Gentral Receipt </title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

                <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
                <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
                <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
                <link rel="stylesheet" href="../assets/css/style.css">
                <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
                <STYLE>
                    
                </STYLE>
            </head>

            <body>


        <div class="main-wrapper">
            <div class="page-wrapper">
                <div class="content container-fluid">
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="page-title"> Gentral Receipt </h3>
                                <hr>
                            </div>
                        </div>
                    </div>

                    <div class="container mt-2">
                        <div class="row">

                            <div class="col-lg-12 margin-tb">
                                <div class="pull-left">

                                </div>
                                <div class="pull-right mb-2">
                                    <a class="btn btn-warning card-body shadow p-3 mb-5" onClick="add()" href="javascript:void(0)">Add Gentral Receipt </a>
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
                            <table class="table table-bordered" id="TGentralReceipt">
                          <thead>
    <tr style="background-color:hsl(147, 50%, 47%);">
        <th>Date</th>
        <th>DR Account</th>
        <th>CR Account</th>
        <th>Description</th>
        <th>Amount</th>
        <th>Status</th>  <!-- Add this -->
        <th>Action</th>
    </tr>
</thead>
                            </table>
                        </div>
                    </div>


                    <!-- boostrap employee model -->
                    <div class="modal fade" id="Store-modal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Gentral Receipt </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
									<div class="card-body shadow p-3 mb-5 bg-body-tertiary rounded">
                                    <form action="javascript:void(0)"  id="StoreForm" name="StoreForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" id="id">
										<div class="form-group row">
											<label class="col-form-label col-lg-2">Date </label>
											<div class="col-lg-7">
											  <div class="input-group">
												<input type="date" class="form-control" placeholder="Amount"  id="date" name="date"
												 aria-describedby="basic-addon2" value="{{ date('Y-m-d') }}">
												<div class="input-group-append">
												</div>
											  </div>
											</div>
										  </div>
										<div class="form-group row">
											<label class="col-form-label col-lg-2">CR Account</label>
											<div class="col-lg-7">
											  <div class="input-group">
												  <select   class="select form-control" name="cramount" id="cramount"
												  aria-hidden="true">
												  <option value="">Please Select</option>
												  @foreach($Amount as $DepartmentData)
												  <option value="{{ $DepartmentData->description}}">
													  {{ $DepartmentData->description }}</option>
												  @endforeach
											  </select>

											  </div>
											</div>
                                            <div class="col-lg-3">
                                                <select hidden="hidden"
                                                class="select form-control" name="crcode" id="crcode"
                                                aria-hidden="true">
                                                <option value="">Please Select</option>
                                                @foreach($Amount as $DepartmentData)
                                                <option value="{{ $DepartmentData->code}}">
                                                    {{ $DepartmentData->code }}</option>
                                                @endforeach
                                            </select>
                                            </div>
										  </div>

										<div class="form-group row">
											<label class="col-form-label col-lg-2">DR Account</label>
											<div class="col-lg-7">
											  <div class="input-group">
												{{-- <input type="text" class="form-control" placeholder="DR Account"
												 aria-label="Username" aria-describedby="basic-addon1"
												 id="dramount" name="dramount"> --}}
												 <select class="select form-control" name="dramount" id="dramount"
												 aria-hidden="true">
												 <option value="">Please Select</option>
												 @foreach($Amount as $DepartmentData)
												 <option value="{{ $DepartmentData->description}}">
													 {{ $DepartmentData->description }}</option>
												 @endforeach
											 </select>
											  </div>
											</div>

                                            <div class="col-lg-3">
                                                <select hidden="hidden"
                                                class="select form-control" name="drcode" id="drcode"
                                                aria-hidden="true">
                                                <option value="">Please Select</option>
                                                @foreach($Amount as $DepartmentData)
                                                <option value="{{ $DepartmentData->code}}">
                                                    {{ $DepartmentData->code }}</option>
                                                @endforeach
                                            </select>
                                            </div>
										  </div>

										<div class="form-group row">
											<label class="col-form-label col-lg-2">Description</label>
											<div class="col-lg-7">
											  <div class="input-group">
												{{-- <input type="text" class="form-control" placeholder="Description"
												 aria-label="Username" aria-describedby="basic-addon1"
												 id="description" name="description"> --}}
												 <textarea id="description" name="description" class="form-control" required="" placeholder="Enter the Expense Note "></textarea>
											  </div>
											</div>
										  </div>

										  <div class="form-group row">
											<label class="col-form-label col-lg-2">Amount </label>
											<div class="col-lg-7">
											  <div class="input-group">
												<input type="number" class="form-control" placeholder="Amount"  id="amount" name="amount"
												 aria-describedby="basic-addon2">
												<div class="input-group-append">
												</div>
											  </div>
											</div>
										  </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="hidden" class="form-control" id="OC"
                                                name="OC" placeholder="Enter a Department Name"
                                                value="{{ Auth::user()->username}}" readonly>
                                            </div>

                                            <div class="col-md-6">
                                               <input type="hidden" class="form-control" id="BC"
                                               name="BC" placeholder="Enter a Department Name"
                                               value="{{ Auth::user()->BC}}" readonly>
                                           </div>
                                       </div>

                                        <div class="col-sm-offset-2 col-sm-10"><br/>
                                            <button type="submit" class="btn btn-primary" id="btn-save">Save changes</button>
                                        </div>
                                    </form>

                                </div>
							</div>
                                <div class="modal-footer"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
         </div>

		 <script>
			var dateObj = new Date();
			var month = ("0" + (dateObj.getMonth() + 1)).slice(-2);
			var day = ("0" + dateObj.getDate()).slice(-2);
			var currentDate = dateObj.getFullYear() + "-" + month + "-" + day;

			document.getElementById('date').value = currentDate;
		</script>


                    <!-- end bootstrap model -->
                    <script type="text/javascript">
                    $(document).ready( function () {
                        $.ajaxSetup({
                            headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        $('#TGentralReceipt').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: "{{ url('gentralreceipt') }}",
 columns: [
    { data: 'date', name: 'date' },
    { data: 'cramount', name: 'cramount' },
    { data: 'dramount', name: 'dramount' },
    { data: 'description', name: 'description' },
    { data: 'amount', name: 'amount' },
    {
        data: 'status',
        name: 'status',
        render: function(data, type, row) {
            if(data === 'Approval') {
                return '<span class="badge bg-success">Approved</span>';
            } else {
                return '<span class="badge bg-warning">Pending</span>';
            }
        }
    },
    { data: 'action', name: 'action', orderable: false},
],
                            order: [[0, 'desc']]
                        });
                    });

                    function add(){
                        $('#StoreForm').trigger("reset");
                        $('#StoreModal').html("Add Store");
                        $('#Store-modal').modal('show');
                        $('#id').val('');
                    }

                    function editFunc(id){
                        $.ajax({
                            type:"POST",
                            url: "{{ url('UpdateGentralReceipt') }}",
                            data: { id: id },
                            dataType: 'json',
                            success: function(res){
                                $('#StoreModal').html("Edit Item");
                                $('#Store-modal').modal('show');
								$('#id').val(res.id);
								$('#date').val(res.date);
                                $('#cramount').val(res.cramount);
                                $('#crcode').val(res.crcode);
                                $('#dramount').val(res.dramount);
                                $('#drcode').val(res.drcode);
								$('#description').val(res.description);
                                $('#amount').val(res.amount);
                            }
                        });
                    }


                    function deleteFunc(id){
                        if (confirm("Delete Record?") == true) {
                            var id = id;
                            // ajax
                            $.ajax({
                                type:"POST",
                                url: "{{ url('DeleteGentralReceipt') }}",
                                data: { id: id },
                                dataType: 'json',
                                success: function(res){
                                    var oTable = $('#TGentralReceipt').dataTable();
                                    oTable.fnDraw(false);
                                }
                            });
                        }
                    }

function ApprovalFunc(id, status){
    // Check if already approved
    if(status === 'Approval'){
        alert('This record is already approved!');
        return false;
    }

    if (confirm("Approve this Record?") == true) {
        var id = id;
        // ajax
        $.ajax({
            type:"POST",
            url: "{{ url('ApprovalGentralReceipt') }}",
            data: { id: id },
            dataType: 'json',
            success: function(res){
                if(res.success){
                    alert('Record approved successfully!');
                    var oTable = $('#TGentralReceipt').dataTable();
                    oTable.fnDraw(false);
                } else {
                    alert(res.message || 'Failed to approve record');
                }
            },
            error: function(xhr, status, error){
                alert('Error: ' + error);
            }
        });
    }
}

                    $('#StoreForm').submit(function(e) {
                        e.preventDefault();
                        var formData = new FormData(this);
                        $.ajax({
                            type:'POST',
                            url: "{{ url('addGentralReceipt')}}",
                            data: formData,
                            cache:false,
                            contentType: false,
                            processData: false,
                            success: (data) => {
                                $("#Store-modal").modal('hide');
                                var oTable = $('#TGentralReceipt').dataTable();
                                oTable.fnDraw(false);
                                $("#btn-save").html('Submit');
                                $("#btn-save"). attr("disabled", false);
                            },
                            error: function(data){
                                console.log(data);
                            }
                        });
                    });


                    </script>
<script>
    $(document).ready(function () {
        // Listen for changes in the Weight and QTY fields
        $('#cramount').on('change', function () {
            let category = $('#cramount').val();
            $.ajax({
                        url: "{{ route('show_voucher_ajax') }}",
                        method: 'GET',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            category: category
                        },
                        success: function (res) {
                            if (res.status == 'success') {

                                var itemsCodeSelected = $('#crcode');
                                // var itemDescriptionSelected = $('#item_description');
                                // var itemUnit_price = $('#unit_price');
                                // Change this to match your items select element
                                // Clear existing options
                                itemsCodeSelected.empty();
                                // itemDescriptionSelected.empty();
                                // itemUnit_price.empty();

                                $.each(res.data, function (index, item) {
                                    itemsCodeSelected.append($('<option>', {
                                        value: item.code,
                                        text: item.code
                                    }));
                                });



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


<script>
    $(document).ready(function () {
        // Listen for changes in the Weight and QTY fields
        $('#dramount').on('change', function () {
            let amount = $('#dramount').val();
            $.ajax({
                        url: "{{ route('show_dr_voucher_ajax') }}",
                        method: 'GET',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            amount: amount
                        },
                        success: function (res) {
                            if (res.status == 'success') {

                                var itemsSelected = $('#drcode');
                                // var itemDescriptionSelected = $('#item_description');
                                // var itemUnit_price = $('#unit_price');
                                // Change this to match your items select element
                                // Clear existing options
                                itemsSelected.empty();
                                // itemDescriptionSelected.empty();
                                // itemUnit_price.empty();

                                $.each(res.data, function (index, item1) {
                                    itemsSelected.append($('<option>', {
                                        value: item1.code,
                                        text: item1.code,
                                    }));
                                });

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