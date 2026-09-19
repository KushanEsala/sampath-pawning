{{--
    Partial: _partials/late_letter_tab.blade.php
    Variables:
      $paginator         — LengthAwarePaginator of TPawnSum (enriched)
      $letter_no         — 1 | 2 | 3
      $chk_class         — 'chk-1' | 'chk-2' | 'chk-3'
      $btn_color         — 'success' | 'primary' | 'danger'
      $extra_col_header  — e.g. '1st Letter Date' (null for 1st-letter tab)
      $extra_col_key     — e.g. 'letter_1_date'  (null for 1st-letter tab)
--}}
@php
    $suffix   = $letter_no;
    $colCount = $extra_col_header ? 7 : 6;   // Manage|Customer|Receipt|Type|FinalDate|DueDate|Action  ± ExtraDate
@endphp

<table class="table table-bordered table-hover late-letter-table">
    <thead>
        <tr class="text-center">
            <th class="manage-col">
                <div class="manage-cell">
                    <input type="checkbox" class="row-check select-all-check"
                        data-target="{{ $chk_class }}" title="Select all">
                </div>
                Manage
            </th>
            <th>Customer</th>
            <th class="text-nowrap">Receipt&nbsp;No</th>
            <th class="text-nowrap">Type</th>
            @if($extra_col_header)
            <th class="text-nowrap">{{ $extra_col_header }}</th>
            @endif
            <th class="text-nowrap">Final Date</th>
            <th class="text-nowrap">Letter Due</th>
            <th class="text-nowrap">Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($paginator as $r)
            @php $detailId = "ll-detail-{$letter_no}-{$r->id}"; @endphp
            <tr>
                {{-- Manage --}}
                <td class="manage-col">
                    <div class="manage-cell">
                        <input type="checkbox" class="row-check {{ $chk_class }}"
                            data-pawn_sum_id="{{ $r->id }}"
                            data-receipt_no="{{ $r->Receipt_Number }}"
                            data-letter_no="{{ $letter_no }}">
                        <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            data-detail-row="{{ $detailId }}"
                            aria-expanded="false"
                            title="Toggle details">
                            <i class="fa fa-eye"></i>
                            <span class="btn-label">View</span>
                        </button>
                    </div>
                </td>

                {{-- Customer --}}
                <td>
                    <strong>{{ $r->Customer_Name }}</strong><br>
                    <small class="text-muted">{{ $r->Customer_NIC }}</small><br>
                    <small class="text-muted">{{ $r->Customer_Phone }}</small>
                </td>

                {{-- Receipt No --}}
                <td class="text-nowrap">
                    {{ !empty($r->Receipt_Number) ? $r->Receipt_Number : $r->old_Receipt_Number }}
                </td>

                {{-- Type --}}
                <td>{{ $r->Receipt_Type }}</td>

                {{-- Extra date (only for tab 2 / tab 3) --}}
                @if($extra_col_header)
                <td class="text-nowrap">{{ optional($r->{$extra_col_key})->format('Y-m-d') ?? '—' }}</td>
                @endif

                {{-- Final Date --}}
                <td class="text-nowrap">{{ optional($r->Final_date)->format('Y-m-d') }}</td>

                {{-- Letter Due Date --}}
                <td class="text-nowrap">{{ $r->next_letter_due_date ?? '—' }}</td>

                {{-- Amount --}}
                <td class="text-end text-nowrap">{{ number_format($r->Amount, 2) }}</td>

                {{-- Action --}}
                <td class="text-center">
                    <button type="button"
                        class="btn btn-sm btn-{{ $btn_color }} print_letter_btn"
                        data-pawn_sum_id="{{ $r->id }}"
                        data-receipt_no="{{ $r->Receipt_Number }}"
                        data-letter_no="{{ $letter_no }}">
                        <i class="fa fa-print me-1"></i> Print
                    </button>
                </td>
            </tr>

            {{-- ── Expandable detail row ── --}}
            <tr id="{{ $detailId }}" class="detail-row d-none">
                <td colspan="{{ $colCount + 3 }}">
                    <div class="detail-panel">
                        <div class="detail-grid">
                            <div class="detail-item"><small>Receipt Date</small><strong>{{ optional($r->Receipt_Date)->format('Y-m-d') }}</strong></div>
                            @if($extra_col_header)
                            <div class="detail-item"><small>{{ $extra_col_header }}</small><strong>{{ optional($r->{$extra_col_key})->format('Y-m-d') ?? '—' }}</strong></div>
                            @endif
                            <div class="detail-item"><small>Final Date</small><strong>{{ optional($r->Final_date)->format('Y-m-d') }}</strong></div>
                            <div class="detail-item"><small>Letter Due</small><strong>{{ $r->next_letter_due_date ?? '—' }}</strong></div>
                            <div class="detail-item"><small>Principal</small><strong>{{ number_format($r->Amount, 2) }}</strong></div>
                            <div class="detail-item"><small>Interest</small><strong>{{ number_format($r->financial_breakdown['interest'], 2) }}</strong></div>
                            <div class="detail-item"><small>Service Charge</small><strong>{{ number_format($r->financial_breakdown['service_charge'], 2) }}</strong></div>
                            <div class="detail-item"><small>Letter / Postage</small><strong>{{ number_format($r->financial_breakdown['letter_charge'], 2) }}</strong></div>
                            <div class="detail-item"><small>Total Arrears</small><strong class="text-danger">{{ number_format($r->financial_breakdown['arrears_total'], 2) }}</strong></div>
                            <div class="detail-item"><small>Redemption Total</small><strong class="text-primary">{{ number_format($r->financial_breakdown['redemption_total'], 2) }}</strong></div>
                            <div class="detail-item"><small>NIC</small><strong>{{ $r->Customer_NIC }}</strong></div>
                            <div class="detail-item"><small>Phone</small><strong>{{ $r->Customer_Phone }}</strong></div>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $colCount + 3 }}" class="text-center text-muted py-4">
                    <i class="fa fa-check-circle me-2 text-success"></i>No receipts pending for this letter stage.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if($paginator->count() > 0)
    <tfoot>
        <tr>
            <td colspan="{{ $colCount + 3 }}" class="text-end">
                <strong>Page total (principal):
                    {{ number_format($paginator->getCollection()->sum('Amount'), 2) }}
                </strong>
            </td>
        </tr>
    </tfoot>
    @endif
</table>

{{-- Bulk action bar --}}
<div class="bulk-action-bar">
    <button type="button"
        class="btn btn-{{ $btn_color }} btn-sm print_selected_btn"
        data-checkclass="{{ $chk_class }}"
        data-letter_no="{{ $letter_no }}"
        disabled>
        <i class="fa fa-print me-1"></i> Print Selected ({{ $letter_no == 1 ? '1st' : ($letter_no == 2 ? '2nd' : '3rd') }} Letter)
    </button>
    <span class="selected-count" id="count-{{ $suffix }}">0 selected</span>
</div>

{{-- Pagination --}}
@if($paginator->hasPages())
<div class="pagination-wrap d-flex justify-content-center mt-3">
    {{ $paginator->onEachSide(2)->links() }}
</div>
@endif
