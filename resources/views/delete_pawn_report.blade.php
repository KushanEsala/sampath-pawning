@if($recipts->count() > 0)
    <ul>
        @foreach ( $recipts as $key=>$receipts)

        @endforeach
    </ul>
@else

@endif



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">


    <script>
        $('#t_pawn_sums').DataTable()
    </script>

    <title>Deleted Recipts</title>
</head>

<body>
    <h2>Deleted Pawning Recipts</h2>
    <div style="text-align: center;">
        <form action="" method="get">
            <label for="from_date">From Date:</label>
            <input type="date" name="from_date" id="from_date">

            <label for="to_date">To Date:</label>
            <input type="date" name="to_date" id="to_date">

            <button type="submit">Search</button>
            <button onclick="printTablefun()">Print</button>

            <Button class="d-inline p-2 text-bg-primary">
                <a href="{{route("home")}}">
                    Back
                </a>
                <i class="fa-solid fa-house"></i>
            </Button>
        </form>
    </div>




    <div class="card shadow p-3 mb-3 bg-body-tertiary rounded" >
        <div class="table">
            <table class="display" id="t_pawn_sums">
                <thead>
                    <tr class="table-secondary">
                        <th>Customer NIC</th>
                        <th>Customer Name</th>
                        <th>Customer Address</th>
                        <th>Customer Phone</th>
                        <th>Receipt Type</th>
                        <th>Pawn Type</th>
                        <th>Receipt Number</th>
                        <th>Receipt Date</th>
                        <th>Amount</th>
                        <th>Total Amount</th>
                        <th>Interest</th>
                        <th>Reason</th>
                        <th>Deleted By</th>

                    </tr>
                </thead>
                <tbody>
                @foreach ( $recipts as $key=>$receipts)
                    <tr>
                        <td>{{$receipts->Customer_NIC }}</td>
                        <td>{{$receipts->Customer_Name}}</td>
                        <td>{{$receipts->Customer_Address }}</td>
                        <td>{{$receipts->Customer_Phone}}</td>
                        <td>{{$receipts->Receipt_Type }}</td>
                        <td>{{$receipts->Pawn_Type }}</td>
                        <td>{{$receipts->Receipt_Number}}</td>
                        <td>{{$receipts->Receipt_Date}}</td>
                        <td>{{$receipts->Amount}}</td>
                        <td>{{$receipts->Total_Amount }}</td>
                        <td>{{$receipts->Interest}}</td>
                        <td>{{$receipts->Reason}}</td>
                        <td>{{$receipts->Deleted_by}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
    </div>

            </div>
        </div>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js">
    </script>
    <script type="text/javascript" charset="utf8"
        src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js">
    </script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js">
    </script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js">
    </script>
    <script>
        jQuery(document).ready(function($) {
            $('#t_pawn_sums').DataTable( //database table name
                {
                    "paging": false,
                    dom: 'Bfrtip',
                    buttons: [
                    'copy',
                    'excel',
                    'csv',
                    'pdf',
                    'print',
                    ],
                }
            );

        });


    function printTablefun() {
        var divToPrint = document.getElementById("t_pawn_sums");
        newWin = window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.print();
        newWin.close();
    }

    </script>
    {{-- form default date set for today --}}
    <script>
        var dateObj = new Date();
        document.getElementById('to_date').value = dateObj.toISOString().slice(0, 10);
    </script>

</html>
