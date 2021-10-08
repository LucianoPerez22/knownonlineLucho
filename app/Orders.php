<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'orderId',
        'firstName',
        'lastName',
        'email',
        'orderIdp',
        'value',
        'processed'
    ];
}
