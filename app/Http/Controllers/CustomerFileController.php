<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerFile;
use File;

class CustomerFileController extends Controller
{
    public function delete($id){
    	$model = CustomerFile::find($id);

    	//$path = public_path('/storage/files/'.$model->url);
    	$path = 'public/files/'.$model->customer_id."/".$model->url;
        	
    	
    	$error = File::delete($path);
    

    	$model->delete();
    	return back();
    }

    public function store(Request $request){
    	$path = "";

        if($request->hasFile('file')){
        	$file     = $request->file('file');
        	$path = $file->getClientOriginalName();

        	$destinationPath = 'public/files/'.$request->customer_id;
        	$file->move($destinationPath,$path);
        	/*
        	$file->store('public/files');
        	
        	$destination = base_path() . '/public/files';
			$file->move('public/files', $path);
			\Storage::disk('local')->put($path ,$file); 
			*/

        //	dd($file);
        	/*
        	
        	$destination = base_path() . '/public/files';
			$file->move('public/files', $path);
            */
            
    	}    
        // ensure every image has a different name
        //$path = $request->file('file')->hashName();
        

        
		
		

       // 
       // dd($path);
        $model = new CustomerFile;

        $model->customer_id = $request->customer_id;
        $model->url = $path;
        $model->save();

        return back();
        
    }
}
