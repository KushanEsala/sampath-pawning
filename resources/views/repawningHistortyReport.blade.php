@extends('layouts.app')

@section('content')
<style>
    /* Modern Professional Styling */
    .report-container {
        background-color: #f5f7fa;
        padding: 30px 20px;
    }

    .card-report {
        border: none;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        overflow: hidden;
        background: white;
    }

    .report-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .report-header h2 {
        font-weight: 600;
        font-size: 1.75rem;
        letter-spacing: 0.5px;
    }

    .table {
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    .table thead {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        color: #ffffff;
    }

    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 16px 12px;
        border: none;
    }

    .table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        border-color: #e2e8f0;
    }

    .table-hover tbody tr:hover {
        background-color: #f7fafc;
        transition: all 0.2s ease;
        transform: translateX(2px);
    }

    .filter-section {
        background: white;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .btn-search {
        padding: 10px 28px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stats-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .badge-custom {
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .text-masked {
        filter: blur(4px);
        user-select: none;
    }

    /* Print specific styles */
    @media print {
        .btn, .filter-section, .no-print {
            display: none !important;
        }
        .card-report {
            box-shadow: none;
            border: 1px solid #000;
        }
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table thead {
            background: #2d3748 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="container-fluid py-4 report-container">
    <div class="report-header shadow">
        <div>
            <h2 class="m-0"><i class="bi bi-graph-up-arrow me-2"></i>Repawning History Report</h2>
            <small class="opacity-75">Comprehensive transaction history and analytics</small>
        </div>
        <a href="{{ route('home') }}" class="btn btn-light btn-sm no-print">
            <i class="bi bi-house-door me-1"></i> Dashboard
        </a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section mb-4">
        <form method="GET" action="{{ route('repawningHistoryReport') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">FROM DATE</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">TO DATE</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" required>
                </div>

                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-search flex-grow-1">
                        <i class="bi bi-search me-1"></i> Generate Report
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-secondary no-print px-4">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                    <a href="{{ route('repawningHistoryReport') }}" class="btn btn-outline-secondary no-print px-4" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Info Alert -->
    @if(request('from_date') && request('to_date'))
    <div class="alert alert-info border-0 shadow-sm no-print mb-4" style="background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3 text-primary"></i>
            <div>
                <strong>Date Range:</strong> {{ \Carbon\Carbon::parse(request('from_date'))->format('d M Y') }} - {{ \Carbon\Carbon::parse(request('to_date'))->format('d M Y') }}
                <span class="ms-4"><strong>Total Records:</strong> <span class="badge bg-primary">{{ count($repawnings) }}</span></span>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Report Table -->
    <div class="card card-report">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Invoice No</th>
                        <th>Pawn Date</th>
                        @if(auth()->user()->role === 'Admin')
                        <th>Customer NIC</th>
                        <th>Customer Name</th>
                        <th>Phone</th>
                        @endif
                        <th class="text-center">Redeem Date</th>
                        <th class="text-end">Original Pawn</th>
                        <th class="text-end">Payable Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repawnings as $index => $row)
                    <tr>
                        <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                        <td>{{ $row->Invoice_Number }}</td>
                        <td>
                            @if($row->Pawn_Date)
                                {{ \Carbon\Carbon::parse($row->Pawn_Date)->format('d M Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        @if(auth()->user()->role === 'Admin')
                        <td>{{ $row->Customer_NIC }}</td>
                        <td>{{ $row->Customer_Name }}</td>
                        <td>{{ $row->Customer_Phone ?? 'N/A' }}</td>
                        @endif
                        <td class="text-center">
                            <span class="badge badge-custom bg-light text-dark border">
                                {{ \Carbon\Carbon::parse($row->Redeem_Date)->format('d M Y') }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">Rs. {{ number_format($row->Original_Pawn_Amount, 2) }}</td>
                        <td class="text-end fw-bold text-success">Rs. {{ number_format($row->Payable_Total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'Admin' ? '11' : '8' }}" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                <h5 class="mb-2">No Records Found</h5>
                                @if(!request('from_date') || !request('to_date'))
                                    <p class="mb-0 small">Please select a date range to generate the report.</p>
                                @else
                                    <p class="mb-0 small">No transactions found for the selected period.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($repawnings) > 0)
                <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="{{ auth()->user()->role === 'Admin' ? '7' : '4' }}" class="text-end ps-3 text-uppercase">Grand Total:</td>
                        <td class="text-end">Rs. {{ number_format($repawnings->sum('Original_Pawn_Amount'), 2) }}</td>
                        <td class="text-end text-warning">Rs. {{ number_format($repawnings->sum('Payable_Total'), 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Statistics Cards -->
    @if(count($repawnings) > 0)
    <div class="row mt-4 no-print g-4">
        <div class="col-md-4">
            <div class="card stats-card text-center border-start border-primary border-4">
                <div class="card-body py-4">
                    <i class="bi bi-receipt text-primary fs-2 mb-2"></i>
                    <h6 class="text-muted mb-2 small text-uppercase">Total Transactions</h6>
                    <h2 class="mb-0 fw-bold text-dark">{{ count($repawnings) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card text-center border-start border-info border-4">
                <div class="card-body py-4">
                    <i class="bi bi-cash-stack text-info fs-2 mb-2"></i>
                    <h6 class="text-muted mb-2 small text-uppercase">Total Original Pawn</h6>
                    <h4 class="mb-0 fw-bold text-info">Rs. {{ number_format($repawnings->sum('Original_Pawn_Amount'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card text-center border-start border-success border-4">
                <div class="card-body py-4">
                    <i class="bi bi-wallet2 text-success fs-2 mb-2"></i>
                    <h6 class="text-muted mb-2 small text-uppercase">Total Payable</h6>
                    <h4 class="mb-0 fw-bold text-success">Rs. {{ number_format($repawnings->sum('Payable_Total'), 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection