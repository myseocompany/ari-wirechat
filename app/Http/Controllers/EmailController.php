<?php



namespace App\Http\Controllers;
use DB;
use Mail;
use File;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Email;
use App\Customer;
use Carbon\Carbon;
use App\EmailQueue;
use App\Action;
use App\Project;

class EmailController extends Controller
{
    
    
    public function store($id){
        $email = Email::find($id);
        $model=null;

        switch ($id) {
            case '1':
                $model=$this->getCustomersByStatus(1);
                break;
             case '2':
                $model=$this->getCustomersByCountry('usa');
                break;   
            case '3':
            	$model=$this->getCustomersBySource(21);
                break;
            case '4':
                $model=$this->getCustomersByStatus(7);
                break;
            case '5':
                $model=$this->getCustomersBySource(22);
                break;
            case '6':
                $model=$this->getCustomersBySource(19);
                break; 
			case '13':
				$model=$this->getCustomersByCity("Bogotá");
				break;
			case '17':
				$model=$this->getCustomersByCity("Bogotá");
				break;
			case '14':
				$model=$this->getCustomersByCity("Myseolandia");
				break;
			case '16':
				$model=$this->getCustomersByStatus(8);
				break;
			case '19':
				$model=$this->getCustomersByCountryAndYear("Colombia","2019");
				break;
			case '20':
				$model=$this->getCustomersByCountryAndYearLatinAmerica("2019");
                break;
            case '21':
                $model=$this->getCustomersByCountryAndYearLatinAmerica("2019");
                break;    
            case '22':
                $model=$this->getCustomersByCountryAndYearSpanishSpeaking("2019");
                break;    
            case '27':
                $model=$this->getCustomers27();
                break;
                
            case '34':
                $model=$this->getCustomers();
                break;

            case '37':
                $model=$this->getCustomers37();
                break;
                    
            default:
                # code...
                break;
        }

        //dd($model);
        
        $count = 1;
        if(!is_null($email->sended_at))
			$sended_at=$email->sended_at;
		else
			$sended_at=0;
        foreach($model as $item){
            Email::addEmailQueue($email, $item, $count*10,$sended_at);
            $count++;
        }
    }


    public function getCustomers(){

        $model = DB::table('customers')
            ->select('customers.id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email','<>','')
            ->where('email','<>',' ')
            ->where('email','<>','null@null.es')
            ->where('email','<>','null@null.com')
            ->where('email','<>','NUll@nul.es')
            ->where('email','<>','notiene@gmail.com')
            ->where('email','<>','nodio@gmail.com')
            ->where('email','<>','noenvio@gmail.com')
            ->where('email','<>','No@valido.com')
            ->where('email','<>','No@valido.com')
            ->where('status_id','=',9)
            ->get();

            return $model;
    }

    // getCustomers37 prospectos actualizados de este año (2020) menos perdidos (9) y no contesta (18)

    // SELECT customers.id, customers.name, customers.email FROM `customers` WHERE 1

    public function getCustomers37()
    {
        $model = DB::table('customers')
            ->select('customers.id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email','<>','')
            ->where('email','<>',' ')
            ->where('email','<>','null@null.es')
            ->where('email','<>','null@null.com')
            ->where('email','<>','NUll@nul.es')
            ->where('email','<>','notiene@gmail.com')
            ->where('email','<>','nodio@gmail.com')
            ->where('email','<>','noenvio@gmail.com')
            ->where('email','<>','No@valido.com')
            ->where('email','<>','No@valido.com')
            ->where('status_id','=',18)
            ->where('status_id', '!=' , 9)
            ->where('updated_at','>', '2020-01-01 00:00:00')
            ->get();

            return $model;
    }

    public function getCustomersByStatus($id){

        // $model = DB::table('customers')
        //     ->select('customers.id', 'name', 'email', DB::raw('COUNT(actions.id)'))
        //     ->leftJoin('actions', 'customers.id','=','customer_id')
        //     ->whereNotNull('email')
        //     ->where('email','<>','')
        //     ->where('email','<>',' ')
        //     ->where('status_id','=',$id)
        //     ->groupBy('customers.id')
        //     ->groupBy('name')
        //     ->groupBy('email')
        //     ->having(DB::raw('COUNT(actions.id)'),"=",'0')
        //     ->get();

        $model = DB::table('customers')
            ->select('customers.id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email','<>','')
            ->where('email','<>',' ')
            ->where('email','<>','null@null.es')
            ->where('email','<>','null@null.com')
            ->where('email','<>','NUll@nul.es')
            ->where('email','<>','notiene@gmail.com')
            ->where('email','<>','nodio@gmail.com')
            ->where('email','<>','noenvio@gmail.com')
            ->where('email','<>','No@valido.com')
            ->where('email','<>','No@valido.com')
            ->where('status_id','=',$id)
            ->get();

            return $model;
    }

    public function getCustomers27(){
        $model = Customer::join('temp', 'temp.email', 'customers.emails')
            ->get();
        
        return $model;
    }

    public function getCustomersByCountry($country){
        $model = DB::table('customers')
            ->select('customers.id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email','<>','')
            ->where('email','<>',' ')
            ->where('email','<>','null@null.es')
            ->where('email','<>','null@null.com')
            ->where('email','<>','NUll@nul.es')
            ->where('email','<>','notiene@gmail.com')
            ->where('email','<>','nodio@gmail.com')
            ->where('email','<>','noenvio@gmail.com')
            ->where('email','<>','No@valido.com')
            ->where('email','<>','No@valido.com')
            ->whereRaw('LOWER(`country`) LIKE ? ',[trim(strtolower($country)).'%'])
            ->get();

            return $model;
    }
    
