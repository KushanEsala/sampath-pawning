

@if($summary->count() > 0)

@else
<p>No results found.</p>

@endif


<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="stylesheet" href="href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<link rel="stylesheet"  href="vendor/DataTables/datatables.min.css">
	<link rel="stylesheet"  href="style.css">
	<script src="vendor/jquery/jquery-1.11.2.min.js" type="text/javascript"></script>
    <script src="vendor/DataTables/datatables.min.js" type="text/javascript"></script>
<title>Daily Report</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script>
    $(document).ready(function() {
	// DataTable initialisation
	$('#sum_table').DataTable(
		{
			"paging": false,
			"autoWidth": true,
			"footerCallback": function ( row, data, start, end, display ) {
				var api = this.api();
				nb_cols = api.columns().nodes().length;
				var j = ;
				while(j < nb_cols){
					var pageTotal = api
                .column( j, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return Number(a) + Number(b);
                }, 0 );
          // Update footer
          $( api.column( j ).footer() ).html(pageTotal);
					j++;
				}
			}
		}
	);
});
</script>
<style type="text/css">
    body{
        font-family: 'Roboto Condensed', sans-serif;
    }
    .m-0{
        margin: 0px;
    }
    .p-0{
        padding: 0px;
    }
    .pt-5{
        padding-top:5px;
    }
    .mt-10{
        margin-top:10px;
    }
    .text-center{
        text-align:center !important;
    }
    .w-100{
        width: 100%;
    }
    .w-50{
        width:50%;
    }
    .w-85{
        width:85%;
    }
    .w-15{
        width:15%;
    }
    .logo img{
        width:45px;
        height:45px;
        padding-top:30px;
    }
    .logo span{
        margin-left:8px;
        top:19px;
        position: absolute;
        font-weight: bold;
        font-size:25px;
    }
    .gray-color{
        color:#741ad4;
    }
    .text-bold{
        font-weight: bold;
    }
    .border{
        border:1px solid black;
    }
    table tr,th,td{
        border: 1px solid #d2d2d2;
        border-collapse:collapse;
        padding:7px 8px;
    }
    table tr th{
        background: #F4F4F4;
        font-size:15px;
    }
    table tr td{
        font-size:13px;
    }
    table{
        border-collapse:collapse;
    }
    .box-text p{
        line-height:10px;
    }
    .float-left{
        float:left;
    }
    .total-part{
        font-size:16px;
        line-height:12px;
    }
    .total-right p{
        padding-right:20px;
    }



    th, td {
      padding-top: 5px;
      padding-bottom: 10px;
      padding-left: 10px;
      padding-right: 30px;
    }
    </style>
</head>


<body>

    <div class="card-body">
        <div class="table-responsive">
            <div class="table-data">
<form style align="center" action="" method="get">
    <label for="from_date">From Date:</label>
    <input type="date" name="from_date" id="from_date" >

    <label for="to_date">To Date:</label>
    <input type="date" name="to_date" id="to_date" >

    <button type="submit">Search</button>
    <button onclick="printTablefun()">Print</button>

    <Button type="button"><a href="{{route("home")}}"> Back</a></Button>

</form>
<div class="head-title">
    <h2 class="text-center m-0 p-0"><b></b></h2><br>
    {{-- <h5 class="text-center m-0 p-0"><b>Smart Omega</b></h5>
    <h5 class="text-center m-0 p-0"><b>Kandy</b></h5>
    <h5 class="text-center m-0 p-0"><b>0775689564</b></h5> --}}

</div>


    <div style="clear: both;"></div>
