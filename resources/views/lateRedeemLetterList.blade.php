@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Letter Posting List</title>
    <style>
        table {
            border-collapse: collapse;
            border: 2px solid rgb(200,200,200);
            letter-spacing: 1px;
        }

        td, th {
            border: 1px solid rgb(190,190,190);
            padding: 10px 20px;
        }

        th {
            background-color: rgb(235,235,235);
        }


        tr:nth-child(even) td {
            background-color: rgb(250,250,250);
        }

        tr:nth-child(odd) td {
            background-color: rgb(245,245,245);
        }

        caption {
            padding: 10px;
            caption-side: top;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .input {
            max-width: 190px;
            background-color: #f5f5f5;
            color: #242424;
            padding: .15rem .5rem;
            min-height: 40px;
            border-radius: 4px;
            outline: none;
            border: none;
            line-height: 1.15;
            box-shadow: 0px 10px 20px -18px;
        }

        input:focus {
            border-bottom: 2px solid #5b5fc7;
            border-radius: 4px 4px 2px 2px;
        }

        input:hover {
            outline: 1px solid lightgrey;
        }

        /* ---- Buttons ---- */
        button {
            font-family: inherit;
            font-size: 14px;
            background: linear-gradient(to bottom, #4dc7d9 0%, #66a6ff 100%);
            color: white;
            padding: 0.8em 1.2em;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 25px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
            cursor: pointer;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.3);
        }

        button:active {
            transform: scale(0.95);
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }

        button span {
            display: block;
            margin-left: 0.4em;
            transition: all 0.3s;
        }

        button svg {
            width: 18px;
            height: 18px;
            fill: white;
            transition: all 0.3s;
        }

        button .svg-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            margin-right: 0.5em;
            transition: all 0.3s;
        }

        button:hover .svg-wrapper {
            background-color: rgba(255, 255, 255, 0.5);
        }

        button:hover svg {
            transform: rotate(45deg);
        }

        /* ---- Date Range Filter ---- */
        .date-filter-form {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 18px;
            flex-wrap: wrap;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 14px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .date-filter-form label {
            font-size: 13px;
            font-weight: 700;
            color: #444;
            white-space: nowrap;
        }

        .date-filter-form .input {
            min-width: 150px;
        }

        .btn-filter {
            background: linear-gradient(to bottom, #4dc7d9 0%, #66a6ff 100%) !important;
        }

        .btn-reset {
            background: linear-gradient(to bottom, #f6d365 0%, #fda085 100%) !important;
            color: white !important;
        }

        /* ---- Letter Filter Buttons ---- */
        .letter-btn-group {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .letter-btn-group a {
            text-decoration: none;
        }

        .btn-letter-1 {
            background: linear-gradient(to bottom, #f6d365 0%, #fda085 100%) !important;
        }

        .btn-letter-2 {
            background: linear-gradient(to bottom, #a1c4fd 0%, #c2e9fb 100%) !important;
            color: #333 !important;
        }

        .btn-letter-2 svg {
            fill: #333 !important;
        }

        .btn-letter-3 {
            background: linear-gradient(to bottom, #d4fc79 0%, #96e6a1 100%) !important;
            color: #333 !important;
        }

        .btn-letter-3 svg {
            fill: #333 !important;
        }

        .btn-active {
            box-shadow: 0px 0px 0px 3px rgba(0, 0, 0, 0.25) !important;
            transform: translateY(-2px);
        }

        /* ---- Active letter badge ---- */
        .letter-badge {
            display: inline-block;
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 14px;
            color: #fff;
            background-color: #555;
        }

        /* ---- Container ---- */
        body {
            margin: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 60px;
            border-radius: 8px;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 6px 12px -2px, rgba(0, 0, 0, 0.3) 0px 3px 7px -3px;
        }

        @media print {
            @page {}
            .print-button,
            .letter-btn-group,
            .letter-badge,
            .date-filter-form,
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div id="printableArea" class="container mt-4">
                    <br>

                    {{-- ====== Date Range Filter Form ====== --}}
                    <form method="GET" action="{{ route('late.redeem.letter.list') }}" class="date-filter-form no-print">
                        <input type="hidden" name="letter" value="{{ $selectedLetter }}">

                        <label for="from_date">📅 From:</label>
                        <input type="date" id="from_date" name="from_date" class="input" value="{{ $fromDate }}">

                        <label for="to_date">📅 To:</label>
                        <input type="date" id="to_date" name="to_date" class="input" value="{{ $toDate }}">

                        <button type="submit" class="btn-filter">
                            <div class="svg-wrapper-1">
                                <div class="svg-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415zm-5.242 1.156a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11"/>
                                    </svg>
                                </div>
                            </div>
                            <span>Filter</span>
                        </button>

                        <a href="{{ route('late.redeem.letter.list', ['letter' => $selectedLetter]) }}" style="text-decoration:none;">
                            <button type="button" class="btn-reset">
                                <div class="svg-wrapper-1">
                                    <div class="svg-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                                        </svg>
                                    </div>
                                </div>
                                <span>Reset</span>
                            </button>
                        </a>
                    </form>
                    {{-- ====== End Date Range Filter Form ====== --}}

                    {{-- ====== Three Letter Filter Buttons ====== --}}
                    <div class="letter-btn-group no-print">

                        <a href="{{ route('late.redeem.letter.list', ['letter' => 1, 'from_date' => $fromDate, 'to_date' => $toDate]) }}">
                            <button type="button" class="btn-letter-1 {{ isset($selectedLetter) && $selectedLetter == 1 ? 'btn-active' : '' }}">
                                <div class="svg-wrapper-1">
                                    <div class="svg-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                                        </svg>
                                    </div>
                                </div>
                                <span>Letter 1 List</span>
                            </button>
                        </a>

                        <a href="{{ route('late.redeem.letter.list', ['letter' => 2, 'from_date' => $fromDate, 'to_date' => $toDate]) }}">
                            <button type="button" class="btn-letter-2 {{ isset($selectedLetter) && $selectedLetter == 2 ? 'btn-active' : '' }}">
                                <div class="svg-wrapper-1">
                                    <div class="svg-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                                        </svg>
                                    </div>
                                </div>
                                <span>Letter 2 List</span>
                            </button>
                        </a>

                        <a href="{{ route('late.redeem.letter.list', ['letter' => 3, 'from_date' => $fromDate, 'to_date' => $toDate]) }}">
                            <button type="button" class="btn-letter-3 {{ isset($selectedLetter) && $selectedLetter == 3 ? 'btn-active' : '' }}">
                                <div class="svg-wrapper-1">
                                    <div class="svg-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                                        </svg>
                                    </div>
                                </div>
                                <span>Letter 3 List</span>
                            </button>
                        </a>

                    </div>
                    {{-- ====== End Filter Buttons ====== --}}

                    {{-- ====== Active Letter Badge ====== --}}
                    @if(isset($selectedLetter))
                        <div class="no-print">
                            <span class="letter-badge">
                                📋 Showing: Letter {{ $selectedLetter }} Posting List
                                &nbsp;|&nbsp; 📅 {{ $fromDate }} → {{ $toDate }}
                                &nbsp;|&nbsp; Total Records: {{ count($receiptType) }}
                            </span>
                        </div>
                    @endif

                    {{-- ====== Table ====== --}}
                    <div class="table-responsive">
                        <table id="LateRedeemtable" class="table table-striped table-bordered" style="width:100%">
                            <caption>
                                <div style="text-align: center; margin-bottom: 15px;">
                                    @foreach($companyData as $sumData)
                                        <div style="margin-bottom: 5px;">
                                            <span style="display: block; font-size: 16px; font-weight: bold;">{{ $sumData['name'] }}</span>
                                            <span style="display: block; font-size: 14px;">{{ $sumData['address'] }}</span>
                                            <span style="display: block; font-size: 14px;">Email: {{ $sumData['email'] }}</span>

                                        </div>
                                    @endforeach
                             @if($branchData)
    <div style="margin-bottom: 5px;">
        <span style="display: block; font-size: 14px;">{{ $branchData->contact1 }}</span>
        {{-- Add any other fields from branchDel model --}}
    </div>
@else
    <p>No branch data found for this branch.</p>
@endif

                                    <span style="display: block; font-size: 16px; font-weight: bold;">
                                        LETTER
                                        @if(isset($selectedLetter)) {{ $selectedLetter }} @endif
                                        POSTING LIST
                                    </span>

                                    <span style="display: block; font-size: 13px; color: #555; margin-top: 4px;">
                                        {{ $fromDate }} &mdash; {{ $toDate }}
                                    </span>
                                </div>
                            </caption>

                            <thead>
                                <tr class="styled-table text-center">
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Receipt No</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($receiptType as $key => $receipts)
                                    <tr class="styled-table text-center">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $receipts->Customer_Name }}</td>
                                        <td>{{ $receipts->Customer_Address }}</td>
                                        <td>{{ $receipts->Receipt_Number }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align:center; color: #999; padding: 20px;">
                                            No records found for the selected date range.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                    {{-- ====== End Table ====== --}}

                    <br>

                    {{-- ====== Print Button ====== --}}
                    <div class="print-button no-print">
                        <button onclick="printTablefun()">
                            <div class="svg-wrapper-1">
                                <div class="svg-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                                        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
                                    </svg>
                                </div>
                            </div>
                            <span>Print List</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ====== Print Script ====== --}}
<script>
 function printTablefun() {
            let printContent = document.getElementById("LateRedeemtable").outerHTML;
            let newWin = window.open("");
            newWin.document.write("<style>");
            newWin.document.write("@page { margin: 1cm; }");
            newWin.document.write("body { font-family: Arial, sans-serif; padding: 10px; }");
            newWin.document.write("table { border-collapse: collapse; width: 100%; }");
            newWin.document.write("th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }");
            newWin.document.write("th { font-weight: bold; }");
            newWin.document.write("tbody tr:nth-child(even) { background: #f0f0f0; }");
            newWin.document.write("tfoot td { background: #f8f9fa; font-weight: bold; border-top: 2px solid #000; }");
            newWin.document.write(".amount-cell { color: #28a745; font-weight: bold; }");
            newWin.document.write(".weight-cell { color: #fd7e14; font-weight: bold; }");
            newWin.document.write(".ticket-cell { color: #17a2b8; font-weight: bold; }");
            newWin.document.write("</style>");
            newWin.document.write("</head><body>");

            newWin.document.write(printContent);
            newWin.document.write("</body></html>");
            newWin.document.close();
            setTimeout(function() {
                newWin.print();
            }, 250);
        }
</script>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function () {
            $('#LateRedeemtable').DataTable({
                "aLengthMenu": [[500, 10, 25, -1], [500, 10, 25, "All"]],
                "iDisplayLength": 1000
            });
        });
    </script>

</body>
</html>

@endsection