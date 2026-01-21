<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseStatusController extends Controller
{
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'quoteId' => 'required|integer|exists:quotes,id',
            'status' => 'required|string|in:pending,partial,attended',
        ]);

        $status = $validated['status'];

        $warehouseRecord = \App\Models\QuoteWarehouse::updateOrCreate(
            ['quote_id' => $validated['quoteId']],
            [
                'status' => $status,
                'employee_id' => Auth::user()?->employee_id, // If user has no employee_id, this will be null, which is fine for now
            ]
        );

        // Separate check for timestamp to avoid overwriting existing timestamp if just updating something else
        // (though in this context we only update status)
        if ($status === 'attended' && !$warehouseRecord->attended_at) {
            $warehouseRecord->attended_at = now();
            $warehouseRecord->saveQuietly();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
