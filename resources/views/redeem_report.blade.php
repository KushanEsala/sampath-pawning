@if($recipts->count() > 0)
    <h2 class="text-center">Pawning Receipts</h2>
    <div class="text-center mb-3">
        <a href="{{ route('home') }}" class="btn btn-secondary">Back</a>
    </div>
@else
    <p class="text-center text-danger">No results found.</p>
@endif

@if($recipts->count() > 0)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pawning Receipts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- DataTables & Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        table.dataTable th, table.dataTable td {
            white-space: nowrap;
        }
    </style>
</head>
<body class="p-3">

    <!-- Date Filter Form -->
    <form action="" method="get" class="mb-4 d-flex gap-3 align-items-end">
        <div>
            <label for="from_date" class="form-label">From Date:</label>
            <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>

        <div>
            <label for="to_date" class="form-label">To Date:</label>
            <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}">
        </div>

        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- Receipt Table -->
    <div class="table-responsive">
        <table id="t_redeem_sums" class="table table-bordered table-striped display nowrap w-100">
            <thead class="table-dark">
                <tr>
                    <th>Receipt No</th>
                    <th>Redeem Date</th>
                    <th>Redeem No</th>
                    <th>Total Weight</th>
                    <th>Pawn Weight</th>
                    <th>Original Pawn Amount</th>
                    <th>Payable Pawn Amount</th>
                    <th>Paid Interest</th>
                    <th>Payable Interest</th>
                    <th>Stamp Fee</th>
                    <th>Document Charges</th>
                    <th>Advance Balance</th>
                    <th>Discount</th>
                    <th>Payable Total</th>
                    <th>OC</th>
                    <th>BC</th>
                </tr>
            </thead>
            <tbody>
                @foreach($redeem as $row)
                <tr>
                    <td>{{ $row->Receipt_Number }}</td>
                    <td>{{ $row->Redeem_Date }}</td>
                    <td>{{ $row->Redeem_Number }}</td>
                    <td>{{ $row->Total_Weight }}</td>
                    <td>{{ $row->Pawn_Weight }}</td>
                    <td>{{ $row->Original_Pawn_Amount }}</td>
                    <td>{{ $row->Payable_Pawn_Amount }}</td>
                    <td>{{ $row->Paid_Interest }}</td>
                    <td>{{ $row->Payable_Interest }}</td>
                    <td>{{ $row->Stamp_Fee }}</td>
                    <td>{{ $row->Document_Charges }}</td>
                    <td>{{ $row->Advance_Balance }}</td>
                    <td>{{ $row->Discount }}</td>
                    <td>{{ $row->Payable_Total }}</td>
                    <td>{{ $row->OC }}</td>
                    <td>{{ $row->BC }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
             <tr class="table-secondary text-center">
                    <th colspan="3">Total</th>
                    <th>{{ $totalWeight }}</th>
                    <th>{{ $pawnWeight }}</th>
                    <th colspan="2"></th>
                    <th>{{ $Interest }}</th>
                    <th colspan="5"></th>
                    <th>{{ $totalAmount }}</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Print Button -->
    <div class="mt-3 text-center">
        <button onclick="printTablefun()" class="btn btn-success">Print</button>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables and Export Scripts -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        $(document).ready(function () {
            $('#t_redeem_sums').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'pdfHtml5',
                    'print'
                ],
                scrollX: true,
                pageLength: 1000
            });
        });

        function printTablefun() {
            let printContent = document.getElementById("t_redeem_sums").outerHTML;
            let newWin = window.open("");
            newWin.document.write("<html><head><title>Print</title>");
            newWin.document.write("<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'>");
            newWin.document.write("</head><body>");
            newWin.document.write(printContent);
            newWin.document.write("</body></html>");
            newWin.print();
            newWin.close();
        }
    </script>
</body>
</html>
@endif