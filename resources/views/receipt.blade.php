@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <meta name="csrf-token" content="{{ csrf_token() }}" />

           <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
          <script src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

        <title>Receipt Type Management</title>

        <style>
            .card {
                border: none;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .card-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 10px 10px 0 0 !important;
                padding: 20px;
            }

            .form-section {
                background: #f8f9fa;
                padding: 25px;
                border-radius: 10px;
                margin-bottom: 20px;
            }

            .section-title {
                color: #667eea;
                font-weight: 600;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #667eea;
            }

            .form-label {
                font-weight: 500;
                color: #495057;
                margin-bottom: 8px;
            }

            .required-asterisk {
                color: #dc3545;
                font-weight: bold;
            }

            .input-group-text {
                background-color: #667eea;
                color: white;
                border: 1px solid #667eea;
                font-weight: 500;
            }

            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            }

            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                padding: 12px 30px;
                font-weight: 500;
                transition: all 0.3s;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }

            .table-wrapper {
                background: white;
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            }

            table.dataTable thead th {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-weight: 500;
                text-align: center;
                padding: 15px 10px;
                border: none;
            }

            table.dataTable tbody td {
                vertical-align: middle;
                padding: 12px 10px;
            }

            .rate-cell-1 {
                background-color: #ffe5e5 !important;
            }

            .rate-cell-2 {
                background-color: #e5e5ff !important;
            }

            .rate-cell-3 {
                background-color: #e5fff5 !important;
            }

            .postage-cell {
                background-color: #e3f2fd !important;
            }

            .badge-rate {
                padding: 6px 12px;
                border-radius: 20px;
                font-weight: 500;
            }

            .action-buttons {
                display: flex;
                gap: 5px;
                justify-content: center;
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 0.875rem;
            }

            .modal-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .modal-content {
                border-radius: 15px;
                overflow: hidden;
            }

            .dataTables_wrapper .dataTables_filter input {
                border-radius: 20px;
                padding: 8px 15px;
                border: 1px solid #ced4da;
            }

            .dataTables_wrapper .dataTables_length select {
                border-radius: 5px;
                padding: 5px 10px;
                border: 1px solid #ced4da;
            }

            .page-item.active .page-link {
                background-color: #667eea;
                border-color: #667eea;
            }

            .alert {
                border-radius: 10px;
                border: none;
            }

            .errMsgContainer1, .errMsgContainer2 {
                margin-bottom: 15px;
            }

            .errMsgContainer1 span, .errMsgContainer2 span {
                display: block;
                padding: 8px;
                background-color: #f8d7da;
                color: #721c24;
                border-radius: 5px;
                margin-bottom: 5px;
            }
        </style>
    </head>
    <body>
        <div class="main-wrapper">
            <div class="page-wrapper">
                <div class="content container-fluid">

                    <!-- Page Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="mb-0"><i class="fas fa-receipt me-2"></i>Receipt Type Management</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alert Section --}}
                    <div class="row">
                        <div class="col-12">
                            @if (session('delete'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{session('delete')}}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif

                            @if (session('added'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{session('added')}}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif

                            @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Add Receipt Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="errMsgContainer1"></div>
                                    <form action="" id="addReceipt" method="post">
                                        @include('partials.receipt-penalty-intervals', ['prefix' => ''])
                                        @csrf

                                        <!-- Basic Information -->
                                        <div class="form-section">
                                            <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Receipt Type Name <span class="required-asterisk">*</span></label>
                                                    <input type="text" class="form-control" placeholder="Enter receipt type name"
                                                        name="receiptname" id="receiptname" value="{{ old('receiptname') }}"
                                                        required maxlength="80">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Effective From <span class="required-asterisk">*</span></label>
                                                    <input type="date" class="form-control" name="effective_from" id="effective_from"
                                                        value="{{ date('Y-m-d') }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Valid Period <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Enter valid period"
                                                            name="valid_period" id="valid_period" value="{{ old('valid_period') }}"
                                                            required maxlength="80">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Service Charge <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Enter service charge"
                                                            name="service_charge" id="service_charge" value="{{ old('service_charge') }}"
                                                            required maxlength="10">
                                                        <span class="input-group-text">Rs.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Rate Setup -->
                                        <div class="form-section">
                                            <h5 class="section-title"><i class="fas fa-percentage me-2"></i>Rate Setup</h5>

                                            <!-- Rate 1 -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Rate 1 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="First period interest rate"
                                                            id="rate1" name="rate1" value="{{ old('rate1') }}" required maxlength="10">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Period 1 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="First period duration"
                                                            id="period1" name="period1" value="{{ old('period1') }}" required maxlength="10">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Rate 2 -->
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Rate 2 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Second period interest rate"
                                                            id="rate2" name="rate2" value="{{ old('rate2') }}" required maxlength="10">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Period 2 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Second period duration"
                                                            id="period2" name="period2" value="{{ old('period2') }}" required maxlength="10">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Rate 3 -->
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Rate 3</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Third period interest rate"
                                                            id="rate3" name="rate3" value="{{ old('rate3') }}" maxlength="10">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Period 3 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Third period duration"
                                                            id="period3" name="period3" value="{{ old('period3') }}" required maxlength="10">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Service Charges -->
                                        <div class="form-section">
                                            <h5 class="section-title"><i class="fas fa-money-bill-wave me-2"></i>Additional Charges</h5>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Less than 25000 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" id="service_charge_less" name="service_charge_less"
                                                            placeholder="Charge for amounts < 25000"
                                                            class="form-control" value="{{ old('service_charge_less') }}">
                                                        <span class="input-group-text">Rs.</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Greater than 25000 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" id="service_charge_greater" name="service_charge_greater"
                                                            placeholder="Rate for amounts > 25000"
                                                            class="form-control" value="{{ old('service_charge_greater') }}">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Postage Charge <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" id="Postage_charge" name="Postage_charge"
                                                            placeholder="Postage charge amount"
                                                            class="form-control" value="{{ old('Postage_charge') }}">
                                                        <span class="input-group-text">Rs.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button class="btn btn-primary add_receipt" type="button">
                                                <i class="fas fa-save me-2"></i>Save Receipt Type
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Receipt Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-wrapper">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0 text-primary"><i class="fas fa-list me-2"></i>Receipt Types (Current)</h5>
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm receipt-history-button" data-type="" data-bs-toggle="modal" data-bs-target="#receiptHistoryModal"><i class="fas fa-history me-1"></i>All history</button>
                                    </div>
                                </div>
                                <div class="table-data">
                                    <table id="receiptTable" class="table table-striped table-bordered table-hover nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">Type Name</th>
                                                <th rowspan="2">Effective</th>
                                                <th rowspan="2">Status</th>
                                                <th rowspan="2">Service Charge</th>
                                                <th colspan="2" class="rate-cell-1">Rate 1</th>
                                                <th colspan="2" class="rate-cell-2">Rate 2</th>
                                                <th colspan="2" class="rate-cell-3">Rate 3</th>
                                                <th rowspan="2" class="postage-cell">Postage</th>
                                                <th rowspan="2">Valid Days</th>
                                                <th rowspan="2">Pawn Amount</th>
                                                <th colspan="2">Service Charge Bands</th>
                                                <th rowspan="2">Penalty Intervals (days)</th>
                                                <th rowspan="2">Action</th>
                                            </tr>
                                            <tr>
                                                <th class="rate-cell-1">Rate %</th>
                                                <th class="rate-cell-1">Days</th>
                                                <th class="rate-cell-2">Rate %</th>
                                                <th class="rate-cell-2">Days</th>
                                                <th class="rate-cell-3">Rate %</th>
                                                <th class="rate-cell-3">Days</th>
                                                <th>< 25000</th>
                                                <th>> 25000</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($Recei_Add as $receipt)
                                            <tr>
                                                <td><strong>{{$receipt->receiptname}}</strong></td>
                                                <td>
                                                    <small class="text-nowrap">From: {{ $receipt->effective_from ? \Carbon\Carbon::parse($receipt->effective_from)->format('Y-m-d') : 'All Time' }}</small>
                                                    @if($receipt->effective_to)
                                                        <br><small class="text-muted text-nowrap">To: {{ \Carbon\Carbon::parse($receipt->effective_to)->format('Y-m-d') }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(($receipt->is_active ?? 1) && !$receipt->effective_to)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Archived</span>
                                                    @endif
                                                </td>
                                                <td>Rs. {{number_format($receipt->service_charge ?? 0, 2)}}</td>
                                                <td class="rate-cell-1">{{$receipt->rate1}}%</td>
                                                <td class="rate-cell-1">{{$receipt->period1}}</td>
                                                <td class="rate-cell-2">{{$receipt->rate2}}%</td>
                                                <td class="rate-cell-2">{{$receipt->period2}}</td>
                                                <td class="rate-cell-3">{{$receipt->rate3}}%</td>
                                                <td class="rate-cell-3">{{$receipt->period3}}</td>
                                                <td class="postage-cell">Rs. {{number_format($receipt->Postage_charge ?? 0, 2)}}</td>
                                                <td>{{$receipt->validPeriod}}</td>
                                                <td>{{$receipt->pawn_amount}}</td>

                                                <td>Rs. {{number_format($receipt->s_charge_less ?? 0, 2)}}</td>
                                                <td>{{$receipt->s_charge_greater}}%</td>
                                                <td>
                                                    1st: {{ $receipt->letter_1_days ?? 21 }} /
                                                     2nd: {{ $receipt->letter_2_days ?? 21 }} /
                                                    3rd: {{ $receipt->letter_3_days ?? 21 }} /
                                                    Reminder: {{ $receipt->forfeit_reminder_days ?? 21 }}
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn btn-sm btn-outline-primary receipt-history-button" data-type="{{ $receipt->receiptname }}" data-bs-toggle="modal" data-bs-target="#receiptHistoryModal" title="View saved versions"><i class="fas fa-history"></i></button>
                                                        <a href="#" class="btn btn-sm btn-success update_receipt_form"
                                                            data-bs-toggle="modal" data-bs-target="#updateReceiptModel"
                                                            data-letter-1-days="{{ $receipt->letter_1_days ?? 21 }}"
                                                            data-letter-2-days="{{ $receipt->letter_2_days ?? 21 }}"
                                                            data-letter-3-days="{{ $receipt->letter_3_days ?? 21 }}"
                                                            data-forfeit-reminder-days="{{ $receipt->forfeit_reminder_days ?? 21 }}"
                                                            data-id="{{$receipt->id}}"
                                                            data-receiptname="{{$receipt->receiptname}}"
                                                            data-effective-from="{{ $receipt->effective_from ? \Carbon\Carbon::parse($receipt->effective_from)->format('Y-m-d') : date('Y-m-d') }}"
                                                            data-rate1="{{$receipt->rate1}}"
                                                            data-period1="{{$receipt->period1}}"
                                                            data-rate2="{{$receipt->rate2}}"
                                                            data-period2="{{$receipt->period2}}"
                                                            data-rate3="{{$receipt->rate3}}"
                                                            data-period3="{{$receipt->period3}}"
                                                            data-valid="{{$receipt->validPeriod}}"
                                                            data-s_char_less="{{$receipt->s_charge_less}}"
                                                            data-s_char_grea="{{$receipt->s_charge_greater}}"
                                                            data-postage-charge="{{$receipt->Postage_charge}}"
                                                            data-service-charge="{{$receipt->service_charge}}">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-sm btn-danger delete_receipt"
                                                            data-id="{{$receipt->id}}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="receiptHistoryModal" tabindex="-1" aria-labelledby="receiptHistoryTitle" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title" id="receiptHistoryTitle">Receipt type change history</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                            <div class="modal-body" id="receiptHistoryContent" aria-live="polite">Loading saved versions…</div>
                        </div></div>
                    </div>

                    {{-- Update Receipt Modal --}}
                    <div class="modal fade" id="updateReceiptModel" tabindex="-1">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Receipt Type</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="errMsgContainer2"></div>
                                    <form action="" method="post" id="updateReceipt">
                                        @include('partials.receipt-penalty-intervals', ['prefix' => 'up_'])
                                        @csrf
                                        <input type="hidden" id="up_id" name="up_id">

                                        <!-- Basic Information -->
                                        <div class="form-section">
                                            <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Receipt Type Name <span class="required-asterisk">*</span></label>
                                                    <input type="text" class="form-control" placeholder="Receipt Type Name"
                                                        name="up_receiptname" id="up_receiptname" required maxlength="80">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Effective Date <span class="required-asterisk">*</span></label>
                                                    <input type="date" class="form-control" name="up_effective_from" id="up_effective_from"
                                                        value="{{ date('Y-m-d') }}" required>
                                                    <small class="text-muted">Takes effect from this date</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Valid Period <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Valid Period"
                                                            name="up_valid_period" id="up_valid_period" required maxlength="80">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Service Charge <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" placeholder="Service Charge"
                                                            name="up_service_charge" id="up_service_charge" required maxlength="10">
                                                        <span class="input-group-text">Rs.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Rate Setup -->
                                        <div class="form-section">
                                            <h5 class="section-title"><i class="fas fa-percentage me-2"></i>Rate Setup</h5>

                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Rate 1 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="up_rate1" name="up_rate1" required maxlength="10">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Period 1 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="up_period1" name="up_period1" required maxlength="10">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Rate 2 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="up_rate2" name="up_rate2" required maxlength="10">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Period 2 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="up_period2" name="up_period2" required maxlength="10">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Rate 3</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="up_rate3" name="up_rate3" maxlength="10">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Period 3 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="up_period3" name="up_period3" required maxlength="10">
                                                        <span class="input-group-text">Days</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Additional Charges -->
                                        <div class="form-section">
                                            <h5 class="section-title"><i class="fas fa-money-bill-wave me-2"></i>Additional Charges</h5>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Less than 25000 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" id="up_service_charge_less" name="up_service_charge_less" class="form-control">
                                                        <span class="input-group-text">Rs.</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Greater than 25000 <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" id="up_service_charge_greater" name="up_service_charge_greater" class="form-control">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Postage Charge <span class="required-asterisk">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" id="up_Postage_charge" name="up_Postage_charge" class="form-control">
                                                        <span class="input-group-text">Rs.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-success update_receipt">
                                                <i class="fas fa-check me-2"></i>Update Receipt
                                            </button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-2"></i>Close
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

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

        <!-- DataTables -->
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

        <script type="text/javascript">
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).ready(function(){
                $(document).on('click', '.receipt-history-button', function () {
                    const name = $(this).attr('data-type') || '';
                    $('#receiptHistoryTitle').text(name ? name + ' — change history' : 'All receipt type history');
                    $('#receiptHistoryContent').text('Loading saved versions…');
                    $.get('{{ route('receipt_type_history') }}', name ? {name: name} : {})
                        .done(function (html) { $('#receiptHistoryContent').html(html); })
                        .fail(function () { $('#receiptHistoryContent').text('History could not be loaded. Please retry.'); });
                });
                // Initialize DataTable
                var table = $('#receiptTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            className: 'btn btn-success btn-sm',
                            text: '<i class="fas fa-file-excel me-1"></i>Excel',
                            exportOptions: {
                                columns: ':not(:last-child)'
                            }
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-danger btn-sm',
                            text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                            exportOptions: {
                                columns: ':not(:last-child)'
                            }
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-info btn-sm',
                            text: '<i class="fas fa-print me-1"></i>Print',
                            exportOptions: {
                                columns: ':not(:last-child)'
                            }
                        }
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search receipts..."
                    }
                });

                // Add new receipt
                $(document).on('click','.add_receipt', function(e){
                    e.preventDefault();
                    let formData = {
                        "_token": "{{ csrf_token() }}",
                        receiptname: $('#receiptname').val(),
                        effective_from: $('#effective_from').val(),
                        rate1: $('#rate1').val(),
                        period1: $('#period1').val(),
                        rate2: $('#rate2').val(),
                        period2: $('#period2').val(),
                        rate3: $('#rate3').val(),
                        period3: $('#period3').val(),
                        valid: $('#valid_period').val(),
                        s_char_less: $('#service_charge_less').val(),
                        s_char_grea: $('#service_charge_greater').val(),
                        Postage_charge: $('#Postage_charge').val(),
                        service_charge: $('#service_charge').val(),
                        letter_1_days: $('#letter_1_days').val(),
                        letter_2_days: $('#letter_2_days').val(),
                        letter_3_days: $('#letter_3_days').val(),
                        forfeit_reminder_days: $('#forfeit_reminder_days').val()
                    };

                    $.ajax({
                        url:"{{ route('add_receipt_ajax') }}",
                        method: 'post',
                        data: formData,
                        success:function(res){
                            if(res.status=='success'){
                                $('#addReceipt')[0].reset();
                                location.reload();
                                toastr.success("Receipt Added Successfully!", "Success", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-top-right",
                                    timeOut: 5000
                                });
                            }
                        },
                        error:function(err){
                            $('.errMsgContainer1').html('');
                            let error = err.responseJSON;
                            $.each(error.errors,function(index, value){
                                $('.errMsgContainer1').append('<span class="text-danger">'+value+'</span>');
                            });
                        }
                    });
                });

                // Show receipt details in update form
                $(document).on('click','.update_receipt_form',function(){
                    $('#up_id').val($(this).data('id'));
                    $('#up_receiptname').val($(this).data('receiptname'));
                    $('#up_effective_from').val('{{ date("Y-m-d") }}');
                    $('#up_rate1').val($(this).data('rate1'));
                    $('#up_period1').val($(this).data('period1'));
                    $('#up_rate2').val($(this).data('rate2'));
                    $('#up_period2').val($(this).data('period2'));
                    $('#up_rate3').val($(this).data('rate3'));
                    $('#up_period3').val($(this).data('period3'));
                    $('#up_valid_period').val($(this).data('valid'));
                    $('#up_service_charge_less').val($(this).data('s_char_less'));
                    $('#up_service_charge_greater').val($(this).data('s_char_grea'));
                    $('#up_Postage_charge').val($(this).data('postage-charge'));
                    $('#up_service_charge').val($(this).data('service-charge'));
                    $('#up_letter_1_days').val($(this).attr('data-letter-1-days') ?? 21);
                    $('#up_letter_2_days').val($(this).attr('data-letter-2-days') ?? 21);
                    $('#up_letter_3_days').val($(this).attr('data-letter-3-days') ?? 21);
                    $('#up_forfeit_reminder_days').val($(this).attr('data-forfeit-reminder-days') ?? 21);
                });

                // Update receipt
                $(document).on('click','.update_receipt',function(e){
                    e.preventDefault();
                    let formData = {
                        "_token": "{{ csrf_token() }}",
                        up_id: $('#up_id').val(),
                        up_receiptname: $('#up_receiptname').val(),
                        up_effective_from: $('#up_effective_from').val(),
                        up_rate1: $('#up_rate1').val(),
                        up_rate2: $('#up_rate2').val(),
                        up_rate3: $('#up_rate3').val(),
                        up_period1: $('#up_period1').val(),
                        up_period2: $('#up_period2').val(),
                        up_period3: $('#up_period3').val(),
                        up_valid: $('#up_valid_period').val(),
                        up_s_char_less: $('#up_service_charge_less').val(),
                        up_s_char_grea: $('#up_service_charge_greater').val(),
                        up_Postage_charge: $('#up_Postage_charge').val(),
                        up_service_charge: $('#up_service_charge').val(),
                        up_letter_1_days: $('#up_letter_1_days').val(),
                        up_letter_2_days: $('#up_letter_2_days').val(),
                        up_letter_3_days: $('#up_letter_3_days').val(),
                        up_forfeit_reminder_days: $('#up_forfeit_reminder_days').val()
                    };

                    $.ajax({
                        url:"{{ route('update_receipt_ajax') }}",
                        method: 'post',
                        data: formData,
                        success:function(res){
                            if(res.status=='success'){
                                $("#updateReceiptModel").modal('hide');
                                location.reload();
                                toastr.success("Receipt Updated Successfully!", "Success", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-top-right",
                                    timeOut: 5000
                                });
                            }
                        },
                        error:function(err){
                            $('.errMsgContainer2').html('');
                            let error = err.responseJSON;
                            $.each(error.errors,function(index, value){
                                $('.errMsgContainer2').append('<span class="text-danger">'+value+'</span>');
                            });
                        }
                    });
                });

                // Delete receipt
                $(document).on('click','.delete_receipt',function(e){
                    e.preventDefault();
                    let receipt_id = $(this).data('id');

                    if(confirm('Are you sure you want to delete this receipt type?')){
                        $.ajax({
                            url:"{{ route('delete_receipt_ajax') }}",
                            method: 'post',
                            data:{"_token": "{{ csrf_token() }}", receipt_id: receipt_id},
                            success:function(res){
                                if(res.status=='success'){
                                    location.reload();
                                    toastr.success("Receipt Deleted Successfully!", "Success", {
                                        closeButton: true,
                                        progressBar: true,
                                        positionClass: "toast-top-right",
                                        timeOut: 5000
                                    });
                                }
                            }
                        });
                    }
                });
            });
        </script>
    </body>
@endsection



    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/plugins/select2/js/select2.min.js"></script>
    <script src="assets/plugins/moment/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>
    



</html>
