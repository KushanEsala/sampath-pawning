@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <title>{{ $isAdmin ? 'User Management' : 'My Profile' }}</title>
    <style>
        /* Common Styles */
        .card-wrapper {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .readonly-field {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
        }

        /* Profile Section */
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .profile-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }

        .profile-icon i {
            font-size: 40px;
            color: #fff;
        }

        /* Admin Table Styles */
        .nav-tabs-custom {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 10px 10px 0 0;
        }

        .nav-tabs-custom .nav-link.active {
            color: #667eea;
            background: linear-gradient(to bottom, rgba(102, 126, 234, 0.1), transparent);
            border-bottom: 3px solid #667eea;
        }

        .badge-role {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-admin {
            background: #e74c3c;
            color: #fff;
        }

        .badge-user {
            background: #3498db;
            color: #fff;
        }

        .badge-cashier {
             background: #0d4d79;
            color: #f0ecec;
        }

        .badge-manager {
            background: #f39c12;
            color: #fff;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            margin: 0 2px;
            transition: 0.2s;
        }

        .btn-action:hover {
            transform: scale(1.05);
        }

        /* Modal Styles */
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 10px 10px 0 0;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        /* DataTable Custom */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            color: #fff !important;
        }

        .tab-content {
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Validation Alert Styles */
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: #fff;
        }

        .alert-danger .alert-heading {
            color: #fff;
            font-weight: 600;
        }

        .alert-danger ul {
            padding-left: 20px;
        }

        .alert-danger .btn-close {
            filter: brightness(0) invert(1);
        }

        .invalid-feedback {
            display: block;
            margin-top: 5px;
            font-size: 13px;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Disabled field styling */
        .form-control:disabled,
        .form-select:disabled {
            background-color: #e9ecef;
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">

                <!-- Validation Errors Alert -->
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <!-- Page Header -->
                <div class="page-header">
                    <h2>
                        <i class="fas fa-{{ $isAdmin ? 'users-cog' : 'user' }} me-2"></i>
                        {{ $isAdmin ? 'User Management & Profile' : 'My Profile' }}
                    </h2>
                    <p class="mb-0">
                        {{ $isAdmin ? 'Manage all system users and your profile' : 'View and update your profile information' }}
                    </p>
                </div>

                @if($isAdmin)
                    <!-- Admin View with Tabs -->
                    <div class="card-wrapper">
                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#myProfile">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#allUsers">
                                    <i class="fas fa-users me-2"></i>All Users ({{ $users->count() }})
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- My Profile Tab -->
                            <div id="myProfile" class="tab-pane fade show active">
                                @include('profile-partials.profile-form', ['editUser' => $user, 'isSelf' => true])
                            </div>

                            <!-- All Users Tab -->
                            <div id="allUsers" class="tab-pane fade">
                                <div class="table-header-actions">
                                    <h5 class="mb-0">User List</h5>
                                    <button class="btn btn-success-custom" onclick="showAddUserModal()">
                                        <i class="fas fa-user-plus me-2"></i>Add New User
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table id="usersTable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Branch</th>
                                                <th>BC</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($users as $u)
                                            <tr>
                                                <td>{{ $u->id }}</td>
                                                <td>{{ $u->username }}</td>
                                                <td>{{ $u->name }}</td>
                                                <td>{{ $u->email }}</td>
                                                <td>
                                                    <span class="badge badge-role badge-{{ strtolower($u->role) }}">
                                                        {{ $u->role }}
                                                    </span>
                                                </td>
                                                <td>{{ $u->Branch }}</td>
                                                <td>{{ $u->BC }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary btn-action" onclick="editUser({{ $u->id }})">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    @if($u->id !== Auth::id())
                                                    <button class="btn btn-sm btn-danger btn-action" onclick="deleteUser({{ $u->id }})">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- Regular User View -->
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <div class="card-wrapper">
                                @include('profile-partials.profile-form', ['editUser' => $user, 'isSelf' => true])
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Add User Modal (Admin Only) -->
    @if($isAdmin)
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" method="POST" action="{{ route('profile.admin.store') }}">
                        @csrf
                        <input type="hidden" name="_form_type" value="add">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Username *</label>
                                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                           value="{{ old('username') }}" required>
                                    <small class="text-muted">Must be unique</small>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}">
                                    <small class="text-muted">Optional</small>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Role *</label>
                                    <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                                        <option value="">Select Role</option>
                                        <option value="Cashier" {{ old('role') == 'Cashier' ? 'selected' : '' }}>Cashier</option>
                                        <option value="User" {{ old('role') == 'User' ? 'selected' : '' }}>User</option>
                                        <option value="Manager" {{ old('role') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Branch *</label>
                                    <select class="select form-control @error('Branch') is-invalid @enderror" name="Branch" id="add_Branch" required>
                                        <option value="">Please Select</option>
                                        @foreach($branch as $BranchData)
                                            <option value="{{ $BranchData->name }}" data-bc="{{ $BranchData->bccode }}" {{ old('Branch') == $BranchData->name ? 'selected' : '' }}>
                                                {{ $BranchData->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('Branch')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">BC</label>
                                    <input type="text" name="BC" id="add_BC" class="form-control readonly-field @error('BC') is-invalid @enderror"
                                           value="{{ old('BC') }}" readonly>
                                    <small class="text-muted">Auto-filled from Branch</small>
                                    @error('BC')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Password *</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                           required minlength="6">
                                    <small class="text-muted">Minimum 6 characters</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-success-custom">
                                <i class="fas fa-user-plus me-2"></i>Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal (Admin Only) -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form_type" value="edit">
                        <input type="hidden" id="edit_user_id" name="edit_user_id">
                        <input type="hidden" id="edit_user_role" name="edit_user_role">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Username</label>
                                    <input type="text" id="edit_username" class="form-control readonly-field" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="edit_email" class="form-control @error('email') is-invalid @enderror">
                                    <small class="text-muted">Optional</small>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Role *</label>
                                    <select name="role" id="edit_role" class="form-control @error('role') is-invalid @enderror" required>
                                        <option value="User">User</option>
                                        <option value="Cashier">Cashier</option>
                                        <option value="Manager">Manager</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                    <small class="text-muted" id="role_restriction_note" style="display:none; color: #f39c12;">
                                        <i class="fas fa-info-circle"></i> Only Admin/Manager can edit this field
                                    </small>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Branch *</label>
                                    <select class="select form-control @error('Branch') is-invalid @enderror" name="Branch" id="edit_branch_select" required>
                                        <option value="">Please Select</option>
                                        @foreach($branch as $BranchData)
                                            <option value="{{ $BranchData->name }}" data-bc="{{ $BranchData->bccode }}">
                                                {{ $BranchData->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="branch_restriction_note" style="display:none; color: #f39c12;">
                                        <i class="fas fa-info-circle"></i> Only Admin/Manager can edit this field
                                    </small>
                                    @error('Branch')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">BC</label>
                                    <input type="text" name="BC" id="edit_bc" class="form-control readonly-field @error('BC') is-invalid @enderror" readonly>
                                    <small class="text-muted">Auto-filled from Branch</small>
                                    @error('BC')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Change Password (Optional)</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                    <small class="text-muted">Leave blank to keep current password</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="delete-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    {!! Toastr::message() !!}

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>

    <script>
        // Get current user role from backend
        const currentUserRole = "{{ Auth::user()->role }}";

        $(document).ready(function() {
            // Initialize Feather Icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Initialize DataTable
            @if($isAdmin)
            $('#usersTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    search: "Search users:",
                    lengthMenu: "Show _MENU_ users per page"
                }
            });

            // Auto-fill BC Code when Branch is selected in Add User Modal
            $('#add_Branch').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const bcCode = selectedOption.data('bc');
                $('#add_BC').val(bcCode || '');
            });

            // Auto-fill BC Code when Branch is selected in Edit User Modal
            $('#edit_branch_select').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const bcCode = selectedOption.data('bc');
                $('#edit_bc').val(bcCode || '');
            });

            // Auto-open Add User Modal if there are validation errors
            @if($errors->any() && old('_form_type') === 'add')
                showAddUserModal();
                // Auto-fill BC if Branch was selected
                const oldBranch = "{{ old('Branch') }}";
                if (oldBranch) {
                    $('#add_Branch').trigger('change');
                }
            @endif

            // Auto-open Edit User Modal if there are validation errors
            @if($errors->any() && old('_form_type') === 'edit' && old('edit_user_id'))
                editUser({{ old('edit_user_id') }});
            @endif
            @endif
        });

        @if($isAdmin)
        // Show Add User Modal
        function showAddUserModal() {
            // Don't reset form if there are validation errors
            @if(!$errors->any() || old('_form_type') !== 'add')
            document.getElementById('addUserForm').reset();
            $('#add_BC').val('');
            @endif

            // Show modal
            new bootstrap.Modal(document.getElementById('addUserModal')).show();
        }

        // Function to check if current user can edit Role and Branch
        function canEditRoleAndBranch(currentRole) {
            return currentRole === 'Admin' || currentRole === 'Manager';
        }

        // Edit User Function
        function editUser(userId) {
            const users = @json($users);
            const user = users.find(u => u.id === userId);

            if (user) {
                $('#edit_user_id').val(user.id);
                $('#edit_user_role').val(user.role);
                $('#edit_username').val(user.username);
                $('#edit_name').val(user.name);
                $('#edit_email').val(user.email);
                $('#edit_role').val(user.role);
                $('#edit_branch_select').val(user.Branch);
                $('#edit_bc').val(user.BC);

                // Check if current user can edit Role and Branch fields
                const canEdit = canEditRoleAndBranch(currentUserRole);

                if (!canEdit) {
                    // Disable Role field
                    $('#edit_role').prop('disabled', true);
                    $('#role_restriction_note').show();

                    // Disable Branch field
                    $('#edit_branch_select').prop('disabled', true);
                    $('#branch_restriction_note').show();
                } else {
                    // Enable Role field
                    $('#edit_role').prop('disabled', false);
                    $('#role_restriction_note').hide();

                    // Enable Branch field
                    $('#edit_branch_select').prop('disabled', false);
                    $('#branch_restriction_note').hide();
                }

                // Set form action
                $('#editUserForm').attr('action', '/profile/admin/update/' + userId);

                // Show modal
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            }
        }

        // Delete User Function
        function deleteUser(userId) {
            if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const form = document.getElementById('delete-form');
                form.action = '/profile/admin/delete/' + userId;
                form.submit();
            }
        }
        @endif
    </script>
</body>
@endsection
</html>