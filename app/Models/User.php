<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Collection;    
use Carbon\Carbon;

use Namu\WireChat\Traits\Chatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    
    use Chatable;

    // Custom logic for allowing chat creation
    public function canCreateChats(): bool
    {
        return true;
    }


    public function searchChatables(string $query): Collection
    {
        $only_my_customers = session('only_my_message_sources');
        

        $customers = Customer::where('name', 'LIKE', "%{$query}%")
            
            ->where(function($query)use($only_my_customers){
                if($only_my_customers){
                    $query->where('user_id', auth()->id());
                }
            })
        
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get();

        return $customers;
    }

    // Obtener un usuario por su número de teléfono
    public static function findByPhone($phone) {
        return self::whereHas('messageSources', function ($query) use ($phone) {
            $query->whereJsonContains('settings->phone_number', $phone);
        })->first();
    }

    // Relación muchos a muchos con message_sources
    public function messageSources(): BelongsToMany
    {
        return $this->belongsToMany(MessageSource::class, 'user_message_sources')
                    ->withPivot('is_active', 'is_default')  // Campos extra de la tabla pivot
                    ->wherePivot('is_active', true);
    }

    // Relación para obtener el message_source predeterminado del usuario
    public function defaultMessageSource(): HasOne
    {
        $model = $this->hasOne(MessageSource::class, 'id', 'message_source_id')
                    ->whereHas('users', function ($query) {
                        $query->where('is_default', true);
                    });
        dd($model);
        return $model;
    }

    // Método para obtener el primer message_source disponible
    public function getFirstMessageSource()
    {
        return MessageSource::first();
    }

    // Método para obtener el message_source predeterminado del usuario
    public function getUserDefaultMessageSource()
    {
        return $this->messageSources()->wherePivot('is_default', true)->first() ?? $this->getFirstMessageSource();
    }

    // Método corregido para obtener el message_source predeterminado global
    public function getDefaultMessageSource()
    {
        return MessageSource::where('is_default', true)->first() ?? $this->getFirstMessageSource();
    }


    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function role(){
        return $this->belongsTo('App\Models\Role');
    }
    public function status(){
        return $this->belongsTo('App\Models\UserStatus');
    }
    public function customer() {
        return $this->belongsTo('App\Models\Customer');
    }

    public function getTotalStatus($status, $request){
        $model = Customer::where(
            function ($query) use ($status, $request) {
                
                if(isset($request->from_date)&& ($request->from_date!=null)){
                    $query = $query->whereBetween('updated_at', array($request->from_date, $request->to_date));
                    
                }else{
                    $query = $query->where('customers.id', "=", "-1");
                }
                if(isset($status)  && ($status!=null)){
                    $query = $query->where('status_id', $status);

                }
                $query = $query->where('user_id', $this->id);
            })
            ->get();
            
            
        return $model->count();
    }

 

    public function getTotalActions($action_type_id, $request){
        $model = Action::
            where(
            function ($query) use ($action_type_id, $request) {
                
                if(isset($request->from_date)&& ($request->from_date!=null)){
                    $query = $query->whereBetween('actions.created_at', array($request->from_date, $request->to_date));
                    
                }else{
                    $query = $query->where('actions.id', "=", "-1");
                }

                if(isset($action_type_id)  && ($action_type_id!=null)){
                    $query = $query->where('type_id', $action_type_id);

                }
                $query = $query->where('creator_user_id', $this->id);
            })
            ->get();
            
        $count = $model->count();
        return $count!=0?$count:"";
    }



    public function getActions($request, $action_type){
        $model = Action::leftJoin("customers", "customers.id", "actions.customer_id")
            ->where(function($query)use($request, $action_type ){

                // se filtran las acciones creadas en ese periodo de tiempo
                
                if($action_type == 27){ // venta
                    $query->whereBetween('sale_date', [$request->from_date, $request->to_date]);
                }else{
                    $query->whereBetween('actions.created_at', [$request->from_date, $request->to_date]);
                }
                
                // filtro los clientes de ese período
                $dates_array = $this->getDateArray($request);
                $date_at = $request->created_updated === "updated" ? 'customers.updated_at' : 'customers.created_at';

                if(isset($request->from_date) && $request->from_date != "") {
                    $query->whereBetween($date_at, $dates_array);
                }

                // filtro por usuario
                #
            
            })
            ->where("customers.user_id", $this->id)
            ->where('actions.type_id', $action_type)
            ->get();
        
            return $model->count();
            
    }

    public function getDateArray($request){
        $to_date = Carbon::today()->subDays(0); // ayer
        $from_date = Carbon::today()->subDays(7);


        if(isset($request->from_date) && ($request->from_date!=null)){
            $to_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date." 00:00:00");
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date." 00:00:00");
        }

        $date_array = 
            Array($from_date->format('Y-m-d'), $to_date->addHours(23)->addMinutes(59)->addSeconds(59)->format('Y-m-d H:i:s'));
        return $date_array;
    }
}
