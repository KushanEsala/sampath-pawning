@extends('layouts.topnavbar')
@extends('layouts.sidebar')

@section('content')
<style>
.forfeit-heading {
    text-align: center;
    font-weight: bold;
    font-size: 26px;
    color: #000107;
    margin-bottom: 20px;
    position: relative;
    display: inline-block;
}
.forfeit-heading::after {
    content: "";
    display: block;
    width: 60%;
    height: 3px;
    background-color: #00010e;
    margin: 8px auto 0;
    border-radius: 2px;
}
</style>

<div class="main-wrapper">
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="card">

                @if (session('success'))
                <script>
                    window.onload = function() {
                        alert("{{ session('success') }}");
                    };
                </script>
                @endif

                @if (session('error'))
                <script>
                    window.onload = function() {
                        alert("{{ session('error') }}");
                    };
                </script>
                @endif

                <div class="card-body">
                    <h3 class="forfeit-heading">Forfeit Article's</h3>
                    <div class="table-responsive">
                        <form id="forfeitForm">
                            <table id="forfeitTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                                <thead class="table-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Item Details</th>
                                        <th>Item Code</th>
                                        <th>Category</th>
                                        <th>Articles</th>
                                        <th>Condition</th>
                                        <th>Karatage</th>
                                        <th>Weight</th>
                                        <th>Quantity</th>
                                        <th>Pawning Price</th>
                                        <th>Sale Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->Bar_code }}</td>
                                        <td>{{ $item->Item_code }}</td>
                                        <td>{{ $item->category }}</td>
                                        <td>{{ $item->Item_description }}</td>
                                        <td>{{ $item->Brand }}</td>
                                        <td>{{ $item->Make }}</td>
                                        <td>{{ $item->Total_Weight }}</td>
                                        <td>{{ $item->QTY }}</td>
                                        <td>{{ $item->purchasePrice }}</td>
                                        <td>{{ $item->saleprice }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-btn" data-id="{{ $item->id }}">Edit</button>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $item->id }}">Delete</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="editForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit-id" name="id" />
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Forfeit Article</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12">
                                    <label for="edit-category" class="form-label">Article Code</label>
                                    <input type="text" class="form-control" id="edit-Item_code" name="Item_code" required readonly>
                                    </div>
                                </div> 
                                <br>   
                            <div class="row">
                                    <div class="col-md-6">
                                    <label for="edit-category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="edit-category" name="category" required>
                                    </div>

                                    <div class="col-md-6">
                                    <label for="edit-category" class="form-label">Article Name</label>
                                    <input type="text" class="form-control" id="edit-Item_description" name="Item_description" required>
                                    </div>
                           </div>
                           <br>
                           
                           <div class="row"> 
                                    <div class="col-md-4">
                                    <label for="edit-category" class="form-label">Karatage</label>
                                    <input type="text" class="form-control" id="edit-Make" name="Make" required>
                                    </div>

                                    <div class="col-md-4">
                                    <label for="edit-category" class="form-label">Condition</label>
                                    <input type="text" class="form-control" id="edit-Brand" name="Brand" required>
                                    </div>

                                    <div class="col-md-4">
                                    <label for="edit-category" class="form-label">Quantity</label>
                                    <input type="text" class="form-control" id="edit-QTY" name="QTY" required>
                                    </div>
                           </div>
                           <br>
                            <div class="row">
                                    <div class="col-md-12">
                                    <label for="edit-category" class="form-label">Article Weight</label>
                                    <input type="text" class="form-control" id="edit-Total_Weight" name="Total_Weight" required readonly>
                                    </div>
                            </div> 
                            <br>
                           <div class="row">
                                     <div class="col-md-6">
                                    <label for="edit-category" class="form-label">Pawning Value Price</label>
                                    <input type="text" class="form-control" id="edit-purchasePrice" name="purchasePrice" required>
                                    </div>

                                    <div class="col-md-6">
                                    <label for="edit-category" class="form-label">Sales Price</label>
                                    <input type="text" class="form-control" id="edit-saleprice" name="saleprice" required>
                                    </div>
                           </div>  
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Save changes</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#forfeitTable').DataTable({
        responsive: true
    });

    // Open modal and load data on Edit button click
    $('#forfeitTable').on('click', '.edit-btn', function() {
        var id = $(this).data('id');

        $.ajax({
            url: '/forfeit-articles/' + id,
            type: 'GET',
            success: function(data) {
                $('#edit-id').val(data.id);
                $('#edit-Item_code').val(data.Item_code); 
                $('#edit-category').val(data.category);
                $('#edit-Item_description').val(data.Item_description);
                $('#edit-QTY').val(data.QTY);
                $('#edit-Brand').val(data.Brand);
                $('#edit-Make').val(data.Make);
                $('#edit-purchasePrice').val(data.purchasePrice);
                $('#edit-saleprice').val(data.saleprice);
                $('#edit-Total_Weight').val(data.Total_Weight);
                var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            },
            error: function() {
                alert('Failed to fetch data for editing');
            }
        });
    });

    // Handle form submit for editing
    $('#editForm').submit(function(e) {
        e.preventDefault();
        var id = $('#edit-id').val();
        var formData = $(this).serialize();

        $.ajax({
            url: '/forfeit-articles/' + id,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(updatedItem) {
                // Update row data in DataTable
                var row = $('#forfeitTable').find('button.edit-btn[data-id="'+id+'"]').closest('tr');
                table.row(row).data([
                    row.find('td').eq(0).text(), // # (index)
                    row.find('td').eq(1).html(), // checkbox cell
                    updatedItem.Item_code,
                    updatedItem.category,
                    updatedItem.Item_description,
                    updatedItem.Brand,
                    updatedItem.Total_Weight,
                    updatedItem.Make,
                    updatedItem.QTY,
                    updatedItem.purchasePrice,
                    updatedItem.saleprice,
                    row.find('td').eq(11).html() // Actions buttons
                ]).draw(false);

                var editModalEl = document.getElementById('editModal');
                var modal = bootstrap.Modal.getInstance(editModalEl);
                modal.hide();

                alert('Record updated successfully');
            },
            error: function() {
                alert('Failed to update record');
            }
        });
    });

    // Handle Delete button click
    $('#forfeitTable').on('click', '.delete-btn', function() {
        if (!confirm('Are you sure you want to delete this record?')) return;

        var id = $(this).data('id');
        var row = $(this).closest('tr');

        $.ajax({
            url: '/forfeit-articles/' + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function() {
                table.row(row).remove().draw();
                alert('Record deleted successfully');
            },
            error: function() {
                alert('Failed to delete record');
            }
        });
    });

    // Optional: handle Save Selected button form submit
    $('#forfeitForm').submit(function(e) {
        e.preventDefault();
        var selectedIds = $(this).find('input[name="selected_ids[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one record.');
            return;
        }

        // Example AJAX POST to save selected ids (customize your route and payload)
        $.ajax({
            url: '/forfeit-articles/save-selected',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                selected_ids: selectedIds
            },
            success: function(response) {
                alert('Selected records saved successfully.');
                // optionally do something here
            },
            error: function() {
                alert('Failed to save selected records.');
            }
        });
    });

});
</script>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    {{-- <script src="assets/plugins/select2/js/select2.min.js"></script> --}}
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

@endsection