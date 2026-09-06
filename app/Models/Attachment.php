<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attachment extends Model {
    protected $fillable = ['user_id', 'attachable_type', 'attachable_id', 'file_name', 'file_type', 'file_size', 'file_path'];
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function attachable() {
        return $this->morphTo();
    }
}
