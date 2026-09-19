@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
        <title>Late Letters</title>

        <style>
            .letter-date { white-space: nowrap; }
            /* ── Letter Tab Colours ── */

/* 1st Letter — Green (no letters sent yet) */
.tab-1st          { color: #1a7a4a !important; border: 2px solid transparent !important; background: #eafaf1 !important; }
.tab-1st:hover    { background: #c8f0d8 !important; border-color: #1a7a4a !important; }
.tab-1st.active   { color: #fff !important; background: #1a7a4a !important;
                    border-color: #1a7a4a #1a7a4a #1a7a4a !important;
                    box-shadow: 0 -3px 0 #0f5132 inset; }

/* 2nd Letter — Blue (1st letter already sent) */
.tab-2nd          { color: #0d4fa0 !important; border: 2px solid transparent !important; background: #e8f0fd !important; }
.tab-2nd:hover    { background: #c5d9f9 !important; border-color: #0d4fa0 !important; }
.tab-2nd.active   { color: #fff !important; background: #0d4fa0 !important;
                    border-color: #0d4fa0 #0d4fa0 #0d4fa0 !important;
                    box-shadow: 0 -3px 0 #083580 inset; }

/* 3rd Letter — Red (most urgent) */
.tab-3rd          { color: #b80000 !important; border: 2px solid transparent !important; background: #fdeaea !important; }
.tab-3rd:hover    { background: #f5c0c0 !important; border-color: #b80000 !important; }
.tab-3rd.active   { color: #fff !important; background: #b80000 !important;
                    border-color: #b80000 #b80000 #b80000 !important;
                    box-shadow: 0 -3px 0 #7a0000 inset; }

/* Remove the default Bootstrap bottom-border overlap */
.nav-tabs .nav-link.active { border-bottom-color: transparent !important; }

/* Subtle badge-style count on each tab (optional) */
.letter-tab .tab-badge {
    display: inline-block;
    background: rgba(255,255,255,.35);
    color: inherit;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    padding: 1px 7px;
    margin-left: 6px;
}
.letter-tab.active .tab-badge { background: rgba(0,0,0,.20); color: #fff; }
            /* Highlight selected rows */
            tr.row-selected td { background-color: #d1ecf1 !important; }

            /* Make checkboxes a bit larger */
            .row-check { width: 18px; height: 18px; cursor: pointer; }

            /* Sticky "Print Selected" bar at the bottom of each tab */
            .bulk-action-bar {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 8px 4px;
                border-top: 1px solid #dee2e6;
                margin-top: 8px;
            }
            .bulk-action-bar .selected-count {
                font-size: .875rem;
                color: #5f6472;
            }
            .late-letter-table-wrap { width: 100%; overflow: visible; }
            .late-letter-table { width: 100%; table-layout: fixed; margin-bottom: 0; }
            .late-letter-table th, .late-letter-table td {
                padding: .55rem .35rem;
                white-space: normal;
                overflow-wrap: anywhere;
                vertical-align: middle;
                font-size: .82rem;
            }
            .late-letter-table .letter-date { white-space: nowrap; }
            .letter-manage-cell { display: flex; flex-direction: column; align-items: center; gap: .4rem; }
            .letter-manage-cell .btn { padding: .22rem .42rem; }
            .late-letter-detail-row > td { padding: 0 !important; background: #f7f8fc; }
            .late-letter-details { padding: .85rem 1rem; border-left: 4px solid #6f42c1; }
            .late-letter-details-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .55rem; }
            .late-letter-detail-item { background: #fff; border: 1px solid #e1e4eb; border-radius: 6px; padding: .55rem; }
            .late-letter-detail-item small { display: block; color: #73788a; }

            #tbl-1 .summary-row > :nth-child(2), #tbl-1 .summary-row > :nth-child(7),
            #tbl-1 .summary-row > :nth-child(11), #tbl-1 .summary-row > :nth-child(12),
            #tbl-1 .summary-row > :nth-child(13) { display: none; }
            #tbl-2 .summary-row > :nth-child(2), #tbl-2 .summary-row > :nth-child(7),
            #tbl-2 .summary-row > :nth-child(8), #tbl-2 .summary-row > :nth-child(12),
            #tbl-2 .summary-row > :nth-child(13), #tbl-2 .summary-row > :nth-child(14),
            #tbl-3 .summary-row > :nth-child(2), #tbl-3 .summary-row > :nth-child(7),
            #tbl-3 .summary-row > :nth-child(8), #tbl-3 .summary-row > :nth-child(12),
            #tbl-3 .summary-row > :nth-child(13), #tbl-3 .summary-row > :nth-child(14) { display: none; }

            .late-letter-table .summary-row > :nth-child(1) { width: 9%; }
            .late-letter-table .summary-row > :nth-child(3) { width: 17%; }
            .late-letter-table .summary-row > :nth-child(4) { width: 10%; }
            .late-letter-table .summary-row > :nth-child(5) { width: 8%; }
            .late-letter-table .summary-row > :nth-child(6) { width: 10%; }
            #tbl-1 .summary-row > :nth-child(8), #tbl-2 .summary-row > :nth-child(9), #tbl-3 .summary-row > :nth-child(9) { width: 10%; }
            #tbl-1 .summary-row > :nth-child(9), #tbl-2 .summary-row > :nth-child(10), #tbl-3 .summary-row > :nth-child(10) { width: 12%; }
            #tbl-1 .summary-row > :nth-child(10), #tbl-2 .summary-row > :nth-child(11), #tbl-3 .summary-row > :nth-child(11) { width: 8%; }
            #tbl-1 .summary-row > :nth-child(14), #tbl-2 .summary-row > :nth-child(15), #tbl-3 .summary-row > :nth-child(15) { width: 9%; }
            #tbl-1 .summary-row > :nth-child(15), #tbl-2 .summary-row > :nth-child(16), #tbl-3 .summary-row > :nth-child(16) { width: 7%; }
            @media (max-width: 1100px) {
                .late-letter-table th, .late-letter-table td { padding: .45rem .25rem; font-size: .72rem; }
                .late-letter-details-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                .letter-manage-cell .btn { font-size: .68rem; }
            }
        </style>
    </head>
    <body>
        <div class="main-wrapper">
            <div class="page-wrapper">
                <div class="content container-fluid">
                    <div class="card shadow">
                        <div class="col-md-9">
                            <h4 class="card-title m-3">Late Letters</h4>
                        </div>
                        <hr size="6" style="color: blue">

                        {{-- Alert section --}}
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

                        <div class="row">
                            <div class="col-md-1"></div>
                            <div class="col">
                                <div class="card-body">
                                    <div class="late-letter-content">
                                        <div class="table-data">
                                            <div class="card shadow p-3 mb-3 bg-body-tertiary rounded">

                                                <a href="{{ route('lateRedeemLetterList') }}" class="btn btn-info mb-1" style="width:fit-content;">
                                                    Print List <i class="fa fa-print"></i>
                                                </a>

                                                <form method="GET" action="{{ route('pawning_late_letters') }}" class="row g-2 my-3 align-items-end">
                                                    <div class="col-md-4">
                                                        <label class="form-label" for="receipt_number">Receipt Number</label>
                                                        <input class="form-control" id="receipt_number" name="receipt_number" value="{{ request('receipt_number') }}" placeholder="Find a receipt">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="receipt_type">Receipt Type</label>
                                                        <select class="form-control" id="receipt_type" name="receipt_type">
                                                            <option value="">All types</option>
                                                            @foreach($receiptType as $type)
                                                                <option value="{{ $type->receiptname }}" @selected(request('receipt_type') == $type->receiptname)>{{ $type->receiptname }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-auto"><button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button></div>
                                                    <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ route('pawning_late_letters') }}">Clear</a></div>
                                                </form>

                                                <br><br>

                                                {{-- ── TAB NAV ── --}}
                                      {{-- ── TAB NAV ── --}}
                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist"
                                        style="border-bottom: 3px solid #1a3c5e; gap: 6px;">

                                        <li class="nav-item">
                                            <a class="nav-link active letter-tab tab-1st" data-bs-toggle="tab" href="#tab-1st-letter"
                                            style="border-radius: 8px 8px 0 0; font-weight: 600; letter-spacing: .3px;
                                                    padding: 10px 22px; transition: all .25s ease;">
                                                <i class="fas fa-envelope me-2"></i>1st Letter
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link letter-tab tab-2nd" data-bs-toggle="tab" href="#tab-2nd-letter"
                                            style="border-radius: 8px 8px 0 0; font-weight: 600; letter-spacing: .3px;
                                                    padding: 10px 22px; transition: all .25s ease;">
                                                <i class="fas fa-envelope me-2"></i>2nd Letter
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link letter-tab tab-3rd" data-bs-toggle="tab" href="#tab-3rd-letter"
                                            style="border-radius: 8px 8px 0 0; font-weight: 600; letter-spacing: .3px;
                                                    padding: 10px 22px; transition: all .25s ease;">
                                                <i class="fas fa-envelope me-2"></i>3rd Letter
                                            </a>
                                        </li>

                                    </ul>

                                                {{-- ── TAB CONTENT ── --}}
                                                <div class="tab-content mt-3">

                                                    {{-- ════════════════════════
                                                         1st Letter Tab
                                                    ════════════════════════ --}}
                                                    <div class="tab-pane fade show active" id="tab-1st-letter" role="tabpanel">
                                                        <div class="late-letter-table-wrap">
                                                            <table class="table table-center table-bordered table-hover late-letter-table" id="tbl-1">
                                                                <thead>
                                                                    <tr class="styled-table text-center summary-row">
                                                                        {{-- Select-all checkbox --}}
                                                                        <th>Manage<br>
                                                                            <input type="checkbox" class="row-check select-all-check"
                                                                                data-target="chk-1"
                                                                                title="Select all">
                                                                        </th>
                                                                        <th>NIC</th>
                                                                        <th>Customer</th>
                                                                        <th>Phone</th>
                                                                        <th>Receipt No</th>
                                                                        <th>Receipt Type</th>
                                                                        <th>Date</th>
                                                                        <th>Final Date</th>
                                                                        <th>Letter Due Date</th>
                                                                        <th>Amount</th>
                                                                        <th>Interest</th>
                                                                        <th>Service</th>
                                                                        <th>Letter/Postage</th>
                                                                        <th>Total Arrears</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @php $sum_1st = 0; @endphp
                                                                    @foreach ($recipts as $receipts)
                                                                        @if($receipts->is_letter_1 == 0 && $receipts->is_letter_2 == 0)
                                                                            <tr class="summary-row">
                                                                                <td class="text-center">
                                                                                    <div class="letter-manage-cell"><input type="checkbox"
                                                                                        class="row-check chk-1"
                                                                                        data-pawn_sum_id="{{ $receipts->id }}"
                                                                                        data-receipt_no="{{ $receipts->Receipt_Number }}"
                                                                                        data-letter_no="1">
                                                                                        <button type="button" class="btn btn-success btn-sm" data-late-letter-details="letter-1-details-{{ $receipts->id }}" aria-expanded="false"><i class="fa fa-eye"></i> <span>View</span></button></div>
                                                                                </td>
                                                                                <td>{{ $receipts->Customer_NIC }}</td>
                                                                                <td><strong>{{ $receipts->Customer_Name }}</strong><br><small>{{ $receipts->Customer_NIC }}</small></td>
                                                                                <td>{{ $receipts->Customer_Phone }}</td>
                                                                               <td>{{ !empty($receipts->Receipt_Number) ? $receipts->Receipt_Number : $receipts->old_Receipt_Number }}</td>
                                                                                <td>{{ $receipts->Receipt_Type }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->Receipt_Date)->format('Y-m-d') }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->Final_date)->format('Y-m-d') }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->next_letter_due_date)->format('Y-m-d') }}</td>
                                                                                <td class="text-end">{{ $receipts->Amount }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['interest'], 2) }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['service_charge'], 2) }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['letter_charge'], 2) }}</td>
                                                                                <td class="text-end fw-bold">{{ number_format($receipts->financial_breakdown['arrears_total'], 2) }}</td>
                                                                                <td>
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-success print_letter_btn"
                                                                                        data-pawn_sum_id="{{ $receipts->id }}"
                                                                                        data-receipt_no="{{ $receipts->Receipt_Number }}"
                                                                                        data-letter_no="1">
                                                                                        <i class="fa fa-print me-1"></i> Print
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                            <tr id="letter-1-details-{{ $receipts->id }}" class="late-letter-detail-row" hidden><td colspan="15"><div class="late-letter-details"><div class="late-letter-details-grid">
                                                                                <div class="late-letter-detail-item"><small>Receipt date</small><strong>{{ optional($receipts->Receipt_Date)->format('Y-m-d') }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Interest</small><strong>{{ number_format($receipts->financial_breakdown['interest'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Service charge</small><strong>{{ number_format($receipts->financial_breakdown['service_charge'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Letter / postage</small><strong>{{ number_format($receipts->financial_breakdown['letter_charge'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Principal</small><strong>{{ number_format($receipts->Amount, 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Total arrears</small><strong>{{ number_format($receipts->financial_breakdown['arrears_total'], 2) }}</strong></div>
                                                                            </div></div></td></tr>
                                                                            @php $sum_1st += $receipts->Amount; @endphp
                                                                        @endif
                                                                    @endforeach
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="15" class="text-end"><strong>Total principal: {{ number_format($sum_1st, 2) }}</strong></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>

                                                        {{-- Bulk action bar for 1st letter --}}
                                                        <div class="bulk-action-bar">
                                                            <button type="button"
                                                                class="btn btn-success btn-sm print_selected_btn"
                                                                data-checkclass="chk-1"
                                                                data-letter_no="1"
                                                                disabled>
                                                                <i class="fa fa-print me-1"></i> Print Selected (1st Letter)
                                                            </button>
                                                            <span class="selected-count" id="count-1">0 selected</span>
                                                        </div>
                                                    </div>

                                                    {{-- ════════════════════════
                                                         2nd Letter Tab
                                                    ════════════════════════ --}}
                                                    <div class="tab-pane fade" id="tab-2nd-letter" role="tabpanel">
                                                        <div class="late-letter-table-wrap">
                                                            <table class="table table-center table-bordered table-hover late-letter-table" id="tbl-2">
                                                                <thead>
                                                                    <tr class="styled-table text-center summary-row">
                                                                        <th>Manage<br>
                                                                            <input type="checkbox" class="row-check select-all-check"
                                                                                data-target="chk-2"
                                                                                title="Select all">
                                                                        </th>
                                                                        <th>NIC</th>
                                                                        <th>Customer</th>
                                                                        <th>Phone</th>
                                                                        <th>Receipt No</th>
                                                                        <th>Receipt Type</th>
                                                                        <th>Date</th>
                                                                        <th>1st letter Date</th>
                                                                        <th>Final Date</th>
                                                                        <th>Letter Due Date</th>
                                                                        <th>Amount</th>
                                                                        <th>Interest</th>
                                                                        <th>Service</th>
                                                                        <th>Letter/Postage</th>
                                                                        <th>Total Arrears</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @php $sum_2nd = 0; @endphp
                                                                    @foreach ($recipts as $receipts)
                                                                        @if($receipts->is_letter_1 == 1 && $receipts->is_letter_2 == 0)
                                                                            <tr class="summary-row">
                                                                                <td class="text-center">
                                                                                    <div class="letter-manage-cell"><input type="checkbox"
                                                                                        class="row-check chk-2"
                                                                                        data-pawn_sum_id="{{ $receipts->id }}"
                                                                                        data-receipt_no="{{ $receipts->Receipt_Number }}"
                                                                                        data-letter_no="2">
                                                                                        <button type="button" class="btn btn-success btn-sm" data-late-letter-details="letter-2-details-{{ $receipts->id }}" aria-expanded="false"><i class="fa fa-eye"></i> <span>View</span></button></div>
                                                                                </td>
                                                                                <td>{{ $receipts->Customer_NIC }}</td>
                                                                                <td><strong>{{ $receipts->Customer_Name }}</strong><br><small>{{ $receipts->Customer_NIC }}</small></td>
                                                                                <td>{{ $receipts->Customer_Phone }}</td>
                                                                                <td>{{ $receipts->Receipt_Number }}</td>
                                                                                <td>{{ $receipts->Receipt_Type }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->Receipt_Date)->format('Y-m-d') }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->letter_1_date)->format('Y-m-d') }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->Final_date)->format('Y-m-d') }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->next_letter_due_date)->format('Y-m-d') }}</td>
                                                                                <td class="text-end">{{ $receipts->Amount }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['interest'], 2) }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['service_charge'], 2) }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['letter_charge'], 2) }}</td>
                                                                                <td class="text-end fw-bold">{{ number_format($receipts->financial_breakdown['arrears_total'], 2) }}</td>
                                                                                <td>
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-primary print_letter_btn"
                                                                                        data-pawn_sum_id="{{ $receipts->id }}"
                                                                                        data-receipt_no="{{ $receipts->Receipt_Number }}"
                                                                                        data-letter_no="2">
                                                                                        <i class="fa fa-print me-1"></i> Print
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                            <tr id="letter-2-details-{{ $receipts->id }}" class="late-letter-detail-row" hidden><td colspan="16"><div class="late-letter-details"><div class="late-letter-details-grid">
                                                                                <div class="late-letter-detail-item"><small>Receipt date</small><strong>{{ optional($receipts->Receipt_Date)->format('Y-m-d') }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>1st letter date</small><strong>{{ optional($receipts->letter_1_date)->format('Y-m-d') ?? '—' }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Interest</small><strong>{{ number_format($receipts->financial_breakdown['interest'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Service charge</small><strong>{{ number_format($receipts->financial_breakdown['service_charge'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Letter / postage</small><strong>{{ number_format($receipts->financial_breakdown['letter_charge'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Total arrears</small><strong>{{ number_format($receipts->financial_breakdown['arrears_total'], 2) }}</strong></div>
                                                                            </div></div></td></tr>
                                                                            @php $sum_2nd += $receipts->Amount; @endphp
                                                                        @endif
                                                                    @endforeach
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="16" class="text-end"><strong>Total principal: {{ number_format($sum_2nd, 2) }}</strong></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>

                                                        <div class="bulk-action-bar">
                                                            <button type="button"
                                                                class="btn btn-primary btn-sm print_selected_btn"
                                                                data-checkclass="chk-2"
                                                                data-letter_no="2"
                                                                disabled>
                                                                <i class="fa fa-print me-1"></i> Print Selected (2nd Letter)
                                                            </button>
                                                            <span class="selected-count" id="count-2">0 selected</span>
                                                        </div>
                                                    </div>

                                                    {{-- ════════════════════════
                                                         3rd Letter Tab
                                                    ════════════════════════ --}}
                                                    <div class="tab-pane fade" id="tab-3rd-letter" role="tabpanel">
                                                        <div class="late-letter-table-wrap">
                                                            <table class="table table-center table-bordered table-hover late-letter-table" id="tbl-3">
                                                                <thead>
                                                                    <tr class="styled-table text-center summary-row">
                                                                        <th>Manage<br>
                                                                            <input type="checkbox" class="row-check select-all-check"
                                                                                data-target="chk-3"
                                                                                title="Select all">
                                                                        </th>
                                                                        <th>NIC</th>
                                                                        <th>Customer</th>
                                                                        <th>Phone</th>
                                                                        <th>Receipt No</th>
                                                                        <th>Receipt Type</th>
                                                                        <th>Date</th>
                                                                        <th>2nd letter Date</th>
                                                                        <th>Final Date</th>
                                                                        <th>Letter Due Date</th>
                                                                        <th>Amount</th>
                                                                        <th>Interest</th>
                                                                        <th>Service</th>
                                                                        <th>Letter/Postage</th>
                                                                        <th>Total Arrears</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @php $sum_3rd = 0; @endphp
                                                                    @foreach ($recipts as $receipts)
                                                                        @if($receipts->is_letter_2 == 1 && $receipts->is_letter_3 == 0)
                                                                            <tr class="summary-row">
                                                                                <td class="text-center">
                                                                                    <div class="letter-manage-cell"><input type="checkbox"
                                                                                        class="row-check chk-3"
                                                                                        data-pawn_sum_id="{{ $receipts->id }}"
                                                                                        data-receipt_no="{{ $receipts->Receipt_Number }}"
                                                                                        data-letter_no="3">
                                                                                        <button type="button" class="btn btn-success btn-sm" data-late-letter-details="letter-3-details-{{ $receipts->id }}" aria-expanded="false"><i class="fa fa-eye"></i> <span>View</span></button></div>
                                                                                </td>
                                                                                <td>{{ $receipts->Customer_NIC }}</td>
                                                                                <td><strong>{{ $receipts->Customer_Name }}</strong><br><small>{{ $receipts->Customer_NIC }}</small></td>
                                                                                <td>{{ $receipts->Customer_Phone }}</td>
                                                                                <td>{{ $receipts->Receipt_Number }}</td>
                                                                                <td>{{ $receipts->Receipt_Type }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->Receipt_Date)->format('Y-m-d') }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->letter_2_date)->format('Y-m-d') }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->Final_date)->format('Y-m-d') }}</td>
                                                                                <td class="letter-date">{{ optional($receipts->next_letter_due_date)->format('Y-m-d') }}</td>
                                                                                <td class="text-end">{{ $receipts->Amount }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['interest'], 2) }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['service_charge'], 2) }}</td>
                                                                                <td class="text-end">{{ number_format($receipts->financial_breakdown['letter_charge'], 2) }}</td>
                                                                                <td class="text-end fw-bold">{{ number_format($receipts->financial_breakdown['arrears_total'], 2) }}</td>
                                                                                <td>
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-danger print_letter_btn"
                                                                                        data-pawn_sum_id="{{ $receipts->id }}"
                                                                                        data-receipt_no="{{ $receipts->Receipt_Number }}"
                                                                                        data-letter_no="3">
                                                                                        <i class="fa fa-print me-1"></i> Print
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                            <tr id="letter-3-details-{{ $receipts->id }}" class="late-letter-detail-row" hidden><td colspan="16"><div class="late-letter-details"><div class="late-letter-details-grid">
                                                                                <div class="late-letter-detail-item"><small>Receipt date</small><strong>{{ optional($receipts->Receipt_Date)->format('Y-m-d') }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>2nd letter date</small><strong>{{ optional($receipts->letter_2_date)->format('Y-m-d') ?? '—' }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Interest</small><strong>{{ number_format($receipts->financial_breakdown['interest'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Service charge</small><strong>{{ number_format($receipts->financial_breakdown['service_charge'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Letter / postage</small><strong>{{ number_format($receipts->financial_breakdown['letter_charge'], 2) }}</strong></div>
                                                                                <div class="late-letter-detail-item"><small>Total arrears</small><strong>{{ number_format($receipts->financial_breakdown['arrears_total'], 2) }}</strong></div>
                                                                            </div></div></td></tr>
                                                                            @php $sum_3rd += $receipts->Amount; @endphp
                                                                        @endif
                                                                    @endforeach
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="16" class="text-end"><strong>Total principal: {{ number_format($sum_3rd, 2) }}</strong></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>

                                                        <div class="bulk-action-bar">
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm print_selected_btn"
                                                                data-checkclass="chk-3"
                                                                data-letter_no="3"
                                                                disabled>
                                                                <i class="fa fa-print me-1"></i> Print Selected (3rd Letter)
                                                            </button>
                                                            <span class="selected-count" id="count-3">0 selected</span>
                                                        </div>
                                                    </div>

                                                </div>{{-- end tab-content --}}

                                         
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1"></div>
                        </div>

                        {{-- Hidden table for list printing --}}
                        <div style="display: none">
                            <table class="styled-table text-center" id="LateRedeemtable">
                                <caption>Late Redeem Reminder Receipt</caption>
                                <thead>
                                    <tr>
                                        <th>NIC</th><th>Name</th><th>Phone</th>
                                        <th>Receipt No</th><th>Receipt Type</th><th>Date</th>
                                        <th>Final Date</th><th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recipts as $receipts)
                                        <tr>
                                            <td>{{ $receipts->Customer_NIC }}</td>
                                            <td>{{ $receipts->Customer_Name }}</td>
                                            <td>{{ $receipts->Customer_Phone }}</td>
                                            <td>{{ $receipts->Receipt_Number }}</td>
                                            <td>{{ $receipts->Receipt_Type }}</td>
                                            <td>{{ optional($receipts->Receipt_Date)->format('Y-m-d') }}</td>
                                            <td>{{ optional($receipts->Final_date)->format('Y-m-d') }}</td>
                                            <td class="text-end">{{ $receipts->Amount }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {!! Toastr::message() !!}

        <script type="text/javascript">
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
        </script>

        <script>
        $(document).ready(function () {

            // ════════════════════════════════════════════════════════
            // HELPER: Refresh the counter badge + enable/disable button
            // ════════════════════════════════════════════════════════
            function refreshCount(letterNum) {
                var checkClass = '.chk-' + letterNum;
                var count = $(checkClass + ':checked').length;
                $('#count-' + letterNum).text(count + ' selected');

                // Enable/disable the "Print Selected" button
                $('[data-checkclass="chk-' + letterNum + '"].print_selected_btn')
                    .prop('disabled', count === 0);
            }

            // ════════════════════════════════════════════════════════
            // SELECT-ALL checkbox in header
            // ════════════════════════════════════════════════════════
            $(document).on('change', '.select-all-check', function () {
                var targetClass = '.' + $(this).data('target');
                $(targetClass).prop('checked', this.checked)
                              .closest('tr')
                              .toggleClass('row-selected', this.checked);

                // Determine letter number from the target class string (chk-1/2/3)
                var letterNum = $(this).data('target').replace('chk-', '');
                refreshCount(letterNum);
            });

            // ════════════════════════════════════════════════════════
            // Individual row checkbox
            // ════════════════════════════════════════════════════════
            $(document).on('change', '.row-check:not(.select-all-check)', function () {
                $(this).closest('tr').toggleClass('row-selected', this.checked);

                var letterNum = this.className.match(/chk-(\d)/);
                if (letterNum) refreshCount(letterNum[1]);

                // Uncheck select-all if any row unchecked
                var targetClass = $(this).attr('class').match(/chk-\d/)[0];
                var allChecked = ($('.' + targetClass).length === $('.' + targetClass + ':checked').length);
                $('[data-target="' + targetClass + '"]').prop('checked', allChecked);
            });

            // ════════════════════════════════════════════════════════
            // PRINT SELECTED — open all selected letters in ONE new tab
            // ════════════════════════════════════════════════════════
            $(document).on('click', '.print_selected_btn', function () {
                var checkClass = $(this).data('checkclass');
                var letter_no  = $(this).data('letter_no');
                var $checked   = $('.' + checkClass + ':checked');

                if ($checked.length === 0) {
                    alert('Please select at least one receipt.');
                    return;
                }

                var letterLabel = letter_no == 1 ? '1st' : (letter_no == 2 ? '2nd' : '3rd');
                if (!confirm('Print ' + $checked.length + ' ' + letterLabel + ' letter(s) in one tab?')) return;

                var pawnSumIds = [];
                $checked.each(function () {
                    pawnSumIds.push($(this).data('pawn_sum_id'));
                });
                $.ajax({
                    url: "{{ route('arrears.letters.issue-bulk') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", pawn_sum_ids: pawnSumIds, letter_no: letter_no },
                    success: function (response) {
                        window.open(response.print_url, '_blank');
                        setTimeout(function () { location.reload(); }, 1200);
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON?.message || 'Unable to issue the selected letters.');
                    }
                });
            });

            // ════════════════════════════════════════════════════════
            // PRINT SINGLE LETTER (existing behaviour, unchanged)
            // ════════════════════════════════════════════════════════
            $(document).on('click', '.print_letter_btn', function () {
                var receipt_no = $(this).data('receipt_no');
                var pawn_sum_id = $(this).data('pawn_sum_id');
                var letter_no  = $(this).data('letter_no');
                var letterLabel = letter_no == 1 ? '1st' : (letter_no == 2 ? '2nd' : '3rd');

                if (!confirm('Print the ' + letterLabel + ' reminder letter for receipt ' + receipt_no + '?')) return;

                $.ajax({
                    url: "{{ route('arrears.letters.issue') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", pawn_sum_id: pawn_sum_id, letter_no: letter_no },
                    success: function (response) {
                        window.open(response.print_url, '_blank');
                        setTimeout(function () { location.reload(); }, 1200);
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON?.message || 'Unable to issue this letter.');
                    }
                });
            });

        }); // end document.ready
        </script>

        <script>
        document.querySelectorAll('[data-late-letter-details]').forEach(function (button) {
            button.addEventListener('click', function () {
                var details = document.getElementById(button.getAttribute('data-late-letter-details'));
                var opening = details.hasAttribute('hidden');
                details.toggleAttribute('hidden', !opening);
                button.setAttribute('aria-expanded', String(opening));
                button.querySelector('span').textContent = opening ? 'Hide' : 'View';
            });
        });
        </script>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
            crossorigin="anonymous"></script>
        <script src="assets/js/jquery-3.6.0.min.js"></script>
        <script src="assets/js/feather.min.js"></script>
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
        <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/plugins/datatables/datatables.min.js"></script>
        <script src="assets/js/script.js"></script>
        <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    </body>
@endsection
</html>
