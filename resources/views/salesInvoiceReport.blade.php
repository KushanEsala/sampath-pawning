<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Invoice Report</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- DataTables Bootstrap 5 CSS -->
  <link
    rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"
  />
  <link
    rel="stylesheet"
    href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css"
  />

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f9fafd;
      padding: 30px 0;
    }
    .container {
      max-width: 1200px;
    }
    h1 {
      margin-bottom: 30px;
      font-weight: 700;
      color: #343a40;
      text-align: center;
    }
    table.dataTable thead th {
      background-color: #343a40;
      color: white;
    }
    .dataTables_wrapper .dt-buttons button {
      margin-right: 8px;
    }
  </style>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

  <!-- Bootstrap 5 JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Buttons extension + Bootstrap styling -->
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

  <!-- JSZip for Excel -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

  <!-- pdfmake for PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
</head>
<body>

  <div class="container">
    <h1>Invoice Report</h1>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
@php
  // Group data by invoice no
  $groupedData = $data->groupBy('Invoice_no');
@endphp

<form action="" method="get" class="row g-3 align-items-end mb-4">
  <div class="col-auto">
    <label for="from_date" class="form-label fw-semibold">
      <i class="bi bi-calendar-event me-1"></i> From Date
    </label>
    <input
      type="date"
      name="from_date"
      id="from_date"
      class="form-control"
      placeholder="Select start date"
    />
  </div>

  <div class="col-auto">
    <label for="to_date" class="form-label fw-semibold">
      <i class="bi bi-calendar-event me-1"></i> To Date
    </label>
    <input
      type="date"
      name="to_date"
      id="to_date"
      class="form-control"
      placeholder="Select end date"
    />
  </div>

  <div class="col-auto">
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-search me-1"></i> Search
    </button>
  </div>


  <div class="col-auto">
    <a href="{{ route('home') }}" class="btn btn-info text-white">
      <i class="bi bi-house me-1"></i> Home
    </a>
  </div>
</form>



<table id="t_redeem_sums" class="table table-bordered table-striped nowrap" style="width:100%">
  <thead>
    <tr>
      <th>Invoice No</th>
      <th>Invoice Date</th>
      <th>Customer Name</th>
      <th>Customer NIC</th>
      <th>Item Code</th>
      <th>Description</th>
      <th>QTY</th>
      <th>Unit Price</th>
      <th>Net Value</th>
      <th>Net Amount (Invoice)</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($groupedData as $invoiceNo => $items)
      <tr>
        <td>{{ $invoiceNo }}</td>
        <td>{{ $items->first()->Invoice_date }}</td>
        <td>{{ $items->first()->Customer_Name }}</td>
        <td>{{ $items->first()->Customer_NIC }}</td>
        <td>
          {!! $items->pluck('Item_code')->implode('<br>') !!}
        </td>
     
            <td>
            <ul style="padding-left: 18px; margin: 0;">
                @foreach ($items->pluck('Item_description') as $desc)
                <li>{{ $desc }}</li>
                @endforeach
            </ul>
            </td>
        <td>
          {!! $items->pluck('QTY')->implode('<br>') !!}
        </td>
        <td>
          {!! $items->pluck('Unit_price')->map(fn($p) => number_format($p, 2))->implode('<br>') !!}
        </td>
        <td>
          {!! $items->pluck('Net_value')->map(fn($v) => number_format($v, 2))->implode('<br>') !!}
        </td>

        <!-- Net Amount is total for the invoice, so just show first or sum -->
        <td>{{ number_format($items->first()->Net_Amount, 2) }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="10" class="text-center">No Records Found</td>
      </tr>
    @endforelse
  </tbody>

  @php
    $totalNetAmount = $groupedData->sum(function ($items) {
        return $items->first()->Net_Amount;
    });
@endphp
    <tfoot>
        <tr>
            <td colspan="7" class="text-end"><strong>Total Net Amount:</strong></td>
            <td colspan="3"> <strong> {{ number_format($totalNetAmount, 2) }}</strong></td>
        </tr>
    </tfoot>
  
</table>




    </div>
  </div>

  <script>
$(document).ready(function () {
  $("#t_redeem_sums").DataTable({
    dom: "Bfrtip",
    buttons: [
      {
        extend: "excelHtml5",
        className: "btn btn-success btn-sm",
        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
      },
      {
        extend: "pdfHtml5",
        className: "btn btn-danger btn-sm",
        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
      },
      {
        extend: "print",
        className: "btn btn-primary btn-sm",
        text: '<i class="bi bi-printer"></i> Print',
      },
    ],
    scrollX: true,
    pageLength: 100, // <-- add this line
  });
});

  </script>


    <script>
        document.getElementById('to_date').valueAsDate = new Date();
    </script>


  <!-- Bootstrap Icons for button icons (optional) -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
  />
</body>
</html>