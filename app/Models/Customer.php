<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Action;
use Carbon;
use Illuminate\Database\Eloquent\Collection;
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
        return $this->image_url??null;  // Adjust 'avatar_url' to your field
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
        //return $users->merge($customers);
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
        'image_url'
        
        
    ];
    function references(){
        return $this->hasMany('App\Models\Reference');
    }

 

    function actions() {
        return $this->hasMany('App\Models\Action')->orderBy('created_at', 'desc');
    }

    function customer_files(){
        return $this->hasMany('App\Models\CustomerFile');
    }

    function histories(){
        return $this->hasMany('App\Models\CustomerHistory');
    }

    function files(){
        return $this->hasMany('App\Models\CustomerFile');
    }

    public function status(){
    	return $this->belongsTo('App\Models\CustomerStatus');
    }

    function user(){
        return $this->belongsTo('App\Models\User');
    }

    function updated_user(){
        return $this->belongsTo('App\Models\User', 'updated_user_id', 'id');
    }

      function source(){
        return $this->belongsTo('App\Models\CustomerSource', 'source_id' , 'id');
    }

    function product(){
        return $this->belongsTo('App\Models\Product');
    }

    // function employee_files(){
    // 	return $this->hasMany('App\Models\EmployeeFile');
    // }

    public function searchableAs(){
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

    public function countActions(){
        $count = Action::where('customer_id','=',$this->id)->count();

        return $count;
    }

    public function countInActions(){
        $count = Action::join('action_types', 'action_types.id', 'type_id')
            ->where('outbound', '=', 0)
            ->where('customer_id','=',$this->id)
            
            ->count();

        return $count;
    }

    public function countOutActions(){
        $count = Action::leftJoin('action_types', 'action_types.id', 'type_id')
            ->where('customer_id','=',$this->id)
            ->where('outbound', '=', 1)
            ->whereNotNull('creator_user_id')
            ->count();

        return $count;
    }

    public function getId(){
        
        return $this->id;
    }

    public function createdDays(){
        $created = new Carbon\Carbon($this->created_at);
        $now = Carbon\Carbon::now();
        $difference = ($created->diff($now)->days < 1)
            ? 'hoy'
            : $created->diffInDays($now) . ' dias';
        return $difference;
    }





    public function phoneAsCode($phone){
        
        if(strlen($phone)>10)
            return true;
        else
            return false;
    }
    
    public function getPhoneWith57($phone){
        if(strlen($phone)>10)
            return $phone;
        elseif( strlen($phone) == 10 )
            return "57".$phone;
        else
            return "";
    }
    
    public function getPhoneStr(){
        $phone = "";
        if(isset($this->phone))
            $phone = $this->phone;
        elseif(isset($this->phone2))
            $phone = $this->phone2;
        return $phone;
    }

    public function cleanPhone($phone){
        $newPhone = $phone;
        $str = substr($phone, 0, 3);
        if(substr($phone, 0, 3) == "p:+")
            $newPhone = substr($phone, 3, strlen($phone));
        if(substr($phone, 0, 1) == "+")
            $newPhone = substr($phone, 1, strlen($phone));

        $newPhone =str_replace(' ', '', $newPhone);
        return $newPhone;
    }

    public function hasAValidPhone(){
        
        $phone = $this->cleanPhone($this->getPhoneStr());
        if($this->phoneAsCode($phone)){
            /*
            $number = substr($phone, -10);
            $ind = str_replace($number, "", $phone);
            if ($ind =='+57' || $ind=="57" || $ind == "54")
                return true;
            else
                return false;
            */
                //echo $phone."*";
                return true;

        }else{
            //echo $phone."_";
            if( $phone=="" || strlen($phone) < 10)
                return false;
            else
                return true; 
        }     
    }

    public function getPhone(){
        $phone = "";
        $phone =  $this->getPhoneWith57($this->cleanPhone($this->getPhoneStr()) ) ;
        return $phone;
    }

    public function getScoringToNumber(): int
    {
        $scores = ['d', 'c', 'b', 'a']; // orden de menor a mayor
    
        if (!empty($this->scoring_profile)) {
            $index = array_search(strtolower($this->scoring_profile), $scores);
            return $index !== false ? $index + 1 : 0;
        }
    
        return 0;
    }
    



    public function getPhoneUS(){
        $phone = "";
        $phone =  $this->getPhoneWith1($this->cleanPhoneUS($this->getPhoneStrUS()) ) ;
        return $phone;
    }

    public function getPhoneWith1($phone){
        if(strlen($phone)>10)
            return $phone;
        elseif( strlen($phone) == 10 )
            return "1".$phone;
        else
            return "";
    }
    
    public function cleanPhoneUS($phone){
        $newPhone = $phone;
        $str = substr($phone, 0, 3);
        if(substr($phone, 0, 3) == "p:+")
            $newPhone = substr($phone, 3, strlen($phone));
        if(substr($phone, 0, 1) == "+")
            $newPhone = substr($phone, 1, strlen($phone));

        $newPhone =str_replace(' ', '', $newPhone);
        return $newPhone;
    }

    public function getPhoneStrUS(){
        $phone = "";
        if(isset($this->phone))
            $phone = $this->phone;
        elseif(isset($this->phone2))
            $phone = $this->phone2;
        return $phone;


    }

    public function getLastUserAction(){
        $model = Action::where("customer_id", $this->id)->orderBy('created_at', 'desc')->first();;

        return $model;
    }

    public function isBanned(){
        $model = Action::where('type_id', 31)
                        ->where('customer_id',"=", $this->id)->first();
        $is_banned = false;
        if($model)
            $is_banned = true;
        return $is_banned;
    }


    public function getName() {
        if (!empty($this->name)) {
            return $this->name;
        } elseif (!empty($this->business)) {
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

    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getInternationalPhone($defaultCountryCode = '57')
    {
        $rawPhone = $this->getPhoneStr();                  // usa el método existente
        $cleaned = $this->cleanPhone($rawPhone);           // limpia el número
        
        // Si ya empieza con el código de país, solo agregamos "+"
        if (strpos($cleaned, $defaultCountryCode) === 0) {
            return '+' . $cleaned;
        }

        // Si tiene 10 dígitos, asumimos que es un número nacional sin indicativo
        if (strlen($cleaned) === 10) {
            return '+' . $defaultCountryCode . $cleaned;
        }

        // Si tiene más de 10 pero sin "+" (posiblemente mal ingresado)
        if (strlen($cleaned) > 10 && $rawPhone[0] !== '+') {
            return '+' . $cleaned;
        }

        return $cleaned; // fallback (dejar como está si no entra en ningún caso)
    }

    public function getBestPhoneCandidate(): ?string
    {
        $candidates = [
            $this->phone,
            $this->phone2,
            $this->contact_phone2
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

}
