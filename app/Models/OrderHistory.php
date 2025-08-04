<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Order;


class OrderHistory extends Order
{
    protected $fillable = [
        'order_id', 'customer_id', 'product_id', 'invoice_id', 'quantity', 'updated_user_id',
        'price', 'shippingCharges', 'shipperCode', 'IVA', 'IVAReturn', 
        'status_id', 'user_id', 'referal_user_id', 
        'authorizationResult', 'authorizationCode', 'errorCode', 
        'errorMessage', 'phone', 'added_at', 'notes', 'delivery_date', 
        'delivery_name', 'delivery_email', 'delivery_address', 
        'delivery_phone', 'delivery_to', 'delivery_from', 'delivery_message', 
        'payment_form', 'payment_id', 'session_id', 'created_at', 'updated_at','user_ip', 'user_agent',
        'request_url', 'request_data', 'unique_machine'
    ];
    
}