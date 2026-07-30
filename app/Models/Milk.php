<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milk extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'dealer_name', 'product_name'];
}
