# Rathnapura receipt 5542: payment and history audit — 2026-10-11

Never run Laravel migrations. This audit made no database changes.

The production database has two distinct receipts numbered 5542: Balangoda `BC=001`, pawn-summary ID 9451, has no printed letters; Rathnapura `BC=002`, pawn-summary ID 13822, has two printed letters. The part-payment search and save paths both constrain the receipt by the logged-in user's `BC`. The screenshot's required Rs. 7,752.95 matches Rathnapura, not Balangoda: interest Rs. 7,502.95 plus two saved letter/postage charges of Rs. 125. The Rs. 60 service charge in the read-only general calculation is excluded from part payment by design.

The rejection is the existing global business rule in `PawningPartPaymentController`: once any letter is printed, a part payment must cover the full interest plus letter arrears before reactivating the receipt. It is not a cross-branch lookup. Accepting less while keeping the receipt in the letter cycle requires a separate decision about capitalization, unpaid postage, interest dates, and whether the letter stage advances; removing that check alone would silently reactivate the receipt and could double-count postage.

This audit also found a display issue: Rathnapura receipt 5542 has a saved first-letter date and Rs. 125 but no `arrears_letter_events` row for that legacy print, while its second letter has an event. Receipt history previously showed only the second letter whenever any event existed. The history display now includes saved letter dates lacking a matching event, without inserting or inventing database events and without duplicating event-backed letters.
