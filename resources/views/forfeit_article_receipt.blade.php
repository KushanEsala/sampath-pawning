@extends('layouts.topnavbar')
@extends('layouts.sidebar')
@section('content')
<style>
.article-receipts { width:100%; table-layout:fixed; }
.article-receipts td, .article-receipts th { white-space:normal; overflow-wrap:anywhere; vertical-align:middle; padding:.65rem; }
.article-detail > td { background:#f7f8fc; padding:1rem; }
.article-detail table { width:100%; table-layout:fixed; }
</style>
<div class="main-wrapper"><div class="page-wrapper"><div class="content container-fluid">
<h4>Forfeit Article List</h4>
<p class="text-muted">Select forfeited receipts to move all their available articles into sale stock. Completed receipts disappear from this working list; their history is retained.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(session('forfeit_print_ids'))<div class="alert alert-info">Transfer completed. <a id="transfer-print" target="_blank" href="{{ route('forfeit.articles.transfer.print', ['events'=>session('forfeit_print_ids')]) }}">Print selected receipts</a></div>@endif
<div class="card"><div class="card-body">
<form method="GET" action="{{ route('forfeit_article_receipt') }}" class="row g-2 align-items-end">
<div class="col-md-4"><label for="article_receipt">Receipt Number</label><input id="article_receipt" name="receipt_number" class="form-control" value="{{ request('receipt_number') }}"></div>
<div class="col-auto"><label for="article_page_size">Rows per page</label><select id="article_page_size" name="per_page" class="form-control">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" @selected((int) request('per_page',25) === $size)>{{ $size }}</option>@endforeach</select></div>
<div class="col-auto"><button class="btn btn-primary">Search</button> <a class="btn btn-outline-secondary" href="{{ route('forfeit_article_receipt') }}">Clear</a></div>
</form></div></div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('items.storeFromForfeit') }}" id="article-transfer-form">
@csrf
<div class="d-flex flex-wrap align-items-center gap-3 mb-3"><label><input type="checkbox" id="select-all-receipts"> Select this page</label><button class="btn btn-danger" type="submit">Forfeit selected &amp; print</button><span class="text-muted small">This transfers stock; it does not record a customer payment.</span></div>
<table class="table table-bordered article-receipts">
<colgroup><col style="width:7%"><col style="width:10%"><col style="width:14%"><col style="width:15%"><col style="width:32%"><col style="width:10%"><col style="width:12%"></colgroup>
<thead><tr><th>Select</th><th>View</th><th>Receipt No</th><th>Stock No<br><small>Invoice No</small></th><th>Customer / NIC</th><th>Articles</th><th>Weight</th></tr></thead><tbody>
@forelse($recipts as $receipt)
<tr><td><input type="checkbox" name="selected_receipts[]" value="{{ $receipt->id }}" aria-label="Select receipt {{ $receipt->Receipt_Number }}"></td><td><button type="button" class="btn btn-success btn-sm" data-article-details="articles-{{ $receipt->id }}" aria-expanded="false" aria-controls="articles-{{ $receipt->id }}">View</button></td><td>{{ $receipt->Receipt_Number }}</td><td>{{ $receipt->Invoice_Number ?: '—' }}</td><td>{{ $receipt->current_customer_name }}<br><small>{{ $receipt->Customer_NIC }}</small></td><td>{{ $receipt->available_articles->sum('QTY') }}</td><td>{{ number_format($receipt->available_articles->sum('Weight'),3) }}</td></tr>
<tr class="article-detail" id="articles-{{ $receipt->id }}" hidden><td colspan="7"><p>Ticket Number: {{ $receipt->Ticket_Number ?: '—' }}</p>
<table class="table table-sm table-bordered"><thead><tr><th>Category / Article</th><th>Condition</th><th>Karatage</th><th>Weight</th><th>Quantity</th><th>Value</th></tr></thead><tbody>
@foreach($receipt->available_articles as $article)<tr><td>{{ $article->category }} / {{ $article->Item_description }}</td><td>{{ $article->Brand }}</td><td>{{ $article->Make }}</td><td>{{ $article->Weight }}</td><td>{{ $article->QTY }}</td><td>{{ number_format($article->purchasePrice,2) }}</td></tr>@endforeach
</tbody></table></td></tr>
@empty<tr><td colspan="7" class="text-center text-muted">No forfeited receipts have articles awaiting transfer.</td></tr>@endforelse
</tbody></table></form>
@include('partials.report-pagination', ['paginator'=>$recipts])
</div></div></div></div></div>
<script>
document.getElementById('select-all-receipts').addEventListener('change', function () {
document.querySelectorAll('[name="selected_receipts[]"]').forEach(box => box.checked = this.checked);
});
document.querySelectorAll('[data-article-details]').forEach(function (button) {
button.addEventListener('click', function () { var row = document.getElementById(button.dataset.articleDetails); var opening = row.hasAttribute('hidden'); row.toggleAttribute('hidden', !opening); button.setAttribute('aria-expanded', String(opening)); button.textContent = opening ? 'Hide' : 'View'; });
});
document.getElementById('article-transfer-form').addEventListener('submit', function (event) {
if (!document.querySelector('[name="selected_receipts[]"]:checked')) { event.preventDefault(); alert('Select at least one receipt.'); }
else if (!confirm('Move all articles of the selected receipts into sale stock?')) event.preventDefault();
});
@if(session('forfeit_print_ids'))
window.addEventListener('load', function () { window.open(document.getElementById('transfer-print').href, '_blank'); });
@endif
</script>
@endsection
