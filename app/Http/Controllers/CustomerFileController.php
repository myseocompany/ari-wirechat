<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerFile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as Fs;

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


    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => ['required','exists:customers,id'],
            'file'        => ['nullable','file'],      // compatibilidad
            'files.*'     => ['nullable','file'],      // múltiple
        ]);

        $customerId = $request->customer_id;
        $dest = public_path("public/files/{$customerId}");
        if (!Fs::isDirectory($dest)) Fs::makeDirectory($dest, 0755, true);

        // Normaliza a un array de UploadedFile
        $uploads = [];
        if ($request->hasFile('files'))   $uploads = $request->file('files');
        elseif ($request->hasFile('file')) $uploads = [$request->file('file')];

        foreach ($uploads as $upload) {
            $original = $upload->getClientOriginalName();
            $upload->move($dest, $original);

            CustomerFile::create([
                'customer_id'     => $customerId,
                'url'             => $original,
                'creator_user_id' => optional(Auth::user())->id,
            ]);
        }

        return back()->with('status', 'Archivo(s) subido(s) exitosamente.');
    }


	    public function reupload(Request $request, CustomerFile $file)
    {
        $request->validate(['file' => ['required','file']]);

        $dest = public_path("public/files/{$file->customer_id}");
        if (!Fs::isDirectory($dest)) Fs::makeDirectory($dest, 0755, true);

        // Guardar exactamente con el mismo nombre que está en BD
        $request->file('file')->move($dest, $file->url);

        // (Opcional) Marcar quién repuso y cuándo
        $file->creator_user_id = optional(Auth::user())->id ?? $file->creator_user_id;
        $file->touch();

        return back()->with('status', "Archivo repuesto: {$file->url}");
    }
}
