<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with('lead')->latest()->paginate(15);
        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $leads = Lead::all();
        $products = Product::where('status', 'active')->get();
        // Generate a new quote number
        $lastQuote = Quotation::latest('id')->first();
        $nextNumber = 'QT-' . str_pad(($lastQuote ? $lastQuote->id + 1 : 1), 5, '0', STR_PAD_LEFT);
        
        return view('quotations.create', compact('leads', 'products', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_number' => 'required|unique:quotations,quote_number',
            'lead_id' => 'required|exists:leads,id',
            'date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
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

            $quotation = Quotation::create([
                'quote_number' => $validated['quote_number'],
                'lead_id' => $validated['lead_id'],
                'date' => $validated['date'],
                'expiry_date' => $validated['expiry_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'subtotal' => 0, // updated below
                'tax' => 0,
                'discount' => 0,
                'grand_total' => 0,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $lineTotal = ($item['quantity'] * $item['unit_price']) + $item['tax'] - $item['discount'];
                
                $subtotal += ($item['quantity'] * $item['unit_price']);
                $totalTax += $item['tax'];
                $totalDiscount += $item['discount'];

                $quotation->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax' => $item['tax'],
                    'discount' => $item['discount'],
                    'line_total' => $lineTotal
                ]);
            }

            $quotation->update([
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $totalDiscount,
                'grand_total' => $subtotal + $totalTax - $totalDiscount
            ]);
        });

        return redirect()->route('quotations.index')->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['lead', 'items.product']);
        return view('quotations.show', compact('quotation'));
    }
}
