<?php

namespace App\Services;

use Illuminate\Http\Request;

/** Keep internal ticket/stock identifiers out of ordinary receipt browsing. */
class ReceiptNumberVisibility
{
    public static function ticket(object|array $receipt, ?Request $request = null): bool
    {
        if (auth()->user()?->isAdmin()) return true;

        $request ??= request();
        $number = is_array($receipt)
            ? trim((string) ($receipt['Ticket_Number'] ?? ''))
            : trim((string) ($receipt->Ticket_Number ?? ''));
        if ($number === '') return false;

        return self::provided($request, 'ticket', $number, 'search_ticket_no', 'ticket_number', 't_number');
    }

    public static function stock(object|array $receipt, ?Request $request = null): bool
    {
        if (auth()->user()?->isAdmin()) return true;

        $request ??= request();
        $number = is_array($receipt)
            ? trim((string) ($receipt['Invoice_Number'] ?? ''))
            : trim((string) ($receipt->Invoice_Number ?? ''));
        if ($number === '') return false;

        return self::provided($request, 'stock', $number, 'search_invoice_no', 'stock_number', 'Stock_Number', 'i_number');
    }

    private static function provided(Request $request, string $kind, string $number, string ...$fields): bool
    {
        if ($request->input('search_type') === $kind
            && trim((string) $request->input('search_query', '')) === $number) {
            return true;
        }

        foreach ($fields as $field) {
            if ($request->filled($field) && trim((string) $request->input($field)) === $number) {
                return true;
            }
        }

        return false;
    }
}
