<a href="javascript:void(0)"
   onClick="editFunc({{ $id }})"
   data-toggle="tooltip"
   title="Edit Record"
   class="btn action-btn btn-edit me-1">
    <i class="fas fa-pen"></i> Edit
</a>

<a href="javascript:void(0)"
   onClick="deleteFunc({{ $id }})"
   data-toggle="tooltip"
   title="Delete Record"
   class="btn action-btn btn-delete me-1">
    <i class="fas fa-trash"></i> Delete
</a>

@auth
@if(auth()->user()->role === 'Admin')
    @if($status === 'Approval')
        <a href="javascript:void(0)"
           class="btn action-btn btn-approved me-1"
           data-toggle="tooltip"
           title="Already Approved">
            <i class="fas fa-check-circle"></i> Approved
        </a>
    @else
        <a href="javascript:void(0)"
           onClick="ApprovalFunc({{ $id }}, '{{ $status }}')"
           data-toggle="tooltip"
           title="Approve Record"
           class="btn action-btn btn-approve me-1">
            <i class="fas fa-check-circle"></i> Approval
        </a>
    @endif
@endif
@endauth

<style>
    .action-btn {
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 14px;
        transition: all 0.25s ease-in-out;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .action-btn i {
        font-size: 15px;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .btn-edit {
        color: #198754;
        border-color: #198754;
    }

    .btn-edit:hover {
        background-color: #198754;
        color: #fff;
    }

    .btn-delete {
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-delete:hover {
        background-color: #dc3545;
        color: #fff;
    }

    .btn-approve {
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-approve:hover {
        background-color: #0d6efd;
        color: #fff;
    }

    .btn-approved {
        background-color: #198754;
        color: #fff;
        cursor: not-allowed !important;
        opacity: 0.85;
    }
</style>