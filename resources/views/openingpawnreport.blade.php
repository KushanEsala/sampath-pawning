@if($open->count() > 0)
    <h2>Opening Pawn Recipts</h2>
    <ul>
        {{-- @foreach ( $redeem as $key=>$redeem)

        @endforeach --}}
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



    {{-- <div><button onClick="window.print()">Print

    </button></div> --}}




    <script>
        // Select wrong element
// Error as #demo is the `div` element
$('#t_redeem_sums').DataTable()

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
        <Button type="button" class="btn btn-info" ><a href="{{route("home")}}"> Back</a></Button>
    </form>




    {{-- <div class="card shadow p-3 mb-3 bg-body-tertiary rounded" > --}}
        <div class="d-flex justify-content-center profile-container">
            <div class='col-md-6 text-center sort-profile' id='sort-profile'>
            <div class='row'>
            <div class='col-md-6 text-center' ><br/>
            <h2 style="text-align:center;"><b>Opening Pawn Recipts</b></h2><hr/>
            <table class='table table-light table-striped table-bordered' id="t_redeem_sums" style="background-color: transparent; border:1px solid rgb(193, 26, 26); margin-top:15px;">



                <tr class="styled-table">
                    <th>Customer_NIC</th>
                    <th>Customer_Name</th>
                    <th>Customer_Address</th>
                    <th>Customer_Phone</th>
                    <th>Receipt_Type</th>
                    <th>Receipt_Number</th>
                    <th>Invoice_Number</th>
                    <th>Receipt_Date</th>
                    <th>Amount</th>
                    <th>Total_Amount</th>
                    <th>Interest</th>
                    <th>IsRedeemed</th>
                    <th>OC</th>
                    <th>BC</th>
                </tr>
                <tbody>
                @foreach ( $open as $key=>$open)
                    <tr>
                        <td>{{$open->Customer_NIC }}</td>
                        <td>{{$open->Customer_Name}}</td>
                        <td>{{$open->Customer_Address }}</td>
                        <td>{{$open->Customer_Phone }}</td>
                        <td>{{$open->Receipt_Type }}</td>
                        <td>{{$open->Receipt_Number }}</td>
                        <td>{{$open->Invoice_Number}}</td>
                        <td>{{$open->Receipt_Date}}</td>
                        <td>{{$open->Amount}}</td>
                        <td>{{$open->Total_Amount}}</td>
                        <td>{{$open->Interest}}</td>
                        <td>{{$open->IsRedeemed}}</td>
                        <td>{{$open->OC}}</td>
                        <td>{{$open->BC}}</td>


                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>{{$totalAmount}}</th>
                        <th>{{$Interest}}</th>
                        <th></th>
                        <th></th>
                        <th></th>
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
           var divToPrint=document.getElementById("t_redeem_sums");
           newWin= window.open("");
           newWin.document.write(divToPrint.outerHTML);
           newWin.print();
           newWin.close();
        }

        function printtbodyfun()
        {
           var divToPrint=document.getElementById("t_redeem_sums");
           newWin= window.open("");
           newWin.document.write(divToPrint.outerHTML);
           newWin.print();
           newWin.close();
        }
        </script>

{{-- <script type="text/javascript">
	$(document).ready( function () {
	    $('#t_redeem_sums').DataTable( {
		    drawCallback: function () {
		      var api = this.api();
		      var sum = 0;
		      var formated = 0;
		      //to show first th
		      $(api.column(0).footer()).html('Total');

		      for(var i=0; i<=14;i++)
		      {
		      	sum = api.column(i, {page:'current'}).data().sum();

		      	//to format this sum
		      	formated = parseFloat(sum).toLocaleString(undefined, {minimumFractionDigits:2});
		      	$(api.column(i).footer()).html('$'+formated);
		      }

		    }
		});
	});
</script> --}}




</html>

