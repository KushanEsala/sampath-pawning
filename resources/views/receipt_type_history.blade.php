<div class="mb-3 text-muted">{{ $typeName }} · {{ $versions->count() }} saved version(s). Values below come from each saved row, not the current type.</div>
@forelse($versions as $version)
    <section class="border rounded-3 p-3 mb-3 bg-white">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div><strong class="fs-5">{{ $version->receiptname }}</strong> <small class="text-muted">Version #{{ $version->id }}</small></div>
            <span class="badge {{ $version->is_active && !$version->effective_to ? 'bg-success' : 'bg-secondary' }}">{{ $version->is_active && !$version->effective_to ? 'Current' : 'Previous' }}</span>
        </div>
        <div class="row g-2 small">
            <div class="col-md-3"><strong>Effective from:</strong> {{ $version->effective_from?->format('Y-m-d') ?: 'Legacy / unknown' }}</div>
            <div class="col-md-3"><strong>Effective to:</strong> {{ $version->effective_to?->format('Y-m-d') ?: 'Open' }}</div>
            <div class="col-md-3"><strong>Saved:</strong> {{ $version->created_at?->format('Y-m-d H:i:s') ?: 'Unknown' }}</div>
            <div class="col-md-3"><strong>Last changed:</strong> {{ $version->updated_at?->format('Y-m-d H:i:s') ?: 'Unknown' }}</div>
            <div class="col-md-3"><strong>Rate 1:</strong> {{ $version->rate1 }}% / {{ $version->period1 }} days</div>
            <div class="col-md-3"><strong>Rate 2:</strong> {{ $version->rate2 }}% / {{ $version->period2 }} days</div>
            <div class="col-md-3"><strong>Rate 3:</strong> {{ $version->rate3 }}% / {{ $version->period3 }} days</div>
            <div class="col-md-3"><strong>Validity:</strong> {{ $version->validPeriod }} days</div>
            <div class="col-md-3"><strong>Service charge:</strong> Rs. {{ number_format((float) $version->service_charge, 2) }}</div>
            <div class="col-md-3"><strong>Postage:</strong> Rs. {{ number_format((float) $version->Postage_charge, 2) }}</div>
            <div class="col-md-3"><strong>Service &lt; 25,000:</strong> Rs. {{ number_format((float) $version->s_charge_less, 2) }}</div>
            <div class="col-md-3"><strong>Service &gt; 25,000:</strong> {{ $version->s_charge_greater }}%</div>
            <div class="col-md-3"><strong>1st letter:</strong> {{ $version->letter_1_days ?? 21 }} days</div>
            <div class="col-md-3"><strong>2nd letter:</strong> {{ $version->letter_2_days ?? 21 }} days</div>
            <div class="col-md-3"><strong>3rd letter:</strong> {{ $version->letter_3_days ?? 21 }} days</div>
            <div class="col-md-3"><strong>Forfeit reminder:</strong> {{ $version->forfeit_reminder_days ?? 21 }} days</div>
        </div>
    </section>
@empty
    <p class="alert alert-info">No saved versions were found for this type.</p>
@endforelse
