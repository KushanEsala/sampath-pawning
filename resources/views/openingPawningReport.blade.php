@if($recipts->count() > 0)
<h2>Opening Pawn Report</h2>
<Button><a href="{{route("home")}}"> Back</a></Button>
<ul>
    @foreach ( $recipts as $key=>$receipts)
    @endforeach
</ul>
@else
<p>No results found.</p>
@endif
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- <div><button onClick="window.print()">Print --}}
    </button></div>
    <script>
        // Select wrong element
        // Error as #demo is the `div` element
        $('#t_opening_pawn_sums').DataTable()
        // Selector too broad.
        // Error as `.display` is applied to both the div and the table
        $('.display').DataTable();
    </script>
    <title>Recipts</title>
    <style>
        .table {
            display: block;
            overflow-y: hidden;
            overflow-x: auto;
            scroll-behavior: smooth;
        }

         .table thead {
            display: table-header-group;
            vertical-align: middle;
            border-color: inherit;
            color: white;
            background: darkcyan;
        }

        tr {
            display: table-row;
            vertical-align: inherit;
            border-color: inherit;
        }

         table th {
            padding: 16px;
            text-align: inherit;
            border-bottom: 1px solid black;
           color:blue!important;
        }

          tbody {
            display: table-row-group;
            vertical-align: middle;
            border-color: inherit;
        }

         table:not(.tr-caption-container) {
            min-width: 100%;
            border-radius: 3px;
        }
        </style>

</head>
<body>
    <form action="" method="get">
        <label for="from_date">From Date:</label>
        <input type="date" name="from_date" id="from_date">

        <label for="to_date">To Date:</label>
        <input type="date" name="to_date" id="to_date">

        <button type="submit">Search</button>
    </form>

    {{-- <div class="card shadow p-3 mb-3 bg-body-tertiary rounded" > --}}
    <div class="d-flex justify-content-center profile-container">
        <div class='col-md-6 text-center sort-profile' id='sort-profile'>
            <div class='row'>
                <div class='col-md-6 text-center'><br />
                    <h2 style="text-align:center; background-color:rgb(113, 105, 255);"  ><b>Opening Pawn Report</b></h2>
                    <hr />
                    <table class="display" id="t_opening_pawn_sums" style="white-space: nowrap; border:1px solid rgb(7, 7, 7); margin-top:15px;width:50%;">
                        <thead class="styled-table">
                            <th>Customer_NIC</th>
                            <th>Customer_Name</th>
                            <th>Customer_Address</th>
                            <th>Customer_Phone</th>
                            <th>Receipt_Type</th>
                            <th>Receipt_Number</th>
                            <th>Invoice_Number</th>
                            <th>Receipt_Date</th>
                            <th>Total Weight</th>
                            <th>Pawn Weight</th>
                            <th>Amount</th>
                            {{-- <th>Total_Amount</th> --}}
                            <th>Interest</th>
                            <th>IsRedeemed</th>
                            <th>OC</th>
                            <th>BC</th>
                        </thead>
                        <tbody>
                            @foreach ( $recipts as $key=>$receipts)
                            <tr>
                                <td>{{$receipts->Customer_NIC }}</td>
                                <td>{{$receipts->Customer_Name}}</td>
                                <td>{{$receipts->Customer_Address }}</td>
                                <td>{{$receipts->Customer_Phone }}</td>
                                <td>{{$receipts->Receipt_Type }}</td>
                                <td>{{$receipts->Receipt_Number }}</td>
                                <td>{{$receipts->Invoice_Number}}</td>
                                <td>{{$receipts->Receipt_Date}}</td>
                                <td>{{$receipts->Total_Weight}}</td>
                                <td>{{$receipts->Pawn_Weight}}</td>
                                <td>{{$receipts->Amount}}</td>
                                {{-- <td>{{$receipts->Total_Amount}}</td> --}}
                                <td>{{$receipts->Interest}}</td>
                                <td>{{$receipts->IsRedeemed}}</td>
                                <td>{{$receipts->OC}}</td>
                                <td>{{$receipts->BC}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8">Total:</td>
                                <td>{{$totalWeight}}</td>
                                <td>{{$pawnWeight}}</td>
                                <td>{{$totalAmount}}</td>
                                <td>{{$Interest}}</td>
                                <td></td>
                                <td></td>
                                <td></td>

                            </tr>
                        </tfoot>
                        {{-- <button onclick="printTablefun()">Print</button> --}}
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>


<script>
    var dateObj = new Date();
    document.getElementById('to_date').value = dateObj.toISOString().slice(0, 10);
</script>


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
    $('#t_opening_pawn_sums').DataTable( //database table name
        {
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
</script>

<script>
    function printTablefun() {
        var divToPrint = document.getElementById("t_opening_pawn_sums");
        newWin = window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.print();
        newWin.close();
    }

    function printtbodyfun() {
        var divToPrint = document.getElementById("t_opening_pawn_sums");
        newWin = window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.print();
        newWin.close();
    }
</script>

{{-- <script type="text/javascript" language="javascript" >
    $(document).ready(function(){

      var dataTable = $('#t_pawn_sums').DataTable({
       "processing" : true,
       "serverSide" : true,
       "order" : [],
       "ajax" : {
        url:"fetch.php",
        type:"POST"
       },
       drawCallback:function(settings)
       {
        $('#Total_Amount').html(settings.json.total);
       }
      });
    });

   </script> --}}

</html>
