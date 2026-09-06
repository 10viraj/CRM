<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['invoice.lead'])->latest()->paginate(15);
        return view('payments.index', compact('payments'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->amount_due,
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated, $invoice) {
            // Create payment
            $invoice->payments()->create([
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes']
            ]);

            // Update invoice balances
            $newAmountPaid = $invoice->amount_paid + $validated['amount'];
            $newAmountDue = $invoice->grand_total - $newAmountPaid;

            $status = $invoice->status;
            if ($newAmountDue <= 0) {
                $status = 'paid';
            }

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'amount_due' => $newAmountDue,
                'status' => $status
            ]);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment recorded successfully.');
    }
}
