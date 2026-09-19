@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Part Payment History Report</h2>
            <p class="text-muted mb-0">View and export payment transaction records</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-primary">
            <i class="bi bi-house me-1"></i> Back to Home
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold"><i class="bi bi-funnel me-2"></i>Filter Options</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-medium">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                    <div class="col-md-3 align-self-end">
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold"><i class="bi bi-table me-2"></i>Payment Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="paymentReport" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>

                            <th class="text-center">Invoice No</th>
                            @if(auth()->check() && auth()->user()->role === 'Admin')
                             <th class="text-center">Receipt No</th>
                                <th class="text-center">Customer NIC</th>
                                <th>Customer Name</th>
                            @endif
                            <th class="text-center">Redeem Date</th>
                            <th class="text-end">Pawn Amount</th>
                            <th class="text-end">Interest</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Operater</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partPayments as $row)
                            <tr>

                                <td class="text-center">{{ $row->Invoice_Number }}</td>
                                @if(auth()->check() && auth()->user()->role === 'Admin')
                                <td class="text-center">{{ $row->Receipt_Number }}</td>
                                    <td class="text-center">{{ $row->Customer_NIC }}</td>
                                    <td>{{ $row->Customer_Name }}</td>
                                @endif
                                <td class="text-center">{{ \Carbon\Carbon::parse($row->Redeem_Date)->format('d M Y') }}</td>
                                <td class="text-end fw-medium">{{ number_format($row->Payable_Pawn_Amount, 2) }}</td>
                                <td class="text-end fw-medium">{{ number_format($row->Paid_Interest, 2) }}</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($row->Payable_Total, 2) }}</td>
                                <td class="text-end text-success fw-medium">{{ number_format($row->Discount, 2) }}</td>
                                <td class="text-end text-success fw-medium">{{ $row->OC }}</td>
                            </tr>
                        @empty
                            <tr>
                                @if(auth()->check() && auth()->user()->role === 'Admin')
                                    <td colspan="10" class="text-center py-5 text-muted">
                                @else
                                    <td colspan="8" class="text-center py-5 text-muted">
                                @endif
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No payment records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($partPayments->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                @if(auth()->check() && auth()->user()->role === 'Admin')
                                    <th colspan="5" class="text-end fw-bold">TOTAL:</th>
                                @else
                                    <th colspan="2" class="text-end fw-bold">TOTAL:</th>
                                @endif
                                <th class="text-end fw-bold">{{ number_format($partPayments->sum('Payable_Pawn_Amount'), 2) }}</th>
                                <th class="text-end fw-bold">{{ number_format($partPayments->sum('Paid_Interest'), 2) }}</th>
                                <th class="text-end fw-bold text-primary">{{ number_format($partPayments->sum('Payable_Total'), 2) }}</th>
                                <th class="text-end fw-bold text-success">{{ number_format($partPayments->sum('Discount'), 2) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        const isAdmin = @json(auth()->check() && auth()->user()->role === 'Admin');
        const sortColumnIndex = isAdmin ? 5 : 3;

        $('#paymentReport').DataTable({
            dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 text-end"B>>rtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel me-1"></i> Export to Excel',
                    className: 'btn btn-success btn-sm me-2',
                    title: 'Part Payment History Report',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-pdf me-1"></i> Export to PDF',
                    className: 'btn btn-danger btn-sm me-2',
                    title: 'Part Payment History Report',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer me-1"></i> Print',
                    className: 'btn btn-secondary btn-sm',
                    title: 'Part Payment History Report',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[sortColumnIndex, 'desc']],
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries available",
                infoFiltered: "(filtered from _MAX_ total entries)"
            }
        });
    });
</script>

<style>
    .card {
        border-radius: 10px;
    }

    .card-header {
        border-bottom: 2px solid #f0f0f0;
        border-radius: 10px 10px 0 0 !important;
    }

    .table {
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    .table thead th {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
        padding: 12px 8px;
        background-color: #f8f9fa;
    }

    .table tbody td {
        padding: 12px 8px;
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .table tfoot th {
        background-color: #e9ecef;
        font-size: 0.9rem;
        padding: 12px 8px;
    }

    .dt-buttons {
        margin-bottom: 0;
        display: flex;
        gap: 0;
        flex-wrap: wrap;
    }

    .dt-button {
        border-radius: 6px !important;
        padding: 6px 14px !important;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .dt-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    }

    .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 7px 14px;
        margin-left: 8px;
        width: 250px;
    }

    .dataTables_filter input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        outline: 0;
    }

    .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 5px 10px;
        margin: 0 8px;
    }

    .pagination {
        margin-top: 20px;
    }

    .page-link {
        border-radius: 6px;
        margin: 0 3px;
        color: #0d6efd;
        border: 1px solid #dee2e6;
    }

    .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .table tbody tr td i.bi-inbox {
        opacity: 0.3;
        color: #6c757d;
    }

    .form-control:focus, .btn:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    @media (max-width: 768px) {
        .dt-buttons {
            justify-content: center;
            margin-bottom: 15px;
        }

        .table {
            font-size: 0.85rem;
        }

        .dataTables_filter input {
            width: 100%;
            margin-top: 10px;
        }

        .card-body {
            padding: 15px;
        }
    }

    .dataTables_info {
        padding-top: 12px;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .dataTables_length {
        font-size: 0.9rem;
    }
</style>
@endsection