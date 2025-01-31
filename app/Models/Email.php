<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\SendWithData;
use Mail;
class Email extends Model{

	public static function addEmailQueue($email, $user, $count, $sended_at){

       

            $email_queue = new EmailQueue;
            $email_queue->user_id = $user->id;
            $email_queue->email_id = $email->id;

            $email_queue->subject = $user->name." ".$email->subject;
            $email_queue->view = $email->view;
            $email_queue->available_at = Email::getDelay($count,$sended_at);
            $email_queue->email = $user->email;
            $email_queue->status_id = 2;
            
            $email_queue->save();
            
    }

     public static function getDelay($seconds,$sended_at){
        if(is_null($sended_at))
			$current_time = Carbon::now(-5);
		else
			$current_time=Carbon::createFromFormat('Y-m-d H:i:s', $sended_at, 'Europe/London');

        return $current_time->addSeconds($seconds);

    }

    public static function isValidEmail($email){

    	return filter_var($email, FILTER_VALIDATE_EMAIL);

    }

    public function getDateInput($date){
        //return date('Y-m-d H:m',strtotime($date));
        return Carbon::parse($date)->format('Y-m-d\TH:i');
    }
    
    public function getYearInput($date){
        return date('Y-m-d',strtotime($date));
    }
    public function getHourInput($date){
        return date('H:m',strtotime($date));
    }
    public function getFileRouter(){
        return env('APP_MAIL_ROUTE')."email".$this->id;
    }

    public function getPathToView($model){
        $my_file = "email".$model->id.".blade.php";

        return env('APP_PATH_TO_MAIL').$my_file;

    }


    /*edit*/
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function email_queue(){
        return $this->hasmany('App\Models\EmailQueue');
    }




    public static function sendUserEmailWelcome($cid, $subject, $view, $eid){
        $model = Customer::find($cid);
        $emails = Array();
        if($model){

            $email = Email::find($eid);
            
            $name = "";
            $email_customer = "";
            $phone = "";
            if(isset($model->name) && $model->name != ""){
                $name = $model->name;
            }
            if(isset($model->email) && $model->email != ""){
                $email_customer = $model->email;
            }
            if(isset($model->phone) && $model->phone != ""){
                $phone = $model->phone;
            }

            $emailcontent = array (
                'subject' => $subject,
                'emailmessage' => 'Este es el contenido',
                'customer_id' => $cid,
                'email_id' => $eid,
                'name' => $name,
                'view' => $view,
                'to' => $email_customer,
                'from_email' => 'mqe@quirky.com.co',
                'from_name' => 'maquiempanadas',
                'model' => $model,
                'phone'=>$phone
             );
            $emails[0]='ventas@maquiempanadas.com';
            $emails[1]='ventas1@maquiempanadas.com';
            $emails[2]='gerencia@maquiempanadas.com';
            $emails[3]='nicolas@myseocompany.co';
            
            $failures = 0;

            try{
                /*
                Mail::to($emails[0])->send(new SendWithData($emailcontent));
                Mail::to($emails[1])->send(new SendWithData($emailcontent));
                Mail::to($emails[2])->send(new SendWithData($emailcontent));
                Mail::to($emails[3])->send(new SendWithData($emailcontent));
                dd($emails);
                */   
            }catch (Exception $e) {
                if (count(Mail::failures()) > 0) {
                    $failures++;
                }
            }
         
        }  
    }

    public function audience(){
        return $this->belongsTo('App\Models\Audience');
    }

}
