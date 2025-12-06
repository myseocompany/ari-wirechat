<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppAccount;
use Illuminate\Http\Request;

class WhatsAppAccountController extends Controller
{
    public function index()
    {
        $accounts = WhatsAppAccount::with('templates')->orderByDesc('is_default')->orderBy('created_at')->get();
        return view('whatsapp_accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('whatsapp_accounts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:190'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'phone_number_id' => ['required', 'string', 'max:100'],
            'business_account_id' => ['nullable', 'string', 'max:100'],
            'api_token' => ['required', 'string'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['api_url'] = "https://graph.facebook.com/v22.0/{$data['phone_number_id']}/messages";

        if ($data['is_default']) {
            WhatsAppAccount::where('is_default', true)->update(['is_default' => false]);
        }

        WhatsAppAccount::create($data);

        return redirect()->back()->with('status', 'Cuenta de WhatsApp guardada.');
    }

    public function update(Request $request, WhatsAppAccount $whatsappAccount)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:190'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'phone_number_id' => ['required', 'string', 'max:100'],
            'business_account_id' => ['nullable', 'string', 'max:100'],
            'api_token' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['api_url'] = "https://graph.facebook.com/v22.0/{$data['phone_number_id']}/messages";

        if (empty($data['api_token'])) {
            unset($data['api_token']); // keep existing token if not provided
        }

        if ($data['is_default']) {
            WhatsAppAccount::where('is_default', true)->update(['is_default' => false]);
        }

        $whatsappAccount->update($data);

        return redirect()->back()->with('status', 'Cuenta actualizada.');
    }

    public function makeDefault(WhatsAppAccount $whatsappAccount)
    {
        WhatsAppAccount::where('is_default', true)->update(['is_default' => false]);
        $whatsappAccount->update(['is_default' => true]);

        return redirect()->back()->with('status', 'Cuenta marcada como predeterminada.');
    }

    public function destroy(WhatsAppAccount $whatsappAccount)
    {
        $whatsappAccount->delete();
        return redirect()->back()->with('status', 'Cuenta eliminada.');
    }

    public function sendTestTemplate(Request $request)
    {
        $data = $request->validate([
            'test_phone' => ['required', 'string'],
        ]);

        $account = WhatsAppAccount::where('is_default', true)->first();
        if (!$account) {
            return redirect()->back()->withErrors(['No hay cuenta predeterminada configurada.']);
        }

        $phone = preg_replace('/\D/', '', $data['test_phone']);
        if (!$phone) {
            return redirect()->back()->withErrors(['Número de teléfono inválido.']);
        }

        try {
            app(\App\Services\WhatsAppGraphService::class)->sendTemplate($account, $phone, 'hello_world', 'en_US');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['Error enviando plantilla: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('status', 'Plantilla enviada a ' . $phone);
    }

    public function syncTemplates(WhatsAppAccount $whatsappAccount)
    {
        try {
            $templates = app(\App\Services\WhatsAppGraphService::class)->listTemplates($whatsappAccount);

            $whatsappAccount->templates()->delete();
            foreach ($templates as $tpl) {
                $whatsappAccount->templates()->create([
                    'name' => $tpl['name'] ?? '',
                    'language' => $tpl['language'] ?? null,
                    'category' => $tpl['category'] ?? null,
                    'status' => $tpl['status'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['No se pudieron sincronizar las plantillas: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('status', 'Plantillas sincronizadas.');
    }
}
