<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Late Redeem Letter Send List</title>

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

        .styled-table {
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.9em;
            font-family: sans-serif;
            min-width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }

        .styled-table thead tr {
            background-color: #009879;
            color: #ffffff;
            text-align: left;
        }

        .styled-table th,
        .styled-table td {
            padding: 12px 15px;
        }

        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }

        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #009879;
        }
    </style>

    <script>
        $('#myTable').DataTable()
        $('.display').DataTable();
    </script>

</head>

<body>
    {{-- <div class="card shadow p-3 mb-3 bg-body-tertiary rounded" > --}}
    <div class="d-flex justify-content-center profile-container">
        <div class='col-md-6 text-center sort-profile' id='sort-profile'>
            <div class='row'>
                <div class='col-md-6 text-center'><br />
                    @if ($errors->any())
                    <div class="alert alert-danger text-center" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <ul>{{ $error }}</ul>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div styel="background-color: yellow;">
                        <h2 style="text-align:center; background-color:rgb(113, 105, 255); color:#ffffff;"><b>Late Redeem Letter Send List</b></h2><hr/>

                        <div style="display: flex; text-align: center;">
                            <div style="flex: 60%; align-content: center;">
                                <form action="" method="GET">
                                    @csrf
                                    <label for="date">From Date :</label>
                                    <input type="date" name="from_date" id="from_date">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label for="date">To Date :</label>
                                    <input type="date" name="to_date" id="to_date">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <button type="submit" id="submit_1">
                                        Submit &nbsp;<i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                    <button onclick="printTablefun()">
                                        <strong> Print &nbsp;</strong><i class="fa-solid fa-print"></i>
                                    </button>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <Button class="d-inline p-2 text-bg-primary">
                                        <a href="{{route("pawning_late_letters")}}">
                                            Back
                                        </a>
                                        <i class="fa-solid fa-house"></i>
                                    </Button>
                                </form>
                            </div>

                        </div>

                        <table class="display responsive" id="myTable"
                            style="background-color: transparent; border: 1px solid rgb(7, 7, 7); margin-top: 15px;">

                            @if($fromDate && $toDate)
                                <caption style="font-size: 18px; font-weight: bold;">
                                    Late Redeem Letter Send List&nbsp;&nbsp;&nbsp;&nbsp;From: {{$fromDate}}&nbsp;&nbsp;To: {{$toDate}}
                                <caption>
                            @endif
                            <thead class="styled-table">
                                <tr>
                                    <th colspan="2" style="text-align: center">Letter</th>
                                    <th colspan="4" style="text-align: center">Customer</th>
                                    <th colspan="4" style="text-align: center">Receipt</th>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>NIC</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Phone</th>
                                    <th>No</th>
                                    <th>Date</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                </tr>

                            </thead>
                            @php
                                $t_payable_total=0;
                            @endphp
                            <tbody>
                                @foreach ( $recipts as $key=>$recipts)
                                    @php
                                        $t_payable_total = $t_payable_total + $recipts->Amount;
                                    @endphp
                                <tr>
                                    <td>
                                        @if ($recipts->letter_3_date)
                                            {{$recipts->letter_3_date }}
                                        @elseif ($recipts->letter_2_date)
                                            {{$recipts->letter_2_date }}
                                        @else
                                            {{$recipts->letter_1_date }}
                                        @endif
                                    </td>

                                    <td>
                                        @if ($recipts->is_letter_3)
                                        3rd Letter
                                        @elseif ($recipts->is_letter_2)
                                        2nd Letter
                                        @else
                                        1st Letter
                                        @endif
                                    </td>

                                    <td>{{$recipts->Customer_NIC }}</td>
                                    <td>{{$recipts->Customer_Name}}</td>
                                    <td>{{$recipts->Customer_Address }}</td>
                                    <td>{{$recipts->Customer_Phone }}</td>
                                    <td>{{$recipts->Receipt_Number }}</td>
                                    <td>{{$recipts->Receipt_Date }}</td>
                                    <td>{{$recipts->Receipt_Type}}</td>
                                    <td style="text-align: right;">{{$recipts->Amount}}</td>
                                </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="6" style="text-align: center"></td>
                                    <td colspan="3" style="text-align: center"><b>Total :</b></td>
                                    <td style="text-align: right"><b>{{ number_format($t_payable_total,2)}}</b></td>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

{{-- form default date set for today --}}
<script>
    document.getElementById('to_date').valueAsDate = new Date();
</script>

{{-- CSRF token script --}}
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

<script>
    jQuery(document).ready(function ($) {
        $('#myTable').DataTable( //database table name
            {
                dom: 'Bfrtip',
                "paging": false,
                buttons: [
                    'copy',
                    'excel',
                    'csv',
                    'pdf',
                ],
            }
        );

    });

</script>

<script>
    function printTablefun() {
        var divToPrint = document.getElementById("myTable");

        // Create a new window
        var newWin = window.open("");

        // Write the table HTML content to the new window
        newWin.document.write(`
            <html>
                <head>
                    <title>Late Redeem Letter Send List</title>
                    <style>
                        table {
                            border-collapse: collapse;
                            width: 100%;
                        }
                        th, td {
                            border: 1px solid #dddddd;
                            text-align: left;
                            padding: 8px;
                        }
                        th {
                            background-color: #f2f2f2;
                        }
                    </style>
                </head>
                <body>
                    ${divToPrint.outerHTML}
                </body>
            </html>
        `);

        // Print the new window
        newWin.print();

        // Close the new window after printing
        newWin.close();
    }
</script>

</html>
