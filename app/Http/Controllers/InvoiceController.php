<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('lead')->latest()->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $leads = Lead::all();
        $products = Product::where('status', 'active')->get();
        // Generate a new invoice number
        $lastInvoice = Invoice::latest('id')->first();
        $nextNumber = 'INV-' . str_pad(($lastInvoice ? $lastInvoice->id + 1 : 1), 5, '0', STR_PAD_LEFT);
        
        return view('invoices.create', compact('leads', 'products', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'lead_id' => 'required|exists:leads,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'notes' => 'nullable|string',
            
            // Arrays from Alpine JS
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'lead_id' => $validated['lead_id'],
                'date' => $validated['date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'subtotal' => 0, // updated below
                'tax' => 0,
                'discount' => 0,
                'grand_total' => 0,
                'amount_paid' => 0,
                'amount_due' => 0
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $lineTotal = ($item['quantity'] * $item['unit_price']) + $item['tax'] - $item['discount'];
                
                $subtotal += ($item['quantity'] * $item['unit_price']);
                $totalTax += $item['tax'];
                $totalDiscount += $item['discount'];

                $invoice->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax' => $item['tax'],
                    'discount' => $item['discount'],
                    'line_total' => $lineTotal
                ]);
            }

            $grandTotal = $subtotal + $totalTax - $totalDiscount;
            $invoice->update([
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'amount_due' => $grandTotal
            ]);
        });

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['lead', 'items.product', 'payments']);
        return view('invoices.show', compact('invoice'));
    }
}
