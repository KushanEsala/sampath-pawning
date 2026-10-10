# Customer limit floor — 2026-10-10

No SQL or Laravel migration is required for this update. No customer or receipt data was changed.

Both Customer Management pages now compare a proposed positive amount limit with the customer's current active capital, and a proposed positive count limit with the current number of active receipts, across all branches. The existing `CustomerPawnPolicy::exposure()` calculation is reused: non-redeemed, non-forfeited main receipts plus unmatched active opening receipts. The forms show those current values and warn before saving. The server repeats the check inside the save transaction so a direct request cannot bypass it. An equal or higher limit is accepted; blank or zero retains the established no-limit meaning.

An existing saved limit that is already below the active exposure remains in the database until a user edits that customer. The user must raise or remove it before saving through either page; this update does not silently rewrite historical limit values.

Local verification: 68 unit tests passed, Blade views compiled, and the exposure route was registered. The local database was not modified by this code change.
