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
            /* ── Letter Tab Colours ── */
            .tab-1st          { color: #1a7a4a !important; border: 2px solid transparent !important; background: #eafaf1 !important; }
            .tab-1st:hover    { background: #c8f0d8 !important; border-color: #1a7a4a !important; }
            .tab-1st.active   { color: #fff !important; background: #1a7a4a !important;
                                border-color: #1a7a4a !important; box-shadow: 0 -3px 0 #0f5132 inset; }

            .tab-2nd          { color: #0d4fa0 !important; border: 2px solid transparent !important; background: #e8f0fd !important; }
            .tab-2nd:hover    { background: #c5d9f9 !important; border-color: #0d4fa0 !important; }
            .tab-2nd.active   { color: #fff !important; background: #0d4fa0 !important;
                                border-color: #0d4fa0 !important; box-shadow: 0 -3px 0 #083580 inset; }

            .tab-3rd          { color: #b80000 !important; border: 2px solid transparent !important; background: #fdeaea !important; }
            .tab-3rd:hover    { background: #f5c0c0 !important; border-color: #b80000 !important; }
            .tab-3rd.active   { color: #fff !important; background: #b80000 !important;
                                border-color: #b80000 !important; box-shadow: 0 -3px 0 #7a0000 inset; }

            .nav-tabs .nav-link.active { border-bottom-color: transparent !important; }

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

            /* Row selection */
            tr.row-selected td { background-color: #d1ecf1 !important; }
            .row-check { width: 18px; height: 18px; cursor: pointer; }

            /* Bulk action bar */
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

            /* ── Slim table — NO horizontal scroll ── */
            .late-letter-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 0;
            }
            .late-letter-table th {
                background: #f0f4f8;
                color: #2c3e50;
                font-weight: 600;
                font-size: .83rem;
                white-space: nowrap;
                padding: .6rem .7rem;
                vertical-align: middle;
            }
            .late-letter-table td {
                font-size: .84rem;
                padding: .6rem .7rem;
                vertical-align: middle;
            }
            .late-letter-table .text-nowrap { white-space: nowrap; }
            .late-letter-table .manage-col  { width: 80px; text-align: center; }

            /* Manage cell — checkbox left, View btn right */
            .manage-cell {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }
            .manage-cell .btn {
                padding: .18rem .42rem;
                font-size: .75rem;
                line-height: 1.2;
            }

            /* ── Expandable detail panel ── */
            .detail-row > td {
                padding: 0 !important;
                background: #f8f9fc;
            }
            .detail-panel {
                padding: .9rem 1.3rem;
                border-left: 4px solid #6f42c1;
            }
            .detail-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: .7rem;
            }
            .detail-item {
                background: #fff;
                border: 1px solid #e1e4eb;
                border-radius: 6px;
                padding: .55rem .75rem;
            }
            .detail-item small {
                display: block;
                color: #73788a;
                font-size: .72rem;
                margin-bottom: 2px;
            }
            .detail-item strong { font-size: .88rem; }

            /* Pagination */
            .pagination-wrap { margin-top: 12px; }

            @media (max-width: 768px) {
                .late-letter-table th, .late-letter-table td { font-size: .75rem; padding: .45rem .4rem; }
                .detail-grid { grid-template-columns: repeat(2, 1fr); }
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
                                    <div class="alert alert-danger text-center" role="alert">{{ session('delete') }} &#10004;</div>
                                @endif
                                @if (session('added'))
                                    <div class="alert alert-success text-center" role="alert">{{ session('added') }} &#10004;</div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert alert-danger" role="alert">
                                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-1"></div>
                            <div class="col">
                                <div class="card-body">

                                    <a href="{{ route('lateRedeemLetterList') }}" class="btn btn-info mb-2" style="width:fit-content;">
                                        Print List <i class="fa fa-print"></i>
                                    </a>

                                    {{-- Search / Filter --}}
                                    <form method="GET" action="{{ route('pawning_late_letters') }}" class="row g-2 my-3 align-items-end">
                                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                                        <div class="col-md-4">
                                            <label class="form-label" for="receipt_number">Receipt Number</label>
                                            <input class="form-control" id="receipt_number" name="receipt_number"
                                                value="{{ request('receipt_number') }}" placeholder="Find a receipt">
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

                                    <br>

                                    {{-- ── TAB NAV ── --}}
                                    <ul class="nav nav-tabs" role="tablist" style="border-bottom: 3px solid #1a3c5e; gap: 6px;">

                                        <li class="nav-item">
                                            <a class="nav-link letter-tab tab-1st {{ $activeTab == 1 ? 'active' : '' }}"
                                               href="{{ request()->fullUrlWithQuery(['tab' => 1, 'page' => 1]) }}"
                                               style="border-radius: 8px 8px 0 0; font-weight: 600; padding: 10px 22px;">
                                                <i class="fas fa-envelope me-2"></i>1st Letter
                                                <span class="tab-badge">{{ $count_1st }}</span>
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link letter-tab tab-2nd {{ $activeTab == 2 ? 'active' : '' }}"
                                               href="{{ request()->fullUrlWithQuery(['tab' => 2, 'page' => 1]) }}"
                                               style="border-radius: 8px 8px 0 0; font-weight: 600; padding: 10px 22px;">
                                                <i class="fas fa-envelope me-2"></i>2nd Letter
                                                <span class="tab-badge">{{ $count_2nd }}</span>
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link letter-tab tab-3rd {{ $activeTab == 3 ? 'active' : '' }}"
                                               href="{{ request()->fullUrlWithQuery(['tab' => 3, 'page' => 1]) }}"
                                               style="border-radius: 8px 8px 0 0; font-weight: 600; padding: 10px 22px;">
                                                <i class="fas fa-envelope me-2"></i>3rd Letter
                                                <span class="tab-badge">{{ $count_3rd }}</span>
                                            </a>
                                        </li>

                                    </ul>

                                    {{-- ── TAB CONTENT ── --}}
                                    <div class="tab-content mt-3">

                                        {{-- ════════════ 1st Letter ════════════ --}}
                                        @if($activeTab == 1)
                                        <div class="tab-pane show active" id="tab-1">
                                            @include('_partials.late_letter_tab', [
                                                'paginator'    => $tab1,
                                                'letter_no'    => 1,
                                                'chk_class'    => 'chk-1',
                                                'btn_color'    => 'success',
                                                'extra_col_header' => null,
                                                'extra_col_key'    => null,
                                            ])
                                        </div>
                                        @endif

                                        {{-- ════════════ 2nd Letter ════════════ --}}
                                        @if($activeTab == 2)
                                        <div class="tab-pane show active" id="tab-2">
                                            @include('_partials.late_letter_tab', [
                                                'paginator'    => $tab2,
                                                'letter_no'    => 2,
                                                'chk_class'    => 'chk-2',
                                                'btn_color'    => 'primary',
                                                'extra_col_header' => '1st Letter Date',
                                                'extra_col_key'    => 'letter_1_date',
                                            ])
                                        </div>
                                        @endif

                                        {{-- ════════════ 3rd Letter ════════════ --}}
                                        @if($activeTab == 3)
                                        <div class="tab-pane show active" id="tab-3">
                                            @include('_partials.late_letter_tab', [
                                                'paginator'    => $tab3,
                                                'letter_no'    => 3,
                                                'chk_class'    => 'chk-3',
                                                'btn_color'    => 'danger',
                                                'extra_col_header' => '2nd Letter Date',
                                                'extra_col_key'    => 'letter_2_date',
                                            ])
                                        </div>
                                        @endif

                                    </div>{{-- end tab-content --}}

                                </div>
                            </div>
                            <div class="col-md-1"></div>
                        </div>

                        {{-- Hidden table for list printing (uses all receipts from whatever is loaded) --}}
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
                                    @php $allReceipts = $tab1 ?? $tab2 ?? $tab3; @endphp
                                    @if($allReceipts)
                                    @foreach ($allReceipts as $r)
                                        <tr>
                                            <td>{{ $r->Customer_NIC }}</td>
                                            <td>{{ $r->Customer_Name }}</td>
                                            <td>{{ $r->Customer_Phone }}</td>
                                            <td>{{ $r->Receipt_Number }}</td>
                                            <td>{{ $r->Receipt_Type }}</td>
                                            <td>{{ optional($r->Receipt_Date)->format('Y-m-d') }}</td>
                                            <td>{{ optional($r->Final_date)->format('Y-m-d') }}</td>
                                            <td class="text-end">{{ number_format($r->Amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    @endif
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

            // ── SELECT-ALL ──
            $(document).on('change', '.select-all-check', function () {
                var targetClass = '.' + $(this).data('target');
                $(targetClass).prop('checked', this.checked)
                              .closest('tr').toggleClass('row-selected', this.checked);
                refreshCount($(this).data('target').replace('chk-', ''));
            });

            // ── INDIVIDUAL CHECKBOX ──
            $(document).on('change', '.row-check:not(.select-all-check)', function () {
                $(this).closest('tr').toggleClass('row-selected', this.checked);
                var m = this.className.match(/chk-(\d)/);
                if (m) refreshCount(m[1]);
                var tc = $(this).attr('class').match(/chk-\d/)[0];
                var allChecked = ($('.' + tc).length === $('.' + tc + ':checked').length);
                $('[data-target="' + tc + '"]').prop('checked', allChecked);
            });

            function refreshCount(num) {
                var count = $('.chk-' + num + ':checked').length;
                $('#count-' + num).text(count + ' selected');
                $('[data-checkclass="chk-' + num + '"].print_selected_btn').prop('disabled', count === 0);
            }

            // ── PRINT SELECTED ──
            $(document).on('click', '.print_selected_btn', function () {
                var checkClass = $(this).data('checkclass');
                var letter_no  = $(this).data('letter_no');
                var $checked   = $('.' + checkClass + ':checked');
                if ($checked.length === 0) { alert('Please select at least one receipt.'); return; }
                var label = letter_no == 1 ? '1st' : (letter_no == 2 ? '2nd' : '3rd');
                if (!confirm('Print ' + $checked.length + ' ' + label + ' letter(s) in one tab?')) return;
                var ids = [];
                $checked.each(function () { ids.push($(this).data('pawn_sum_id')); });
                $.ajax({
                    url: "{{ route('arrears.letters.issue-bulk') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", pawn_sum_ids: ids, letter_no: letter_no },
                    success: function (r) { window.open(r.print_url, '_blank'); setTimeout(function () { location.reload(); }, 1200); },
                    error:   function (xhr) { alert(xhr.responseJSON?.message || 'Unable to issue the selected letters.'); }
                });
            });

            // ── PRINT SINGLE ──
            $(document).on('click', '.print_letter_btn', function () {
                var receipt_no  = $(this).data('receipt_no');
                var pawn_sum_id = $(this).data('pawn_sum_id');
                var letter_no   = $(this).data('letter_no');
                var label = letter_no == 1 ? '1st' : (letter_no == 2 ? '2nd' : '3rd');
                if (!confirm('Print the ' + label + ' reminder letter for receipt ' + receipt_no + '?')) return;
                $.ajax({
                    url: "{{ route('arrears.letters.issue') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", pawn_sum_id: pawn_sum_id, letter_no: letter_no },
                    success: function (r) { window.open(r.print_url, '_blank'); setTimeout(function () { location.reload(); }, 1200); },
                    error:   function (xhr) { alert(xhr.responseJSON?.message || 'Unable to issue this letter.'); }
                });
            });

            // ── VIEW EXPAND ──
            $(document).on('click', '[data-detail-row]', function () {
                var rowId   = $(this).data('detail-row');
                var $row    = $('#' + rowId);
                var opening = $row.hasClass('d-none');
                $row.toggleClass('d-none', !opening);
                $(this).find('span.btn-label').text(opening ? 'Hide' : 'View');
                $(this).attr('aria-expanded', String(opening));
            });

        }); // end ready
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
            crossorigin="anonymous"></script>
        <script src="assets/js/jquery-3.6.0.min.js"></script>
        <script src="assets/js/feather.min.js"></script>
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
        <script src="assets/js/script.js"></script>
        <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    </body>
@endsection
</html>
