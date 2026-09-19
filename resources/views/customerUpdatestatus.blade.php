@extends('layouts.topnavbar')
@extends('layouts.sidebar')

@section('content')

<style>
    :root {
        --cu-terracotta: #174177;
        --cu-terracotta-dark: #1840f3;
        --cu-amber: #61cfe2;
        --cu-brown: #120775;
        --cu-parchment: #faf6ee;
        --cu-parchment-dark: #f0e8d8;
        --cu-border: #e4d9c4;
        --cu-text: #3c3128;
        --cu-muted: #688a73;
    }

    .cu-wrapper {
        color: var(--cu-text);
    }

    .cu-wrapper h1, .cu-wrapper h2, .cu-wrapper h3,
    .cu-wrapper h4, .cu-wrapper h5, .cu-wrapper h6,
    .cu-wrapper .page-title {
        color: var(--cu-brown);
        letter-spacing: 0.02em;
    }

    .cu-wrapper .page-title {
        font-weight: 700;
        border-bottom: 3px solid var(--cu-terracotta);
        display: inline-block;
        padding-bottom: 6px;
    }

    .cu-card {
        background: #fff;
        border: 1px solid var(--cu-border);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(91, 70, 54, 0.06);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .cu-card .card-header {
        background: var(--cu-parchment);
        border-bottom: 1px solid var(--cu-border);
        padding: 0.9rem 1.25rem;
    }

    .cu-card .card-header h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .cu-card .card-body {
        padding: 1.5rem;
    }

    .cu-section-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--cu-terracotta-dark);
        margin: 0 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--cu-border);
    }

    .cu-wrapper label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--cu-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.3rem;
    }

    .cu-wrapper .form-control,
    .cu-wrapper .form-select {
        border: 1px solid var(--cu-border);
        border-radius: 6px;
        padding: 0.55rem 0.75rem;
        background: var(--cu-parchment);
        color: var(--cu-text);
    }

    .cu-wrapper .form-control:focus,
    .cu-wrapper .form-select:focus {
        border-color: var(--cu-terracotta);
        box-shadow: 0 0 0 0.2rem rgba(193, 99, 61, 0.15);
        background: #fff;
    }

    .cu-btn-primary {
        background: var(--cu-terracotta);
        border-color: var(--cu-terracotta);
        color: #fff;
        font-weight: 600;
        letter-spacing: 0.03em;
        padding: 0.55rem 1.4rem;
        border-radius: 6px;
    }
    .cu-btn-primary:hover {
        background: var(--cu-terracotta-dark);
        border-color: var(--cu-terracotta-dark);
        color: #fff;
    }

    .cu-btn-secondary {
        background: transparent;
        border: 1px solid var(--cu-border);
        color: var(--cu-brown);
        font-weight: 600;
        padding: 0.55rem 1.4rem;
        border-radius: 6px;
    }
    .cu-btn-secondary:hover {
        background: var(--cu-parchment-dark);
        color: var(--cu-brown);
    }

    .cu-stat {
        background: var(--cu-parchment);
        border: 1px solid var(--cu-border);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        text-align: center;
    }

    .cu-stat .cu-stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--cu-muted);
        margin-bottom: 0.4rem;
    }

    .cu-stat .cu-stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--cu-terracotta-dark);
    }

    .cu-badge-active {
        background: #3f7d4f;
        color: #fff;
        padding: 0.3rem 0.7rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    .cu-badge-inactive {
        background: var(--cu-muted);
        color: #fff;
        padding: 0.3rem 0.7rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
    }

    .cu-not-found {
        background: var(--cu-parchment);
        border: 1px dashed var(--cu-terracotta);
        border-radius: 10px;
        padding: 2rem;
        text-align: center;
        color: var(--cu-brown);
        font-weight: 600;
    }
</style>

<div class="page-wrapper cu-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Customer Management</h3>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Validation Errors:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search Card -->
        <div class="cu-card">
            <div class="card-header">
                <h5><i class="fas fa-search me-2"></i>Find Customer</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('customerUpdatestatus') }}">
                    <div class="row align-items-end g-3">
                        <div class="col-md-8">
                            <label>Customer NIC</label>
                            <input type="text"
                                   name="nic"
                                   class="form-control"
                                   placeholder="Enter NIC"
                                   value="{{ $nic ?? '' }}"
                                   required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn cu-btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('customerUpdatestatus') }}"
                               class="btn cu-btn-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Customer Result -->
        @if(isset($customer))
            @if($customer)



                <div class="cu-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-user-edit me-2"></i>Edit Customer Details</h5>
                        @if($customer->Status == 1)
                            <span class="cu-badge-active">ACTIVE</span>
                        @else
                            <span class="cu-badge-inactive">INACTIVE</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('customerUpdatestatus.update', $customer->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <!-- Basic Information -->
                                <div class="col-md-12">
                                    <p class="cu-section-title">Basic Information</p>
                                </div>

                                <div class="col-md-2" style="display: none">
                                    <div class="form-group">
                                        <label>Title <span class="text-danger">*</span></label>
                                        <select name="title" class="form-select" >
                                            <option value="">Select</option>
                                            <option value="Mr" {{ $customer->Title == 'Mr' ? 'selected' : '' }}>Mr</option>
                                            <option value="Mrs" {{ $customer->Title == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                            <option value="Miss" {{ $customer->Title == 'Miss' ? 'selected' : '' }}>Miss</option>
                                            <option value="Dr" {{ $customer->Title == 'Dr' ? 'selected' : '' }}>Dr</option>
                                            <option value="Rev" {{ $customer->Title == 'Rev' ? 'selected' : '' }}>Rev</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2" style="display: none">
                                    <div class="form-group">
                                        <label>Gender <span class="text-danger">*</span></label>
                                        <select name="gender" class="form-select" >
                                            <option value="">Select</option>
                                            <option value="Male" {{ $customer->Gender == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ $customer->Gender == 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Other" {{ $customer->Gender == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control"
                                               value="{{ $customer->First_name }}" required>
                                    </div>
                                </div>

                                <div class="col-md-4" style="display: none">
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control"
                                               value="{{ $customer->Middle_name }}">
                                    </div>
                                </div>

                                <div class="col-md-4" style="display: none">
                                    <div class="form-group">
                                        <label>Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control"
                                               value="{{ $customer->Last_name }}" >
                                    </div>
                                </div>

                                <div class="col-md-6" style="display: none">
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" name="name" class="form-control"
                                               value="{{ $customer->Name }}">
                                    </div>
                                </div>

                                <div class="col-md-4" style="display: none">
                                    <div class="form-group">
                                        <label>Customer Code</label>
                                        <input type="text" name="code" class="form-control"
                                               value="{{ $customer->Code }}" >
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="col-md-12 mt-2">
                                    <p class="cu-section-title">Contact Information</p>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contact 1 <span class="text-danger">*</span></label>
                                        <input type="text" name="contact_1" class="form-control"
                                               value="{{ $customer->Contact_1 }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contact 2</label>
                                        <input type="text" name="contact_2" class="form-control"
                                               value="{{ $customer->Contact_2 }}">
                                    </div>
                                </div>

                                <!-- Address Information -->
                                <div class="col-md-12 mt-2">
                                    <p class="cu-section-title">Address Information</p>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address 1 <span class="text-danger">*</span></label>
                                        <textarea name="address_1" class="form-control" rows="2" required>{{ $customer->Address_1 }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-6" style="display: none">
                                    <div class="form-group">
                                        <label>City 1 <span class="text-danger">*</span></label>
                                        <input type="text" name="city_1" class="form-control"
                                               value="{{ $customer->City_1 }}" >
                                    </div>
                                </div>

                                <div class="col-md-6" style="display: none">
                                    <div class="form-group">
                                        <label>Address 2</label>
                                        <textarea name="address_2" class="form-control" rows="2">{{ $customer->Address_2 }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-6" style="display: none">
                                    <div class="form-group">
                                        <label>City 2</label>
                                        <input type="text" name="city_2" class="form-control"
                                               value="{{ $customer->City_2 }}">
                                    </div>
                                </div>

                                <!-- Identification Information -->
                                <div class="col-md-12 mt-2">
                                    <p class="cu-section-title">Identification Information</p>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>NIC <span class="text-danger">*</span></label>
                                        <input type="text" name="nic" class="form-control"
                                               value="{{ $customer->NIC }}" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Driving License</label>
                                        <input type="text" name="driving_license" class="form-control"
                                               value="{{ $customer->Driving_license }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Passport</label>
                                        <input type="text" name="passport" class="form-control"
                                               value="{{ $customer->Passport }}">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Other Identifications</label>
                                        <textarea name="other_identifications" class="form-control" rows="2">{{ $customer->Other_identifications }}</textarea>
                                    </div>
                                </div>


                                                <!-- Pawn Summary -->
                <div class="cu-card">
                    <div class="card-header">
                        <h5><i class="fas fa-coins me-2"></i>Pawn Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="cu-stat">
                                    <div class="cu-stat-label">Current Pawning Amount Total</div>
                                    <div class="cu-stat-value">
                                        {{ number_format($pawnStats->total_pawn_amount ?? 0, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="cu-stat">
                                    <div class="cu-stat-label">Pawn Count</div>
                                    <div class="cu-stat-value">
                                        {{ number_format($pawnStats->pawn_count ?? 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                                <!-- Status and Additional Fields -->
                                <div class="col-md-12 mt-2">
                                    <p class="cu-section-title">Status &amp; Limits</p>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="1" {{ $customer->Status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $customer->Status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Limit Amount</label>
                                        <input type="number" step="0.01" min="0" name="limit_amount" class="form-control"
                                               value="{{ $customer->Limit_Amount }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Limit Pawn Count</label>
                                        <input type="number" step="1" min="0" name="limit_pawn_count" class="form-control"
                                               value="{{ $customer->Limit_Pawn_Count }}">
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-md-12 mt-4 pt-2" style="border-top: 1px solid var(--cu-border);">
                                    <button type="submit" class="btn cu-btn-primary">
                                        <i class="fas fa-save"></i> Update Customer
                                    </button>
                                    <a href="{{ route('customerUpdatestatus') }}" class="btn cu-btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="cu-not-found">
                    <i class="fas fa-user-slash me-2"></i> No customer found for this NIC.
                </div>
            @endif
        @endif

    </div>
</div>

<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/feather.min.js"></script>
<script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="assets/plugins/apexchart/apexcharts.min.js"></script>
<script src="assets/plugins/apexchart/chart-data.js"></script>
<script src="assets/js/script.js"></script>
@endsection