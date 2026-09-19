<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt History {{ $history['receipt']->Receipt_Number }}</title>
    <style>
        body{font-family:Arial,sans-serif;font-size:12px;color:#111;margin:24px} h1,h2{margin:0 0 12px} .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px}.box{border:1px solid #aaa;padding:8px}table{width:100%;border-collapse:collapse;margin:12px 0 20px}th,td{border:1px solid #777;padding:6px;text-align:left}th{background:#eee}.amount{text-align:right}.print{margin-bottom:16px}@media print{.print{display:none}}
    </style>
</head>
<body>
@php($receipt = $history['receipt'])
@php($customer = $history['customer'])
@php($financial = $history['financial'])
<button class="print" onclick="window.print()">Print</button>
<h1>Receipt History</h1>
<div class="grid">
    <div class="box"><strong>Receipt No:</strong> {{ $receipt->Receipt_Number }}<br><strong>Stock No (Invoice No):</strong> {{ $receipt->Invoice_Number }}<br><strong>Ticket No:</strong> {{ $receipt->Ticket_Number }}</div>
    <div class="box"><strong>Customer:</strong> {{ $customer ? trim($customer->First_name.' '.$customer->Middle_name.' '.$customer->Last_name) : $receipt->Customer_Name }}<br><strong>Address:</strong> {{ optional($customer)->Address_1 ?? $receipt->Customer_Address }}<br><strong>Telephone:</strong> {{ optional($customer)->Contact_1 ?? $receipt->Customer_Phone }}</div>
    <div class="box"><strong>Receipt Date:</strong> {{ optional($receipt->Receipt_Date)->format('Y-m-d') }}<br><strong>Expiry Date:</strong> {{ optional($receipt->Final_date)->format('Y-m-d') }}<br><strong>Printed:</strong> {{ now()->format('Y-m-d H:i') }}</div>
</div>
<h2>Current Amounts</h2>
<table><thead><tr><th>Principal</th><th>Interest</th><th>Service</th><th>Letter Charges</th><th>Total Interest Payable</th><th>Redemption Total</th></tr></thead><tbody><tr>
    <td class="amount">{{ number_format($financial['principal'],2) }}</td><td class="amount">{{ number_format($financial['interest'],2) }}</td><td class="amount">{{ number_format($financial['service_charge'],2) }}</td><td class="amount">{{ number_format($financial['letter_charge'],2) }}</td><td class="amount">{{ number_format($financial['arrears_total'],2) }}</td><td class="amount">{{ number_format($financial['redemption_total'],2) }}</td>
</tr></tbody></table>
<h2>History — Newest First</h2>
<table><thead><tr><th>Date/Time</th><th>Type</th><th>Amount</th><th>Details</th></tr></thead><tbody>
@foreach($history['timeline'] as $event)<tr><td>{{ $event['date'] }}</td><td>{{ $event['type'] }}</td><td class="amount">{{ $event['amount'] !== null ? number_format($event['amount'],2) : '' }}</td><td>@foreach($event['details'] as $label=>$value)<strong>{{ $label }}:</strong> {{ $value }}@if(!$loop->last)<br>@endif @endforeach</td></tr>@endforeach
</tbody></table>
</body></html>
