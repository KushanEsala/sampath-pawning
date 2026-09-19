@if($Advance_Payments->count() > 0)
<h2>Advance Payment Reports</h2>


@else
<p>No results found.</p>

@endif



<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<script>
    function print () {
 var printDiv = document.getElementById("divPrint");
 var printWindow = window.open('', '', 'left=0, top=0, width=800, height=500, toolbar=0, scrollbars=0, status=0');
 printWindow.document.write(printDiv.innerHTML);
 printWindow.document.close();
 printWindow.focus();
 printWindow.print();
}
</script>



<script>
    // Select wrong element
// Error as #demo is the `div` element
$('#cancel_pawnings').DataTable()

// Selector too broad.
// Error as `.display` is applied to both the div and the table
$('.display').DataTable();
</script>
<title>Cancel_Pawning Report</title>
<style>

    th, td {
      padding-top: 5px;
      padding-bottom: 10px;
      padding-left: 10px;
      padding-right: 30px;
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

<div>
    <button id="print" onclick="print()">Print</button><br><br>
   </div>



<div id="divPrint"  style="border: 1px  black; padding: 5px; width:1000px height:1000" >
    <div class="table">
        
        <table class="display" id="summaries" >
            <thead style="padding-left:30px">
                <tr class="table-secondary">
                    <th>S_No</th>
                    <th>Bill_type</th>
                    <th>Bill_No</th>
                    <th>Cus_Name</th>
                    <th>Cus_Address</th>
                    <th>NIC</th>
                    <th>Mobile</th>
                    <th>Total_Weight</th>
                    <th>Paid_Date_Time</th>
                    <th>Amount</th>
                    
                    
                   
                    

                </tr>
            </thead>
            <tbody>
            @foreach ( $Advance_Payments as $key=>$Advance_Payments)
                <tr>
                    <td>{{$Advance_Payments->S_No }}</td>
                    <td>{{$Advance_Payments->Bill_type}}</td>
                    <td>{{$Advance_Payments->Bill_No }}</td>
                    <td>{{$Advance_Payments->Cus_Name}}</td>
                    <td>{{$Advance_Payments->Cus_Address }}</td>
                    <td>{{$Advance_Payments->NIC }}</td>
                    <td>{{$Advance_Payments->Mobile }}</td>
                    <td>{{$Advance_Payments->Total_Weight }}</td>
                    <td>{{$Advance_Payments->Paid_Date_Time }}</td>
                    <td>{{$Advance_Payments->Amount }}</td>
                    
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
        $('advance_payments').DataTable( //database table name
            {
                dom: 'Bfrtip',
                buttons: [


                ],
            }
        );

    });
</script>

</html>
