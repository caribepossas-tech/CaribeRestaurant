<?php

namespace App\Models;

use App\Traits\HasRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSPaymentMethod extends Model
{
    use HasFactory, HasRestaurant;

    protected $table = 'pos_payment_methods';

    protected $fillable = ['restaurant_id', 'name', 'status'];
}
