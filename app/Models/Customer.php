<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Namu\WireChat\Traits\Chatable;

class Customer extends Authenticatable
{
    use Chatable;

    // Custom logic for allowing chat creation
    public function canCreateChats(): bool
    {
        return true;
    }

    /**
     * Accessor Returns the URL for the user's cover image (used as an avatar).
     * Customize this based on your avatar field.
     */
    public function getCoverUrlAttribute(): ?string
    {
        // $image = null;
        // if($this->image_url)
        //     $image = $this->image_url;
        return $this->image_url ?? null;  // Adjust 'avatar_url' to your field
    }

    public function searchChatables(string $query): Collection
    {

        /* users = User::where('name', 'LIKE', "%{$query}%")
        ->limit(20)
        ->get(); */

        $customers = Customer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get();

        // Merge them into one collection
        // return $users->merge($customers);
        return $customers;

    }

    protected $fillable = [
        'name',
        'document',
        'position',
        'area_code',
        'phone',
        'phone2',
        'contact_phone2',
        'email',
        'address',
        'city',
        'country',
        'department',
        'business',
        'business_document',
        'business_phone',
        'business_area_code',
        'business_email',
        'business_address',
        'business_city',
        'image_url',

    ];

    public function references()
    {
        return $this->hasMany('App\Models\Reference');
    }

    public function actions()
    {
        return $this->hasMany('App\Models\Action')->orderBy('created_at', 'desc');
    }

    public function nextPendingAction()
    {
        return $this->hasOne(Action::class)
            ->whereNull('delivery_date')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderByDesc('created_at');
    }

    public function lastRelevantAction()
    {
        return $this->hasOne(Action::class)
            ->where(function ($query) {
                $query->whereNull('due_date')->orWhereNotNull('delivery_date');
            })
            ->latest('created_at');
    }

    public function histories()
    {
        return $this->hasMany('App\Models\CustomerHistory');
    }

