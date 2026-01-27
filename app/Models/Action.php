<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Action extends Model
{
    use SoftDeletes;

    /** Campos que se pueden asignar en masa (create / update). */
    protected $fillable = [
        'note',
        'url',
        'due_date',
        'delivery_date',
        'object_id',
        'customer_id',
        'customer_owner_id',
        'customer_createad_at',
        'customer_updated_at',
        'type_id',
        'creator_user_id',
        'owner_user_id',
        'sale_date',
        'sale_amount',
        'reminder_type',
    ];

    /** Casts para fechas y numéricos. */
    protected $casts = [
        'due_date' => 'datetime',
        'delivery_date' => 'datetime',
        'customer_createad_at' => 'datetime',
        'customer_updated_at' => 'datetime',
        'sale_date' => 'date',
        'sale_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (Action $action): void {
            self::recordActivity($action, 'action.created', [
                'customer_id' => $action->customer_id,
                'type_id' => $action->type_id,
            ]);
        });

        static::updated(function (Action $action): void {
            $changes = array_diff_key($action->getChanges(), ['updated_at' => true]);
            if ($changes === []) {
                return;
            }

            self::recordActivity($action, 'action.updated', [
                'customer_id' => $action->customer_id,
                'changes' => array_keys($changes),
            ]);
        });

        static::deleted(function (Action $action): void {
            self::recordActivity($action, 'action.deleted', [
                'customer_id' => $action->customer_id,
            ]);
        });
    }

    private static function recordActivity(Action $action, string $event, array $meta = []): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $event,
            'subject_type' => self::class,
            'subject_id' => $action->id,
            'meta' => $meta,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function type()
    {
        return $this->belongsTo('App\Models\ActionType');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator_user_id');
    }

    public function email()
    {
        return $this->belongsTo('App\Models\Email', 'object_id', 'id');
    }

    public function getCustomerName()
    {
        $str = 'Sin cliente';
        if (isset($this->customer)) {
            $str = $this->customer->name;
        }

        return $str;
    }

    public function getTypeName()
    {
        $str = 'Sin accion';
        if (isset($this->type)) {
            $str = $this->type->name;
        }

        return $str;
    }

    public function getCreatorName()
    {
        $str = 'Automatico';
        if (isset($this->creator)) {
            $str = $this->creator->name;
        }

        return $str;
    }

    public function getEmailSubject()
    {
        $str = 'NA';
        if (isset($this->email)) {
            $str = $this->email->subject;
        }

        return $str;

    }

    public function getDescription()
    {
        $str = $this->note;
        if (isset($this->email)) {
            $str = $this->email->subject;
        }

        return $str;

    }

    public static function saveAction($uid, $eid, $aid)
    {
        $model = new Action;
        $model->customer_id = $uid;
        $model->object_id = $eid;
        $model->type_id = $aid;
        date_default_timezone_set('America/Bogota');
        $date = date('Y-m-d H:i:s');
        $model->delivery_date = $date;

        $model->save();

    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function isPending()
    {
        return ! is_null($this->due_date) && is_null($this->delivery_date);
    }

    public function wasNeverPending()
    {
        return is_null($this->due_date) && is_null($this->delivery_date);
    }

    public function shouldShow()
    {
        return $this->wasNeverPending() || $this->isPending();
    }
}
