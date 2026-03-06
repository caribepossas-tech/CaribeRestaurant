<?php

namespace App\Models;

use App\Traits\HasRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSPaymentMethod extends Model
{
    use HasFactory, HasRestaurant;

    protected $table = 'pos_payment_methods';

    protected $fillable = ['restaurant_id', 'type', 'name', 'bank_name', 'bank_account_details', 'status', 'show_in_shop'];
}