    public function getCustomersByCity($city){
        $model = DB::table('customers')
            ->select('customers.id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email','<>','')
            ->where('email','<>',' ')
            ->where('email','<>','null@null.es')
            ->where('email','<>','null@null.com')
            ->where('email','<>','NUll@nul.es')
            ->where('email','<>','notiene@gmail.com')
            ->where('email','<>','nodio@gmail.com')
            ->where('email','<>','noenvio@gmail.com')
            ->where('email','<>','No@valido.com')
            ->where('email','<>','No@valido.com')
            ->whereRaw('LOWER(`city`) LIKE ? ',[trim(strtolower($city)).'%'])
            ->get();
            return $model;
    }

    public function getCustomersBySource($src){
    	$model = DB::table('customers')
    	   ->select('customers.id', 'name', 'email')
    	   ->where('source_id','=',$src)
    	   ->get();

    	   return $model;
    }

	public function getCustomersByCountryAndYear($country,$year){
		$model = DB::table('customers')
            ->select('customers.id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email','<>','')
            ->where('email','<>',' ')
            ->where('email','<>','null@null.es')
            ->where('email','<>','null@null.com')
            ->where('email','<>','NUll@nul.es')
            ->where('email','<>','notiene@gmail.com')
            ->where('email','<>','nodio@gmail.com')
            ->where('email','<>','noenvio@gmail.com')
            ->where('email','<>','No@valido.com')
            ->where('email','<>','No@valido.com')
            ->whereRaw('LOWER(`country`) LIKE ? ',[trim(strtolower($country)).'%'])
            ->where('created_at','>', $year.'-01-01 00:00:00')
            ->get();
			//dd($model);
            return $model;
		
		}

		public function getCustomersByCountryAndYearLatinAmerica($year){
		  $model = DB::table('customers')
            ->select('customers.id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email','<>','')
            ->where('email','<>',' ')
            ->where('email','<>','null@null.es')
            ->where('email','<>','null@null.com')
            ->where('email','<>','NUll@nul.es')
            ->where('email','<>','notiene@gmail.com')
            ->where('email','<>','nodio@gmail.com')
            ->where('email','<>','noenvio@gmail.com')
            ->where('email','<>','No@valido.com')
            ->where('email','<>','No@valido.com')
            ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('Colombia')).'%'])
            ->where('created_at','>', $year.'-01-01 00:00:00')
            ->get();
			//dd($model);
            return $model;
		
        }
        
        public function getCustomersByCountryAndYearSpanishSpeaking($year){
            $model = DB::table('customers')
                ->select('customers.id', 'name', 'email')
                ->whereNotNull('email')
                ->where('email','<>','')
                ->where('email','<>',' ')
                ->where('email','<>','null@null.es')
                ->where('email','<>','null@null.com')
                ->where('email','<>','NUll@nul.es')
                ->where('email','<>','notiene@gmail.com')
                ->where('email','<>','nodio@gmail.com')
                ->where('email','<>','noenvio@gmail.com')
                ->where('email','<>','No@valido.com')
                ->where('email','<>','No@valido.com')
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('ESTADOS UNIDOS')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('CANADA')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('SUIZA')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('FRANCIA')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('AUSTRALIA')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('INGLATERRA')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('AUSTRIA')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('ALEMANIA')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('NUEVA ZELANDA')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('TRINIDAD Y TOBAGO')).'%'])
                ->whereRaw('LOWER(`country`) NOT LIKE ? ',[trim(strtolower('ITALIA')).'%'])
                ->where('created_at','>', $year.'-01-01 00:00:00')
                ->get();
                //dd($model);
                return $model;
            
            }
        

    public function getDelay($seconds){



        $current_time = Carbon::now(-5);

        return $current_time->addSeconds($seconds);

    }

    public function send(){

        $model = EmailQueue::
            where('available_at','<',Carbon::now())
            ->where('email','<>', '')
            ->where('email','<>', ' ')
            ->where('status_id','=', 2)
            ->whereNotNull('email')
            ->whereNull('sended_at')
            ->get();
        
        $max = 200;
        $count = 0;

        foreach($model as $item){
			//dd($item->email);
            if($count<$max){
                if (filter_var($item->email, FILTER_VALIDATE_EMAIL)) {

                    $error = $this->sendUserEmail($item->user_id, $item->subject, $item->view, $item->email_id); 
                    echo "<div>".$item->email."</div>";          
                    $this->destroyEmailQueue($item->id);
                }else{
                    $this->destroyEmailQueue($item->id);
                }
            }
            $count++;
        }
    }

    public function destroyEmailQueue($id){
        $model = EmailQueue::find($id);
        $model->sended_at = Carbon::now();
        $model->status_id = 3; // Enviado
        $model->save();

    }

    public function sendUserEmail($cid, $subject, $view, $eid){
        //dd($cui);
        $error = 0;
        $customer = Customer::find($cid);
        
        if($customer){

            $email = Email::find($eid);

            $emailcontent = array (
            'subject' => $subject,
            'emailmessage' => 'Este es el contenido',
            'customer_id' => $cid,
            'email_id' => $eid,
            'name' => $customer->name,
            'customer_mail' => $customer->mail,
            
             );

            Mail::send($view, $emailcontent, function ($message) use ($customer, $subject){
                    $message->subject($subject);
                    //$message->bcc("nicolas@myseocompany.co");
                    $message->to($customer->email);
            });
            
            
            if(count(Mail::failures())>0){
                $error = 1;
                Action::saveAction($cid, $eid, 5);
            }else{
                Action::saveAction($cid, $eid, 2);
            }
         
        }
        return $error;  
    }

    
    
}
