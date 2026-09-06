<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Invoice extends Model {
    protected $fillable = ['invoice_number', 'lead_id', 'date', 'due_date', 'status', 'notes', 'subtotal', 'tax', 'discount', 'grand_total', 'amount_paid', 'amount_due'];
    protected $casts = [
        'date' => 'date',
        'due_date' => 'date'
    ];
    public function lead() {
        return $this->belongsTo(Lead::class);
    }
    public function items() {
        return $this->hasMany(InvoiceItem::class);
    }
    public function payments() {
        return $this->hasMany(Payment::class);
    }
}