</div>
<div class="table-section bill-tbl w-100 mt-5">
    <table class="table w-100 mt-5">
        <tr>
            <th class="w-50"><h3 class="text-center m-0 p-0">Day Summary Report</h3><h5 style="width:70%" align="right" >Branch - {{$BC}}</h5></th>


        </tr>




 <table id="sum_table"  style="width:70%" align="center" border="1" cellpadding="20" class="table  table-bordered">
    <thead  align="center">

        <tr>
            <thead>
              <th bgcolor="gray" style="text-align:center;" colspan="2">Category</th>
              <th bgcolor="gray" style="text-align:center;">In</th>
              <th bgcolor="gray"style="text-align:center;">Out</th>
              <th bgcolor="gray"style="text-align:center;">Total</th>
            </thead>
          </tr>
          <tbody
          <tr>
           <td colspan="2">Opening Balance</td>
            <td style align="right"></td>
            <td style align="right">
            </td>
            <td style align="right">{{$yesterdayCashBalance}}</td>
          </tr>
          {{-- <tr>
            <td colspan="2">Powning</td>
             <td style align="right"></td>
             <td style align="right"></td>
           </tr> --}}
           <tr>
            <td colspan="2">Redeem</td>
             <td style align="right">{{$Payable_Total}}</td>
             <td style align="right"></td>
             <td style align="right"></td>
           </tr>

             <tr>
            <td colspan="2">Part Payment</td>
             <td style align="right">{{$TPawnPaymentamount}}</td>
             <td style align="right"></td>
             <td style align="right"></td>
           </tr>

           <tr>
            <td colspan="2">Intrest</td>
             <td style align="right">{{$Interest}}</td>
             <td style align="right"></td>
           </tr>
           <tr>
            <td colspan="2">Document Charge</td>
             <td style align="right">{{$Document_Charges}}</td>
             <td style align="right"></td>
           </tr>
           <tr>
            <td colspan="2">Head Office Cash</td>
             <td style align="right">
                {{$amountt}}</td>
             <td style align="right"></td>
           </tr>
           <tr>
            <td colspan="2">Stam Duty</td>
             <td style align="right">{{$Stamp_Fee}}</td>
             <td style align="right"></td>
           </tr>
           <tr>
            <td colspan="2">Western Union RCVD</td>
             <td style align="right"></td>
             <td style align="right"></td>
           </tr>
           <tr>
            <td colspan="2">Other Recipt</td>
             <td style align="right"></td>
             <td style align="right"></td>
           </tr>


          <tr>
            <td style align="right" colspan="2"><b>IN TOTAL</td>

             <td style align="right"></td>
                <td></td>
             <td style align="right"><b>{{$tolin}}</td>
           </tr>

           <tr>
            <td colspan="2">Pawning</td>
             <td style align="right"></td>
             <td style align="right">{{$Total_Amount}}</td>
             <td style align="right"></td>
           </tr>
            <tr>
            <td colspan="2">RePawning</td>
             <td style align="right"></td>
             <td style align="right">{{$TRepawningSumAmount}}</td>
             <td style align="right"></td>
           </tr>
           <!-- Dynamic Expenses -->
@foreach($expenses as $key => $expense)
<tr>
    <td colspan="2">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
    <td style align="right"></td>
    <td style align="right">{{ $expense['formatted'] }}</td>
    <td style align="right"></td>
</tr>
@endforeach

               <tr>
                <td align="right" colspan="2"><b>OUT TOTAL</td>
                 <td style align="right"></td>
                 <td style align="right"><b></td>
                 <td style align="right"><b>{{$totalout}}</td>
               </tr>



            <tr>
             <td style align="right" colspan="2"><b>NET BALANCE</td>
                 <td style align="right"></td>
                 <td></td>
              <td style align="right"><b>{{$net}}</td>

            </tr>


          </tbody>

                <tfoot>




                   </tfoot>

        </table>


    </div>
</div>
</div>

</body>
<script>
    function printTablefun()
    {
       var divToPrint=document.getElementById("sum_table");
       newWin= window.open("");
       newWin.document.write(divToPrint.outerHTML);
       newWin.print();
       newWin.close();
    }

    function printtbodyfun()
    {
       var divToPrint=document.getElementById("sum_table");
       newWin= window.open("");
       newWin.document.write(divToPrint.outerHTML);
       newWin.print();
       newWin.close();
    }
    </script>


<script>
    // Add the CSRF token to your JavaScript
    var csrf_token = '{{ csrf_token() }}';

    // Your existing JavaScript...

    function saveData() {
        var netBalance = parseFloat("{{$net}}");
        var fromDate = $("#from_date").val();
        var toDate = $("#to_date").val();

        $.ajax({
            type: "POST",
            url: "{{ route('saveData') }}", // Assuming the route name is 'saveData'
            data: {
                _token: csrf_token,
                netBalance: netBalance,
                from_date: fromDate,
                to_date: toDate
            },
            success: function (response) {
                console.log('Data saved successfully:', response);
            },
            error: function (error) {
                console.error('Error saving data:', error);
            }
        });
    }

    // Your existing JavaScript...
</script>

<script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.1.0/bootstrap.min.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.css" rel="stylesheet" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
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
      $('#order_data').DataTable(    //database table name
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

<script>
    var dateObj = new Date();
    document.getElementById('to_date').value = dateObj.toISOString().slice(0, 10);

</script>


 <script>
    // Add the CSRF token to your JavaScript
var csrf_token = '{{ csrf_token() }}';

// Your existing JavaScript...

function saveData() {
    var netBalance = parseFloat("{{$net}}");
    var fromDate = $("#from_date").val();
    var toDate = $("#to_date").val();

    $.ajax({
        type: "POST",
        url: "",
        data: {
            _token: csrf_token,
            netBalance: netBalance,
            from_date: fromDate,
            to_date: toDate
        },
        success: function (response) {
            console.log('Data saved successfully:', response);
        },
        error: function (error) {
            console.error('Error saving data:', error);
        }
    });
}

// Your existing JavaScript...

 </script>

</html>