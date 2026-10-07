<div class="form-section">
    <h5 class="section-title">Penalty Letter &amp; Forfeit Reminder Intervals</h5>
    <p class="text-muted">The first letter is printable on the receipt's effective expiry date, including older receipts. Enter calendar days for the later stages; late printing does not move their scheduled dates.</p>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label" for="{{ $prefix }}letter_1_days">Expiry to 1st letter</label>
            <input type="number" class="form-control" id="{{ $prefix }}letter_1_days" name="{{ $prefix }}letter_1_days" value="0" readonly>
            <small class="text-muted">Same day as expiry</small>
        </div>
        @foreach(['letter_2_days' => '1st to 2nd letter', 'letter_3_days' => '2nd to 3rd letter', 'forfeit_reminder_days' => '3rd letter to Forfeit Reminder'] as $field => $label)
            <div class="col-md-3 mb-3">
                <label class="form-label" for="{{ $prefix.$field }}">{{ $label }} (days)</label>
                <input type="number" class="form-control" id="{{ $prefix.$field }}" name="{{ $prefix.$field }}" min="0" max="3650" step="1" value="{{ old($prefix.$field, 21) }}" required>
            </div>
        @endforeach
    </div>
</div>