    public function files()
    {
        return $this->hasMany('App\Models\CustomerFile');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'customer_tag')->withTimestamps();
    }

    // por compatibilidad con tu Blade actual
    public function customer_files()
    {
        return $this->files();
    }

    public function status()
    {
        return $this->belongsTo('App\Models\CustomerStatus');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function updated_user()
    {
        return $this->belongsTo('App\Models\User', 'updated_user_id', 'id');
    }

    public function source()
    {
        return $this->belongsTo('App\Models\CustomerSource', 'source_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    // function employee_files(){
    // 	return $this->hasMany('App\Models\EmployeeFile');
    // }

    public function searchableAs()
    {
        return 'employee_id';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Customize array...

        return $array;
    }

    public function countActions()
    {
        $count = Action::where('customer_id', '=', $this->id)->count();

        return $count;
    }

    public function countInActions()
    {
        $count = Action::join('action_types', 'action_types.id', 'type_id')
            ->where('outbound', '=', 0)
            ->where('customer_id', '=', $this->id)

            ->count();

        return $count;
    }

    public function countOutActions()
    {
        $count = Action::leftJoin('action_types', 'action_types.id', 'type_id')
            ->where('customer_id', '=', $this->id)
            ->where('outbound', '=', 1)
            ->whereNotNull('creator_user_id')
            ->count();

        return $count;
    }

    public function getId()
    {

        return $this->id;
    }

    public function createdDays()
    {
        $created = new Carbon\Carbon($this->created_at);
        $now = Carbon\Carbon::now();
        $difference = ($created->diff($now)->days < 1)
            ? 'hoy'
            : $created->diffInDays($now).' dias';

        return $difference;
    }

    public function phoneAsCode($phone)
    {

        if (strlen($phone) > 10) {
            return true;
        } else {
            return false;
        }
    }

    public function getPhoneWith57($phone)
    {
        if (strlen($phone) > 10) {
            return $phone;
        } elseif (strlen($phone) == 10) {
            return '57'.$phone;
        } else {
            return '';
        }
    }

    public function getPhoneStr()
    {
        $phone = '';
        if (isset($this->phone)) {
            $phone = $this->phone;
        } elseif (isset($this->phone2)) {
            $phone = $this->phone2;
        }

        return $phone;
    }

    public function cleanPhone($phone)
    {
        $newPhone = $phone;
        $str = substr($phone, 0, 3);
        if (substr($phone, 0, 3) == 'p:+') {
            $newPhone = substr($phone, 3, strlen($phone));
        }
        if (substr($phone, 0, 1) == '+') {
            $newPhone = substr($phone, 1, strlen($phone));
        }

        $newPhone = str_replace(' ', '', $newPhone);

        return $newPhone;
    }

    public function hasAValidPhone()
    {

        $phone = $this->cleanPhone($this->getPhoneStr());
        if ($this->phoneAsCode($phone)) {
            /*
            $number = substr($phone, -10);
            $ind = str_replace($number, "", $phone);
            if ($ind =='+57' || $ind=="57" || $ind == "54")
                return true;
            else
                return false;
            */
            // echo $phone."*";
            return true;

        } else {
            // echo $phone."_";
            if ($phone == '' || strlen($phone) < 10) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function getPhone()
    {
        $phone = '';
        $phone = $this->getPhoneWith57($this->cleanPhone($this->getPhoneStr()));

        return $phone;
    }

    public function getScoringToNumber(): int
    {
        $scores = ['d', 'c', 'b', 'a']; // orden de menor a mayor

        if (! empty($this->scoring_profile)) {
            $index = array_search(strtolower($this->scoring_profile), $scores);

            return $index !== false ? $index + 1 : 0;
        }

        return 0;
    }

    public function getPhoneUS()
    {
        $phone = '';
        $phone = $this->getPhoneWith1($this->cleanPhoneUS($this->getPhoneStrUS()));

        return $phone;
    }

    public function getPhoneWith1($phone)
    {
        if (strlen($phone) > 10) {
            return $phone;
        } elseif (strlen($phone) == 10) {
            return '1'.$phone;
        } else {
            return '';
        }
    }

    public function cleanPhoneUS($phone)
    {
        $newPhone = $phone;
        $str = substr($phone, 0, 3);
        if (substr($phone, 0, 3) == 'p:+') {
            $newPhone = substr($phone, 3, strlen($phone));
        }
        if (substr($phone, 0, 1) == '+') {
            $newPhone = substr($phone, 1, strlen($phone));
        }

        $newPhone = str_replace(' ', '', $newPhone);

        return $newPhone;
    }

    public function getPhoneStrUS()
    {
        $phone = '';
        if (isset($this->phone)) {
            $phone = $this->phone;
        } elseif (isset($this->phone2)) {
            $phone = $this->phone2;
        }

        return $phone;

    }

    public function getLastUserAction()
    {
        $model = Action::where('customer_id', $this->id)->orderBy('created_at', 'desc')->first();

        return $model;
    }

    public function getName()
    {
        if (! empty($this->name)) {
            return $this->name;
        } elseif (! empty($this->business)) {
            return $this->business;
        } else {
            return 'Sin nombre';
        }
    }

    public function getInitials()
    {
        $str = trim($this->getName());

        if (empty($str)) {
            return '??';
        }

        $words = preg_split('/\s+/u', $str); // separa por espacios, soporta unicode
        $initials = '';

        if (isset($words[0]) && mb_strlen($words[0], 'UTF-8') > 0) {
            $initials .= mb_substr($words[0], 0, 1, 'UTF-8');
        }

        if (isset($words[1]) && mb_strlen($words[1], 'UTF-8') > 0) {
            $initials .= mb_substr($words[1], 0, 1, 'UTF-8');
        }

        return mb_strtoupper($initials ?: '??', 'UTF-8');
    }

    public function getStatusColor()
    {
        if ($this->status && $this->status->color) {
            return $this->status->color;
        }

        return '#000000'; // Negro por defecto si no tiene estado
    }

    public function hasFullAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->canViewAllCustomers()) {
            return true;
        }

        $owner = $this->user;
        $ownerIsActive = $owner && (int) ($owner->status_id ?? 1) === 1; // si no hay status, asumimos activo para no filtrar de más

        if (! $ownerIsActive) {
            return true;
        }

        return (int) ($this->user_id ?? 0) === (int) $user->id;
    }

    public function getVisibleName(?User $user): string
    {
        return $this->getName();
    }

    public function getVisibleEmail(?User $user): ?string
    {
        // Email se considera campo básico, siempre visible.
        return $this->email ?: null;
    }

    public function getBestEmail(): ?string
    {
        $candidates = [
            $this->email,
            $this->contact_email,
            $this->business_email,
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate)) {
                continue;
            }

            $candidate = trim($candidate);

            if ($candidate === '' || ! filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            return $candidate;
        }

        return null;
    }

    public function getVisiblePhone(?User $user): ?string
    {
        if (! $this->hasFullAccess($user)) {
            return null;
        }

        $candidate = $this->getBestPhoneCandidate();

        return $candidate ? $this->getInternationalPhone($candidate) : null;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getInternationalPhone($phone = null, $defaultCountryCode = '57')
    {
        $rawPhone = $phone ?? $this->getPhoneStr();
        $cleaned = $this->cleanPhone($rawPhone);

        if (empty($cleaned)) {
            return null;
        }

        if (strpos($rawPhone, '+') === 0) {
            return $rawPhone;
        }

        if (strlen($cleaned) === 10) {
            return '+'.$defaultCountryCode.$cleaned;
        }

        if (strlen($cleaned) >= 11 && strlen($cleaned) <= 13) {
            return '+'.$cleaned;
        }

        return $cleaned;
    }

    public function getBestPhoneCandidate(): ?string
    {
        $candidates = [
            $this->phone,
            $this->phone2,
            $this->contact_phone2,
        ];

        $validPhones = collect($candidates)
            ->filter() // elimina nulos o vacíos
            ->map(function ($p) {
                return $this->cleanPhone($p);
            })
            ->filter(function ($p) {
                $len = strlen($p);

                return $len >= 10 && $len <= 13;
            })
            ->sortByDesc(function ($p) {
                return strlen($p); // preferir el más largo dentro del rango
            })
            ->values();

        return $validPhones->first();
    }

    public function getLastContactLabel(): ?string
    {
        $lastAction = $this->getLastUserAction();

        if (! $lastAction || empty($lastAction->created_at)) {
            return null;
        }

        try {
            $createdAt = \Carbon\Carbon::parse($lastAction->created_at);
        } catch (\Exception $e) {
            return null;
        }

        $diffDays = $createdAt->diffInDays(now());
        $color = 'secondary';
        $textColor = 'text-white';
        $icon = 'fa-clock-o'; // por defecto

        if ($diffDays < 2) {
            $color = 'success';
            $icon = 'fa-check-circle'; // contacto reciente
        } elseif ($diffDays < 90) {
            $color = 'warning';
            $textColor = 'text-dark';
            $icon = 'fa-hourglass'; // contacto intermedio (no existe exacto en FA4, este sirve)
        } else {
            $color = 'danger';
            $icon = 'fa-exclamation-circle'; // contacto viejo
        }

        $humanDate = method_exists($createdAt, 'diffForHumans') ? $createdAt->diffForHumans() : $createdAt->format('d-m-Y');

        return '<span class="badge bg-'.$color.' '.$textColor.'">
                <i class="fa '.$icon.'"></i> '.$humanDate.'
            </span>'
                .($lastAction->note
                    ? '<br><small>Nota: "'.\Illuminate\Support\Str::limit($lastAction->note, 40).'"</small>'
                    : '');
    }

    public static function normalizePhone(string $phone): string
    {
        // Quitar cualquier carácter que no sea dígito
        return preg_replace('/[^0-9]/', '', $phone);
    }

    public static function findByPhoneInternational(string $incomingPhone): ?Customer
    {
        $normalized = self::normalizePhone($incomingPhone);

        return self::whereRaw("REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', '') = ?", [$normalized])
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone2, '+', ''), ' ', ''), '-', '') = ?", [$normalized])
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(contact_phone2, '+', ''), ' ', ''), '-', '') = ?", [$normalized])
            ->first();
    }
}
