<div class="form-section">
    <h5 class="section-title">Penalty Letter &amp; Forfeit Reminder Intervals</h5>
    <p class="text-muted">Enter calendar days. First letter: after expiry. Second/third: after the previous scheduled letter date. Reminder: after the third letter is issued. Use 0 for the same day.</p>
    <div class="row">
        @foreach(['letter_1_days' => 'Expiry to 1st letter', 'letter_2_days' => '1st to 2nd letter', 'letter_3_days' => '2nd to 3rd letter', 'forfeit_reminder_days' => '3rd letter to Forfeit Reminder'] as $field => $label)
            <div class="col-md-3 mb-3">
                <label class="form-label" for="{{ $prefix.$field }}">{{ $label }} (days)</label>
                <input type="number" class="form-control" id="{{ $prefix.$field }}" name="{{ $prefix.$field }}" min="0" max="3650" step="1" value="{{ old($prefix.$field, 21) }}" required>
            </div>
        @endforeach
    </div>
</div>
