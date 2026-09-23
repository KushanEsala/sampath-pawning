<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt History {{ $history['receipt']->Receipt_Number }}</title>
    <style>
        @page { margin: 14mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 18px; background: #fff; }
        h1, h2 { margin: 0 0 10px; font-size: 16px; }
        h2 { margin-top: 18px; font-size: 13px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; }
        .box { border: 1px solid #000; padding: 7px; overflow-wrap: anywhere; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 10px 0 16px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: top; color: #000; background: #fff; }
        .amount, .ledger-money { text-align: right; font-variant-numeric: tabular-nums; }
        .ledger-date { width: 13%; }
        .ledger-description { width: 51%; }
        .ledger-money { width: 12%; }
        .ledger-balance, .ledger-title { font-weight: bold; }
        .ledger-summary, .ledger-meta, .ledger-operator { color: #000; font-size: 10px; line-height: 1.35; margin-top: 2px; overflow-wrap: anywhere; }
        .ledger-detail-grid { display: block; }
        .ledger-detail-row td { padding: 5px 8px; }
        .fa, .fas, .far { display: none; }
        .print { margin-bottom: 16px; }
        @media print {
            .print { display: none; }
            tr { break-inside: avoid; }
        }
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
<h2>Complete Ledger History — Newest First</h2>
@include('partials.receiptLedgerTable', ['ledgerRows' => $history['ledger'], 'ledgerExpanded' => true])
</body></html>
