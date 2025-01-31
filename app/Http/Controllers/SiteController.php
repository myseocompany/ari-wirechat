<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Point;
use Mail;
use App\User;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
  
        return view('site.index');
    }

    public function config()
    {
        //
  
        return view('site.config');
    } 
    public function satisfaction()
    {
        //
  
        return view('site.satisfaction');
    } 
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function report()
    {
        //
  
        return view('site.report');
    }    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    
    public function create()
    {
        //
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function testMail(){
        $data = "Datos";
        $message = "Prueba";
        $user = User::find(7);


        $to_address = "nicolas@myseocompany.co";
$subject = "This goes in the subject line of the email!";
$message = "This is the body of the email.\n\n";
$message .= "More body: probably a variable.\n";
$headers = "From: noresponder@mqe.com.co\r\n";
mail("$to_address","$subject","$message","$headers");
echo "Mail Sent.";

        
        
        Mail::raw("Contenido mail", function ($message) use ($user){

            $message->subject('MQE');

            $message->to('nicolas@myseocompany.co');

        });
        
        
    }
}
