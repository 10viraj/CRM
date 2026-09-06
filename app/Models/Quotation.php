<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Quotation extends Model {
    protected $fillable = ['quote_number', 'lead_id', 'date', 'expiry_date', 'status', 'notes', 'subtotal', 'tax', 'discount', 'grand_total'];
    protected $casts = [
        'date' => 'date',
        'expiry_date' => 'date'
    ];
    public function lead() {
        return $this->belongsTo(Lead::class);
    }
    public function items() {
        return $this->hasMany(QuotationItem::class);
    }
}
