
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redeem Receipts</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <!-- Custom CSS -->
    <style>
        th, td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-light p-3">

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('home') }}" class="btn btn-secondary">Back</a>
    </div>

    <!-- Filter Form -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="from_date" class="form-label">From Date:</label>
            <input type="date" class="form-control" name="from_date" id="from_date" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-3">
            <label for="to_date" class="form-label">To Date:</label>
            <input type="date" class="form-control" name="to_date" id="to_date" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- Redeem Table -->
    <div class="card p-4 shadow">
        <div class="table-responsive">
            <table id="t_redeem_sums" class="table table-bordered table-striped display nowrap w-100">
                <thead class="table-dark">
                <tr class="styled-table">
                 <th>Customer NIC</th>
                 <th>Customer Name</th>
                 <th>Customer Address</th>
                 <th>Customer Phone</th>
                 <th>Receipt Type</th>
                 <th>Receipt Number</th>
                 <th>Receipt Date</th>
                 <th>IsRedeemed</th>
                 <th>Amount</th>
                 <th>Interest</th>
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
                 <td>{{$receipts->Receipt_Date}}</td>
                 <td>{{$receipts->IsRedeemed}}</td>
                 <td>{{$receipts->Amount}}</td>
                 <td>{{$receipts->Interest}}</td>
             </tr>
         @endforeach
         </tbody>

         <tfoot>
             <tr>
                 <td colspan="8"><center> <b> Total </b> </center></td>
                 <td><b> {{$amount}} </b></td>
                 <td><b> {{$Interest}} </b></td>
             </tr>
         </tfoot>
     </table>
        </div>
    </div>

    <!-- JS CDN Includes -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <!-- Export Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

    <!-- DataTables Init -->
    <script>
        $(document).ready(function () {
            $('#t_redeem_sums').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'pdfHtml5',
                    'print'
                ]
            });

            // Set default to_date to today
            document.getElementById('to_date').value = new Date().toISOString().slice(0, 10);
        });
    </script>

</body>
</html>