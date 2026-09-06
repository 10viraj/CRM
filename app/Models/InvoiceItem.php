<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InvoiceItem extends Model {
    protected $fillable = ['invoice_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'tax', 'discount', 'line_total'];
    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
