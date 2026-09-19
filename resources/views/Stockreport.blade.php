@if($recipts->count() > 0)
    <h2>Stock Report</h2>



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
$('#t_pawn_sums').DataTable()

 // Selector too broad.
 // Error as `.display` is applied to both the div and the table
 $('.display').DataTable();
    </script>
    <title>Recipts</title>
    <style>
        table{
            border-collapse: collapse;
            width: 100%;
        }
    th,td{
        border: 1px solid rgb(8, 8, 8);
        padding: 5px;
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
        <button onclick="printTablefun()">Print</button>
        <Button ><a href="{{route("home")}}"> Back</a></Button>
    </form>




    {{-- <div class="card shadow p-3 mb-3 bg-body-tertiary rounded" > --}}
        <div class="d-flex justify-content-center profile-container">
            <div class='col-md-6 text-center sort-profile' id='sort-profile'>
            <div class='row'>
            <div class='col-md-6 text-center' ><br/>
            <h1 style="text-align:center;"><b>Stock Report</b></h1><hr/>

            <table class='table table-light table-striped table-bordered' id="t_pawn_sums" style="background-color: transparent; border:1px solid rgb(193, 26, 26); margin-top:15px;">
                       <tr class="styled-table">
                        <th>Customer_NIC</th>
                        <th>Customer_Name</th>
                        <th>Customer_Address</th>
                        <th>Customer_Phone</th>
                        <th>Receipt_Type</th>
                        <th>Receipt_Number</th>
                        <th>Invoice_Number</th>
                        <th>Receipt_Date</th>
                        <th>IsRedeemed</th>
                        <th>Amount</th>
                        <th>Interest</th>
                        <!--<th>OC</th>-->
                        <!--<th>BC</th>-->
                    </tr>
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
                        <td>{{$receipts->IsRedeemed}}</td>
                        <td>{{$receipts->Amount}}</td>
                        <td>{{$receipts->Interest}}</td>
                        <!--<td>{{$receipts->OC}}</td>-->
                        <!--<td>{{$receipts->BC}}</td>-->

                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="2"><b>Total Pawn Count = {{$pawnCount}} </td>
                        <td colspan="7"> <center> <b>Total: </b></center></td>
                        <td><b>{{$amount}}</b></td>
                        <td><b>{{$Interest}}</b></td>
                    </tr>
                </tfoot>





            </table>

        </div>
    </div>

            </div>
        </div>
    </div>

</body>


 <script>
        function printTablefun()
        {

           var divToPrint=document.getElementById("t_pawn_sums");
           newWin= window.open("");
           newWin.document.write(divToPrint.outerHTML);
           newWin.print();
           newWin.close();
        }

        function printtbodyfun()
        {
           var divToPrint=document.getElementById("t_pawn_sums");
           newWin= window.open("");
           newWin.document.write(divToPrint.outerHTML);
           newWin.print();
           newWin.close();
        }
        </script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
<script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
  <script>
    jQuery(document).ready(function($) {
      $('#t_pawn_sums').DataTable(    //database table name
        {
        dom: 'Bfrtip',
        buttons: [
                    'copy',
                    'excel',
                    'csv',
                    'pdf',
                    'print'
                ],
        }
      );

    } );
    </script>




</html>
