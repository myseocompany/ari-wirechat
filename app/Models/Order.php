<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    protected $fillable = [
        'order_id', 'customer_id', 'product_id', 'invoice_id', 'quantity', 'updated_user_id',
        'price', 'shippingCharges', 'shipperCode', 'IVA', 'IVAReturn',
        'status_id', 'user_id', 'referal_user_id',
        'authorizationResult', 'authorizationCode', 'errorCode',
        'errorMessage', 'phone', 'phone2', 'contact_phone2',
        'billing_name', 'billing_document', 'billing_email', 'billing_address',
        'billing_city', 'billing_country',
        'added_at', 'notes', 'delivery_date',
        'delivery_name', 'delivery_email', 'delivery_address',
        'delivery_phone', 'delivery_to', 'delivery_from', 'delivery_message',
        'payment_form', 'payment_id', 'session_id', 'created_at', 'updated_at', 'user_ip', 'user_agent',
        'request_url', 'request_data', 'unique_machine',
    ];

    public static function boot(): void
    {

        parent::boot();

        static::updating(function ($order) {
            $orderHistoryData = $order->getOriginal();
            $orderHistoryData['order_id'] = $orderHistoryData['id'];
            unset($orderHistoryData['id']);

            OrderHistory::create($orderHistoryData);
        });

        static::created(function (Order $order): void {
            self::recordActivity($order, 'order.created', [
                'customer_id' => $order->customer_id,
                'status_id' => $order->status_id,
            ]);
        });

        static::updated(function (Order $order): void {
            $changes = array_diff_key($order->getChanges(), ['updated_at' => true]);
            if ($changes === []) {
                return;
            }

            self::recordActivity($order, 'order.updated', [
                'customer_id' => $order->customer_id,
                'changes' => array_keys($changes),
            ]);
        });

    }

    private static function recordActivity(Order $order, string $event, array $meta = []): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $event,
            'subject_type' => self::class,
            'subject_id' => $order->id,
            'meta' => $meta,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function updatedUser()
    {
        return $this->belongsTo('App\Models\User', 'updated_user_id');

    }

    public function Referal()
    {
        return $this->belongsTo('App\Models\User')->where('status_id', 3);

    }

    public function referal_user()
    {
        return $this->belongsTo('App\Models\User');

    }

    public function OrderStatus()
    {
        return $this->belongsTo('App\Models\OrderStatus');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\OrderStatus');
    }

    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'order_products');
    }

    public function firstProductName()
    {
        return optional($this->products->first())->name ?? '—';
    }

    public function productList()
    {
        return $this->hasMany('App\Models\OrderProduct');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\OrderTransaction');
    }

    public function getTotal()
    {
        $total = 0;

        if (isset($this->productList)) {
            foreach ($this->productList as $item) {

                $total += ((100 - $item->discount) / 100) * $item->price * $item->quantity;

            }
        }

        return $total;
    }

    public function countItems()
    {
        $total = 0;

        if (isset($this->productList)) {
            foreach ($this->productList as $item) {

                $total += 1;
            }
        }

        return $total;
    }

    public function histories()
    {
        return $this->hasMany(OrderHistory::class, 'order_id');
    }
}
