<?php

//MQE

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
use App\EmailQueueStatus;
use App\Action;
use App\Project;


class EmmailController extends Controller{
    
    public function __construct(){$this->middleware('auth');}


    public function storeAudience($id){
        $email = Email::find($id);
        $model=$email->audience->customers;
        //dd($model);
        $count = 1;
        if(!is_null($email->sended_at))
            $sended_at=$email->sended_at;
        else
            $sended_at=0;

        if($model)
            foreach($model as $item){
                Email::addEmailQueue($email, $item, $count*10,$sended_at);

                echo "<ol>".$count." agregando a :".$item->email.":</ol>";


                $count++;
            }
        return redirect('/e-mails/'.$id.'/show');
    }


    // eid = email_id, aid = audience id
    public function store($eid, $aid){
        $email = Email::find($eid);
        $model=null;

        switch ($aid) {
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
            case '14':
                $model=$this->getCustomersByCity("Myseolandia");
            case '15':
                $model=$this->getCustomersByBoughtProducts("Reserva");    
                break;
            case '16':
                $model=$this->getRecentCustomers();    
                break;
            case '19':
                $model=$this->getCustomersByProjectId(1);    
                break;
            case '27':
                $model=$this->getCustomers27();    
                break;
            case '29':
                $model=$this->getCustomers27();    
                break;
            case '31':
                $model=$this->getCustomersByYear(2020);
                break;   
            case '32':
                $model=$this->getCustomersByYear(2020);      
                break;
            case '34':
                $model=$this->getCustomers(34);      
                break;
            case '36':
                $model=$this->getCustomersFriday();      
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
        $oks = 1;
        $errors = 1;
        if(!is_null($email->sended_at))
            $sended_at=$email->sended_at;
        else
            $sended_at=0;
        $emails = Array();
        echo "<ul>";
        if($model->count()){
	        foreach($model as $item){
                
                
	            if(!array_key_exists($item->email, $emails)){
	                $emails[$item->email] = $item->id; 
	                $email->addEmailQueue($email, $item, $count*10, $sended_at);
	                
                    echo "<ol>".$count." agregando a :".$item->email.":</ol>";
                    $oks++;

	            }else{
                    echo "<ol>".$count." No agregando :".$item->id.":</ol>";
                    $errors++;
                }
                //echo "<ol>".$count." agregando a :".$item->email.":</ol>";
                $count++;
	        }
	        //dd($emails);
	                
	        $count--;
	        echo "<br>".$count.":".$oks."/".$errors;
        }
        echo "</ul>";
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
            ->where('status_id','<>',18)
            ->where('status_id', '<>' , 9)
            ->where('updated_at','>', '2020-01-01 00:00:00')
            ->get();

            return $model;
    }

    public function getCustomers($eid){

        $email_queue = EmailQueue::where("email_id", '=', $eid)
            ->select('user_id')
            ->get();
        
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
            ->where('status_id','<>',12)
            ->whereNotIn('id', $email_queue)
            ->get();

        return $model;
    }
    public function getCustomers27(){
/*        $model = Customer::join('temp', 'temp.email', 'customers.email')
            ->get();
*/
       $model = Customer::where('email','nicolas@myseocompany.co')
            ->get();

            
        
        return $model;
    }

    public function index(){
        
        $model = Email::orderBy("active", "DESC")->get();
        return view('emmails.index', compact('model'));
    }

    public function destroy($id)
    {
        $model = Email::find($id);
        $model->delete();

        return redirect('/emmails');
    }

    public function create()
    {
        $model = Email::orderBy("id", "DESC")->get();
        return view('emmails.create', compact('model'));
    }

    public function edit($id)
    {
        $model = Email::find($id);
        return view('emmails.edit', compact('model'));
    }

    public function show($id)
    {
        $model = Email::find($id);

        $actions = Action::where('object_id', '=', $id)
            ->where('type_id', '=', 2)
            ->get();

        return view('emmails.show', compact('model', 'actions'));
    }

    public function storeEmail(Request $request)
    {
        //
        $model = new \App\Email();
        $count=0;
        
        $model->sended_at = Carbon::now();
        $model->subject= $request->subject;
        $model->scheduled_at = Carbon::now();
        $model->content=$request->content;
        $model->view="emails.";

        if($request->save != null){
        //id 1 para mensajes en estado borrador
            $status = 1;
        
        }else if($request->programming != null){
        //id 2 para mensajes en estado programado
            $status = 2;
            
        }else if($request->send != null){
        //id 3 para mensajes en estado enviado
            $status = 3;
            
        }
        $model->save();

        /*
        $model->view="emails.".$model->id;
        $model->save();
        */
        $this->saveView($model);
        
        $model->view = $model->getFileRouter($model);
        $model->save();   
        
        return redirect('/e-mails');
    }

    public function update(Request $request)
    {
        
        $model =  Email::find($request->id);
        $count=0;
        
        $model->sended_at = Carbon::now();
        $model->subject= $request->subject;
        if(isset($request->scheduled_at) && ($request->scheduled_at!=""))
            $model->scheduled_at = $request->scheduled_at;
        $model->content=$request->content;
        $model->view = $model->getFileRouter($model);


        
        if($request->save != null){
        //id 1 para mensajes en estado borrador
            $status = 1;
        
        }else if($request->programming != null){
        //id 2 para mensajes en estado programado
            $status = 2;
            
        }else if($request->send != null){
        //id 3 para mensajes en estado enviado
            $status = 3;
            
        }
      
        $this->saveView($model);


        $model->save();   
        return redirect('/e-mails/'.$model->id.'/show');
    }

     public function ChangeStatu(Request $request){
        
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        
        $model =  Email::find($request->email_id);

        if($model->active == 1){
            $model->active = 0;
            $model->type_id = 0;
        }else{
            $model->active = 1;
             $model->type_id = 1;
        }
       
        $model->save();

        return response()->json("exit!");
        
    }

    

    public function saveView($model){

        
        
        $html = $model->content;
        $unregister = '<p style="font-size:11px!important;margin:0;line-height:120%"> Si desea cancelar su suscripción por favor haga <a href="'.env('APP_DOMAIN').'customers/{!! $customer_mail !!}/unsubscribe" style="color:#333333;text-decoration:underline" rel="nofollow" target="_blank">clic aquí</a>. </p>';

        $pixel = '<img id="imgPixel" style="display:none;" src="'.env('APP_DOMAIN').'customers/{!! $customer_id !!}/actions/trackEmail/{!! $email_id !!}" width="0" height="0">';


        $html .= $unregister;
        $html .= $pixel;
        $url = $model->getPathToView($model);
        //dd($url);
        
        File::put($url, $html);

    }

    public function getBuyers(){
        $model = Customer::select(DB::raw('lower(customers.email) as email,  min(customers.id) as id'))
        ->rightJoin('buyers', 'customers.email', '=', 'buyers.email')
        ->groupBy('customers.email')
        ->havingRaw('min(customers.id)')
        ->get();
        
        //dd( $model[0] );

        return $model;
    }

    public function getCustomersByProjectId($id){
        $model = DB::table('customers')
            ->select('customers.id', 'customers.name', 'customers.email')
            ->whereNotNull('customers.email')
            ->where('customers.project_id','=',$id)
            //->where('customers.email','=','nicolas@myseocompany.co')
            ->distinct()
            ->get();

            return $model;
    }
    public function getCustomersFriday(){
        $model = DB::table('customers')
            ->select('customers.id', 'customers.name', 'customers.email')
            ->whereNotNull('customers.email')
            ->where('customers.email','<>','')
            ->where('customers.email','<>',' ')
            ->where('customers.email','<>','null@null.es')
            ->where('customers.email','<>','null@null.com')
            ->where('customers.email','<>','NUll@nul.es')
            ->where('customers.email','<>','notiene@gmail.com')
            ->where('customers.email','<>','nodio@gmail.com')
            ->where('customers.email','<>','noenvio@gmail.com')
            ->where('customers.email','<>','No@valido.com')
            ->where('customers.email','<>','No@valido.com')
            ->whereRaw("created_at > '2020-01-01 00:00:00'")
            ->whereIn('status_id', [4,2, 28, 7, 21, 6, 8, 27]) // Contactado, prospecto, seguimiento, proyecto, vip, negociación, ganado otros y ganado

            ->distinct()
            ->get();
            //dd($model);

            return $model;
    }
    public function getRecentCustomers(){
        $model = DB::table('customers')
            ->join('recent', 'customers.email', '=', 'recent.email')
            ->select('customers.id', 'customers.name', 'customers.email')
            ->whereNotNull('customers.email')
            ->where('customers.email','<>','')
            ->where('customers.email','<>',' ')
            ->where('customers.email','<>','null@null.es')
            ->where('customers.email','<>','null@null.com')
            ->where('customers.email','<>','NUll@nul.es')
            ->where('customers.email','<>','notiene@gmail.com')
            ->where('customers.email','<>','nodio@gmail.com')
            ->where('customers.email','<>','noenvio@gmail.com')
            ->where('customers.email','<>','No@valido.com')
            ->where('customers.email','<>','No@valido.com')

            ->distinct()
            ->get();
            //dd($model);

            return $model;
    }

    public function getCustomersByBoughtProducts($bought_products){
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
            ->whereRaw('LOWER(`bought_products`) LIKE %',[trim(strtolower($bought_products)).'%'])
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

    public function getCustomersByAmericaWithoutColombia(){
        $model = Customer::select('customers.id', 'name', 'email')
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
            ->where('country', 'not like', "%buenas%")
            ->where ('country', 'not like', "%CO%")
            ->where('country', 'not like', "%$%")
            ->where('country', 'is not', 'null')
            ->where('country', '<>', "")
            ->whereNotIn('status_id', [8,27])
            ->whereNotIn('country', ["Bogota","espa??a","medellin","cali","cucuta","bucaramanga","barranquilla","Pais","Valledupar","villavicencio","Norte de Santander", "Meta", "Huila", "NN", "Boyaca", "Valle", "no dejo", "Magdalena", "Monteria", "Indefinido", "Precio", "x", "Santander", "La guajira", "Santa Marta", "Valle del cauca", "Cauca", "5", "Empanadas", "Tolima", "caqueta", "Valor", "Yopal Casanare", "Cesar", "Somagoso", "Cilombia", "Popayán", "Pasto", ".", "Pasto Nariño", "La vega cundinamarcs", "pacho cund", "Arauca", "0", "España", "ES", "Australiano", "Francia", "Italia", "Suiza", "israel", "Portugal", "France", "Antioquia", "Cartagena", "Santa Elena", "Cundinamarca", "Suecia", "holanda", "Austria", "Germany", "Inglaterra", "Russia", "belgica", "Bolivar", "Switzerland", "manizales", "Poland", "Neiva", "Moldova", "Ukraine", "Uk", "Italy", "Belgium", "Berlin", "Malta", "Silvia luz cardona villegas", "Soledad", "United Kingdom", "Philippines", "Somalia", "No llamar sólo Whatsapp", "no tiene", "TV", "k","Manzana 8 casa 8", "Sincelejo", "m", "Bhg", "Mauromuñoz", "Bucaramanga santander", "Bogot??", "Barcelona", "57", "Barrio los Andes. 58#16", "Espa??a Barcelona", "Chaparral", "Autralia", "Singapore", "Suiza y Argentina", "Australia", "Reino Unido", "NA", "IT", "FR", "Cr 63 #22 _ 31", "5555", "Gg", "Sogamoso", "Sweden", "Yes","Deutschland","Europa","3002392907","Taiwan", "Polonia", "Antarctica", "Eu", "palmira valle", "Avenida primero de mayo", "ggg", "Netherlands Antilles", "Kolten Page", "Fbs", "PP", "NT", "BW", "Bb", "dft", "Ethiopia", "Informacion sobre trading", "Bucarama ga", "Ipiales", "clombia", "cv", "0.", "Nederland", "A", "3168719906", "Suram??rica", "D", "jj", "Precio de la maquina x favor", "Tee", "35", "ALGERIE", "Hola me gustaría un vídeo", "Duitama", "SUPER EMPANADAS YOPAL", "Cllombiq", "mSoNAcWOQHhWhlJyL", "Ciudadela Bonanza Mz 14 L 33", "Precio me interesa", "Paipa Boyac??", "Nepal", "INGLATERRA", "Fusagasugá", "Barrio arkabal mz L casa 31", "Calle 58 NRO 62a202", "H", "Información", "Ibagué", "3118811689", "DV", "Gilbert", "555", "Ireland", "Buenaventura", "barranca bermeja", "AU", "ss", "Inqlaterra", "Luxembourg", "Bosnia and Herzegovina", "Carrera 25 # 19-16", "How much its that?", "Que  valor tiene la maquina y medios de pago", "Me guatriaa resivir información", "Calle ppal el Carito", "555656", "Me guatriaa resivir información", "564", "Slovenia", "Itagui", "g", "vv", "Krystal Ballard", "Fuentes", "Netherlands", "Paris", "Cra13#24-16", "Floridablanca", "pereira", "Croatia", "Recreo", "C0LOMBIA", "san gil", "Riohacha", "Rusia", "New Zealand", "KH", "Necesito el catalogo de precios", "Evhdidjdvvdnsiaoqvsvsjdb", "22222", "b", "--------",  "Venden inyectadores de sabores?", "Carter a22#36_29 local 202", "Carrera 20 #4_115","Muñoz  Loor Jessenia",  "Buga valle", "BUCARAMANGA///bogata", "Casanare",  "Cra 71 no 94_23", "Bogots", "dir falsa", "Sabaneta", "Carrera 45B # 90-210", "Precioo", "Envigado", "Av. Bolívar",  "espa??a3", "Barcelona  pa??a", "palmira", "Precio  por favor", "madrid", "v", "PH", "Aguadas", "Rumania","Uhhjj", "Que precio tiene y donde la venden...", "Quiero saber el precio de la maquina mil gracias", "Máquina de empanadas", "Me interesa", "chiquinquira", "Calombia", "No", "Greenland", "Denmark", "Ciénaga Mag","Por favor me regalas precio de la maquina gracias",  "Carrera 1 #17-195 ridadero", "londres inglaterra", "n", "111", "Olaya Herrera", "leticia - amazonas", "12101215", "Uzbekistan","313 882 65 92", "China", "bogota/// bucaramanga"])

            ->get();
    }

    public function getCustomersByYear($year){
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
            ->whereRaw("created_at >'".$year."-01-01 00:00:00'")
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

    public function getDelay($seconds){



        $current_time = Carbon::now(-5);

        return $current_time->addSeconds($seconds);

    }

    public function send(){

        $model = EmailQueue::
            //where('available_at','<',Carbon::now())
            where('email','<>', '')
            ->where('email','<>', ' ')
            ->where('status_id','=', 2)
            ->whereNotNull('email')
            ->whereNull('sended_at')
            ->get();
        
            //dd($model);
        $max = 1000;
        $count = 0;
        foreach($model as $item){
			//dd($item->email);
            if($count<$max){
                if (filter_var($item->email, FILTER_VALIDATE_EMAIL)) {
                    $this->sendUserEmail($item->user_id, $item->subject, $item->view, $item->email_id);
                    echo "<div>".$item->email."</div>";
                    $this->destroyEmailQueue($item->id);
                }else{
                    echo "<div>".$item->email."</div>";
                    $item->status_id = 5;
                    $item->save();
                }
            }
            $count++;
        }
        echo $count--;
    }

    public function destroyEmailQueue($id){
        $model = EmailQueue::find($id);
        $model->sended_at = Carbon::now();
        $model->status_id = 3; // Enviado
        $model->save();

    }

    public function sendUserEmail($cid, $subject, $view, $eid){
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
                Action::saveAction($cid, $eid, 5);
            }else{
                Action::saveAction($cid, $eid, 2);
            }
         
        }  
    }    

    
    
}
