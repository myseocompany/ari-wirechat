<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerFile;
use File;

class CustomerFileController extends Controller
{
	public function delete($id)
	{
		$model = CustomerFile::find($id);

		$originalPath = 'public/files/'.$model->customer_id.'/'.$model->url;
		$trashPath = 'public/files_deleted/'.$model->customer_id;

		// Asegura que la carpeta destino exista
		if (!File::exists($trashPath)) {
			File::makeDirectory($trashPath, 0755, true, true);
		}

		// Mover el archivo a la papelera si existe
		if (File::exists($originalPath)) {
			File::move($originalPath, $trashPath.'/'.$model->url);
		} else {
			\Log::warning("Archivo no encontrado para mover a papelera: ".$originalPath);
		}

		// Elimina el registro de base de datos
		$model->delete();

		return back()->with('success', 'Archivo movido a papelera.');
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
		$model->creator_user_id = auth()->id();
		
        $model->save();

        return back();
        
    }
}
