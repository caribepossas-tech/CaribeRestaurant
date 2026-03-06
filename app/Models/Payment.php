<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Payment extends Model
{
    use HasFactory;
    use HasBranch;

    const RECEIPT_FOLDER = 'receipts';

    protected $guarded = ['id'];

    public function receiptUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            return $this->receipt ? asset_url_local_s3(self::RECEIPT_FOLDER . '/' . $this->receipt) : null;
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

}
