<?php

namespace App\Http\Controllers;

use App\Models\CustomerFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerFileController extends Controller
{
    // Helpers para armar las "keys" en Spaces (carpetas = prefijos)
    protected function fileKey(int $customerId, string $filename): string
    {
        return "files/{$customerId}/{$filename}";
    }

    protected function trashKey(int $customerId, string $filename): string
    {
        return "files_deleted/{$customerId}/{$filename}";
    }

    public function destroy(Request $request, CustomerFile $file)
    {
        $disk = Storage::disk('spaces');

        $srcKey = $this->fileKey($file->customer_id, $file->url);
        $dstKey = $this->trashKey($file->customer_id, $file->url);

        // En S3/Spaces no hay que "crear carpetas": basta con mover usando prefijos
        if ($disk->exists($srcKey)) {
            // move copia+borra en el bucket
            $disk->move($srcKey, $dstKey);
        } else {
            \Log::warning("Archivo no encontrado para mover a papelera: {$srcKey}");
        }

        $file->delete();

        if ($request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Archivo movido a papelera.',
                'id' => (int) $file->id,
            ]);
        }

        return back()->with('success', 'Archivo movido a papelera.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'file' => ['nullable', 'file'],
            'files.*' => ['nullable', 'file'],
        ]);

        $disk = Storage::disk('spaces');
        $customerId = (int) $request->customer_id;

        // Normaliza a array de UploadedFile
        $uploads = [];
        if ($request->hasFile('files')) {
            $uploads = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $uploads = [$request->file('file')];
        }

        $createdFiles = [];
        foreach ($uploads as $upload) {
            $original = $upload->getClientOriginalName();
            $extension = $upload->getClientOriginalExtension();
            $storedName = (string) Str::uuid();
            if ($extension !== '') {
                $storedName .= '.'.$extension;
            }

            // Guarda en Spaces (visibilidad según tu caso)
            // Si quieres todo público:
            $key = $this->fileKey($customerId, $storedName);
            $disk->putFileAs("files/{$customerId}", $upload, $storedName, [
                'visibility' => 'public',                // o 'private'
                'ContentType' => $upload->getMimeType(), // útil para servir correctamente
            ]);

            $customerFile = CustomerFile::create([
                'customer_id' => $customerId,
                'url' => $storedName,
                'name' => $original,
                'creator_user_id' => optional(Auth::user())->id,
            ]);

            $customerFile->loadMissing('creator');
            $createdFiles[] = [
                'id' => (int) $customerFile->id,
                'url' => $customerFile->url,
                'name' => $customerFile->name,
                'created_at' => optional($customerFile->created_at)->toDateTimeString(),
                'creator_name' => $customerFile->creator?->name ?? 'Sin usuario',
                'status' => $customerFile->status,
                'open_url' => route('customer_files.open', $customerFile->id),
                'destroy_url' => route('customer_files.destroy', $customerFile),
            ];
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'files' => $createdFiles,
            ]);
        }

        return back()->with('status', 'Archivo(s) subido(s) exitosamente.');
    }

    public function reupload(Request $request, CustomerFile $file)
    {
        $request->validate(['file' => ['required', 'file']]);

        $disk = Storage::disk('spaces');
        $upload = $request->file('file');

        // Sobrescribe exactamente el mismo nombre/clave
        $key = $this->fileKey($file->customer_id, $file->url);
        $disk->putFileAs("files/{$file->customer_id}", $upload, $file->url, [
            'visibility' => 'public',                 // o 'private'
            'ContentType' => $upload->getMimeType(),
        ]);

        // (Opcional) marca quién lo repuso
        $file->creator_user_id = optional(Auth::user())->id ?? $file->creator_user_id;
        $file->touch();

        return back()->with('status', "Archivo repuesto: {$file->url}");
    }

    public function open(CustomerFile $file)
    {
        $key = "files/{$file->customer_id}/{$file->url}";

        // 15 min; 'inline' para ver en el navegador (usa 'attachment' para descargar)
        $url = Storage::disk('spaces')->temporaryUrl(
            $key,
            now()->addMinutes(15),
            ['ResponseContentDisposition' => 'inline; filename="'.addslashes($file->url).'"']
        );

        return redirect()->away($url);
    }
}
