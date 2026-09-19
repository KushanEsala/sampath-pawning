<head>
    <style>
    .styled-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .styled-table thead th {
        background-color: #4183ec;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        padding: 12px;
        border-bottom: 2px solid #dee2e6;
    }

    .styled-table tbody tr {
        background-color: #f9f9f9;
        transition: background-color 0.3s;
    }

    .styled-table tbody tr:hover {
        background-color: #e0f0ff;
    }

    .styled-table td {
        padding: 12px;
        vertical-align: middle;
        color: #333;
        font-size: 15px;
    }

    .styled-table th,
    .styled-table td {
        text-align: center;
    }
    </style>
</head>


<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-6">
        <table class="styled-table text-center">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Receipt Number</th>
                    <th>Comment</th>
                    <th>Comment Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($feedbacks as $feedback)
                <tr>
                    <td>{{ $feedback->Customer_Name }}</td>
                     <td>{{ $feedback->Receipt_Number }}</td>
                    <td>{{ $feedback->feedback }}</td> <!-- You might want to change this to actual 'comment' field -->
                    <td>{{ $feedback->Current_date }}</td> <!-- Or the comment date field -->
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

