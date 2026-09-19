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
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>Item Category</title>

    <style>
        :root {
            --primary-color: #4a6cf7;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --border-radius: 10px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: #f4f6f9;

        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            color: white;
            box-shadow: var(--box-shadow);
        }

        .page-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 28px;
        }

        .page-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .action-bar {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
        }

        .search-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .btn-add-new {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-add-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .table-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            padding: 15px;
            border: none;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9ff;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }

        .badge-code {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }


        .btn-action {
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            margin: 0 3px;
        }

        .btn-edit {
            background-color: #e8f5e9;
            color: #4caf50;
        }

        .btn-edit:hover {
            background-color: #4caf50;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
        }

        .btn-delete {
            background-color: #ffebee;
            color: #f44336;
        }

        .btn-delete:hover {
            background-color: #f44336;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3);
        }

        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            padding: 20px 30px;
        }

        .modal-title {
            font-weight: 600;
            font-size: 20px;
        }

        .modal-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
        }

        .form-control:read-only {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .required {
            color: #dc3545;
            margin-left: 3px;
        }

        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-update {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
            color: white;
        }

        .btn-close-modal {
            background-color: #e0e0e0;
            color: #666;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-close-modal:hover {
            background-color: #d0d0d0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h4 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #666;
        }

        .stats-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
            text-align: center;
        }

        .stats-card h4 {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
            color: var(--primary-color);
        }

        .stats-card p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #c62828;
        }

        .alert-box {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .alert-error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            border-left: 5px solid #c92a2a;
        }

        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
            color: white;
            border-left: 5px solid #2b8a3e;
        }

        .alert-box i {
            font-size: 24px;
            margin-right: 15px;
        }

        .alert-box .alert-content {
            flex: 1;
        }

        .alert-box .alert-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .alert-box .alert-message {
            font-size: 14px;
            opacity: 0.95;
        }

        .alert-box ul {
            margin: 5px 0 0 0;
            padding-left: 20px;
            list-style-type: disc;
        }

        .alert-box li {
            margin: 5px 0;
            line-height: 1.5;
        }

        .alert-close {
            background: rgba(255, 255, 255, 0.3);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .alert-close:hover {
            background: rgba(255, 255, 255, 0.5);
            transform: rotate(90deg);
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .shake-animation {
            animation: shake 0.5s ease;
        }

        @media (max-width: 768px) {
            .action-bar {
                flex-direction: column;
            }

            .search-box {
                max-width: 100%;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><i class="fas fa-layer-group me-2"></i>Item Category Management</h3>
                            <p>Manage your item categories and classifications</p>
                        </div>
                        <div class="stats-badge" style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 20px;">
                            <span style="font-size: 24px; font-weight: 700;">{{ count($itemCategoryData) }}</span>
                            <p style="margin: 0; font-size: 12px;">Total Categories</p>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="action-bar">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search categories...">
                        <i class="fas fa-search"></i>
                    </div>
                    <button class="btn btn-add-new" data-bs-toggle="modal" data-bs-target="#addCategoryModel">
                        <i class="fas fa-plus-circle me-2"></i>Add New Category
                    </button>
                </div>

                <!-- Categories Table -->
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-tag me-2"></i>Category Name</th>
                                    <th class="text-center"><i class="fas fa-cog me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($itemCategoryData as $data)
                                <tr class="category-row">
                                    <td>
                                        <span class="badge-category">{{ $data->category }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn-action btn-edit update_category_form"
                                            data-bs-toggle="modal" data-bs-target="#updateCategoryModel"
                                            data-id="{{$data->id}}"
                                            data-category="{{$data->category}}">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                        <button class="btn-action btn-delete delete_category"
                                            data-id="{{$data->id}}">
                                            <i class="fas fa-trash-alt me-1"></i>Delete
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <h4>No Categories Found</h4>
                                            <p>Start by adding your first category</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add Category Modal -->
                <div class="modal fade" id="addCategoryModel" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Category</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="errMsgContainer"></div>
                                <form id="addCategory">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Category Name<span class="required">*</span></label>
                                        <input type="text" class="form-control" placeholder="Enter category name"
                                            id="categoryName" name="categoryName" required maxlength="80">
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="button" class="btn-save add_category">
                                            <i class="fas fa-save me-2"></i>Save Category
                                        </button>
                                        <button type="button" class="btn-close-modal ms-2" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Category Modal -->
                <div class="modal fade" id="updateCategoryModel" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Category</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="errMsgContainer2"></div>
                                <form id="updateItemCategory">
                                    @csrf
                                    <input type="hidden" id="up_id" name="up_id">

                                    <div class="mb-3">
                                        <label class="form-label">Category Name<span class="required">*</span></label>
                                        <input type="text" class="form-control" placeholder="Enter category name"
                                            id="up_CategoryName" name="up_CategoryName" required maxlength="80">
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="button" class="btn-update update_category">
                                            <i class="fas fa-check me-2"></i>Update Category
                                        </button>
                                        <button type="button" class="btn-close-modal ms-2" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </button>
                                    </div>
                                </form>
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

            // Function to show alert box in modal
            function showModalAlert(container, type, title, message) {
                let iconClass = type === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle';
                let alertClass = type === 'error' ? 'alert-error' : 'alert-success';

                $(container).html(
                    '<div class="alert-box ' + alertClass + ' shake-animation">' +
                        '<i class="fas ' + iconClass + '"></i>' +
                        '<div class="alert-content">' +
                            '<div class="alert-title">' + title + '</div>' +
                            '<div class="alert-message">' + message + '</div>' +
                        '</div>' +
                        '<button class="alert-close" onclick="this.parentElement.remove()">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</div>'
                );

                // Scroll to alert
                setTimeout(function() {
                    $(container)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);

                // Auto remove shake animation
                setTimeout(function() {
                    $(container + ' .alert-box').removeClass('shake-animation');
                }, 500);
            }

            // Real-time Search Functionality
            $('#searchInput').on('keyup', function() {
                let searchValue = $(this).val().toLowerCase();

                $('#categoriesTable tbody tr.category-row').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchValue) > -1);
                });

                // Show "no results" message if no categories match
                let visibleRows = $('#categoriesTable tbody tr.category-row:visible').length;
                if (visibleRows === 0) {
                    if ($('#noResultsRow').length === 0) {
                        $('#categoriesTable tbody').append(
                            '<tr id="noResultsRow"><td colspan="2"><div class="empty-state">' +
                            '<i class="fas fa-search"></i>' +
                            '<h4>No Results Found</h4>' +
                            '<p>Try adjusting your search terms</p>' +
                            '</div></td></tr>'
                        );
                    }
                } else {
                    $('#noResultsRow').remove();
                }
            });

            // Add new category
            $(document).on('click', '.add_category', function (e) {
                e.preventDefault();
                let categoryName = $('#categoryName').val();

                $.ajax({
                    url: "{{ route('add_category_ajax') }}",
                    method: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        categoryName: categoryName,
                    },
                    success: function (res) {
                        if (res.status == 'success') {
                            $('#addCategoryModel').modal('hide');
                            $('#addCategory')[0].reset();

                            toastr.success(res.message || 'Category added successfully!', "Success", {
                                "closeButton": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "timeOut": "3000"
                            });

                            // Reload page after short delay
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function (err) {
                        let errorList = '<ul style="margin: 0; padding-left: 20px;">';

                        if (err.responseJSON && err.responseJSON.errors) {
                            $.each(err.responseJSON.errors, function (index, value) {
                                errorList += '<li>' + value + '</li>';
                            });
                        } else {
                            errorList += '<li>An error occurred. Please try again.</li>';
                        }
                        errorList += '</ul>';

                        showModalAlert('.errMsgContainer', 'error', 'Validation Error', errorList);
                    }
                })
            });

            // Show category details in update form
            $(document).on('click', '.update_category_form', function () {
                let id = $(this).data('id');
                let categoryName = $(this).data('category');

                $('#up_id').val(id);
                $('#up_CategoryName').val(categoryName);
            });

            // Update category details
            $(document).on('click', '.update_category', function (e) {
                e.preventDefault();

                let up_id = $('#up_id').val();
                let up_categoryName = $('#up_CategoryName').val();

                $.ajax({
                    url: "{{ route('update_category_ajax') }}",
                    method: 'post',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        up_id: up_id,
                        up_categoryName: up_categoryName,
                    },
                    success: function (res) {
                        if (res.status == 'success') {
                            $("#updateCategoryModel").modal('hide');
                            $('#updateItemCategory')[0].reset();

                            toastr.success(res.message || 'Category updated successfully!', "Success", {
                                "closeButton": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "timeOut": "3000"
                            });

                            // Reload page after short delay
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function (err) {
                        let errorList = '<ul style="margin: 0; padding-left: 20px;">';

                        if (err.responseJSON && err.responseJSON.errors) {
                            $.each(err.responseJSON.errors, function (index, value) {
                                errorList += '<li>' + value + '</li>';
                            });
                        } else {
                            errorList += '<li>An error occurred. Please try again.</li>';
                        }
                        errorList += '</ul>';

                        showModalAlert('.errMsgContainer2', 'error', 'Validation Error', errorList);
                    }
                })
            });

            // Delete category with confirmation
            $(document).on('click', '.delete_category', function (e) {
                e.preventDefault();
                let id = $(this).data('id');
                let button = $(this);

                if (confirm('⚠️ Are you sure you want to delete this category?\n\nThis action cannot be undone.')) {
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

                    $.ajax({
                        url: "{{ route('delete_category_ajax') }}",
                        method: 'post',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            id: id
                        },
                        success: function (res) {
                            if (res.status == 'success') {
                                toastr.success(res.message || 'Category deleted successfully!', "Success", {
                                    "closeButton": true,
                                    "progressBar": true,
                                    "positionClass": "toast-top-right",
                                    "timeOut": "3000"
                                });

                                // Fade out and remove row
                                button.closest('tr').fadeOut(500, function() {
                                    $(this).remove();

                                    // Check if table is empty
                                    if ($('#categoriesTable tbody tr.category-row').length === 0) {
                                        $('#categoriesTable tbody').html(
                                            '<tr><td colspan="2"><div class="empty-state">' +
                                            '<i class="fas fa-inbox"></i>' +
                                            '<h4>No Categories Found</h4>' +
                                            '<p>Start by adding your first category</p>' +
                                            '</div></td></tr>'
                                        );
                                    }
                                });
                            }
                        },
                        error: function() {
                            button.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i>Delete');

                            toastr.error('Failed to delete category. Please try again.', 'Error', {
                                "closeButton": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "timeOut": "3000"
                            });
                        }
                    });
                }
            });

            // Clear error messages when modals close
            $('#addCategoryModel, #updateCategoryModel').on('hidden.bs.modal', function () {
                $('.errMsgContainer, .errMsgContainer2').html('');
                $('#addCategory, #updateItemCategory')[0].reset();
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
       <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
@endsection

</html>