@if($redeem->count() > 0)
<h2>Opening Pawning Article Report</h2>
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

    <Button type="button" class="btn btn-info"><a href="{{route("home")}}"> Back</a></Button>

    <script>
        // Select wrong element
        // Error as #demo is the `div` element
        $('#t_opening_pawn_details').DataTable()
        // Selector too broad.
        // Error as `.display` is applied to both the div and the table
        $('.display').DataTable();
    </script>
    <title>Recipts</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th,
        td {
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
    </form>

    {{-- <div class="card shadow p-3 mb-3 bg-body-tertiary rounded" > --}}
    <div class="d-flex justify-content-center profile-container">
        <div class='col-md-6 text-center sort-profile' id='sort-profile'>
            <div class='row'>
                <div class='col-md-6 text-center'><br />
                    <h2 style="text-align:center;"><b>Opening Pawning Article Report</b></h2>
                    <hr />
                    <table class='table table-light table-striped table-bordered' id="t_opening_pawn_details"
                        style="background-color: transparent; border:1px solid rgb(193, 26, 26); margin-top:15px;">
                        <tr style="background-color:hsl(204, 71%, 70%);">
                            <th>Receipt_Number</th>
                            <th>Invoice_Number</th>
                            <th>Receipt_Type</th>
                            <th>Category</th>
                            <th>Articles</th>
                            <th>Condition</th>
                            <th>Karatage</th>
                            <th>Pawn Weight</th>
                            <th>Total Weight</th>
                            <th>QTY</th>
                            <th>OC</th>
                            <th>BC</th>
                        </tr>
                        <tbody>
                            @foreach ( $redeem as $key=>$redeem)
                            <tr>
                                <td>{{$redeem->Receipt_Number }}</td>
                                <td>{{$redeem->Invoice_Number}}</td>
                                <td>{{$redeem->Receipt_Type }}</td>
                                <td>{{$redeem->Category}}</td>
                                <td>{{$redeem->Articles}}</td>
                                <td>{{$redeem->Condition }}</td>
                                <td>{{$redeem->Karatage}}</td>
                                <td>{{$redeem->Weight }}</td>
                                <td>{{$redeem->Total_Weight}}</td>
                                <td>{{$redeem->QTY}}</td>
                                <td>{{$redeem->OC}}</td>
                                <td>{{$redeem->BC}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>{{$pawnWeight}}</th>
                                <th>{{$totalWeight}}</th>
                                <th>{{$pawnqty}}</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <button onclick="printTablefun()">Print</button>
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

<script>
    function printTablefun() {
        var divToPrint = document.getElementById("t_opening_pawn_details");
        newWin = window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.print();
        newWin.close();
    }

    function printtbodyfun() {
        var divToPrint = document.getElementById("t_opening_pawn_details");
        newWin = window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.print();
        newWin.close();
    }

</script>

{{-- <script type="text/javascript">
	$(document).ready( function () {
	    $('#t_pawn_details').DataTable( {
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
