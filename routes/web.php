<?php
use App\Http\Controllers\RDTestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OptimizerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerStatusController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\CustomerFileController;
use App\Http\Controllers\Api\APIController;
use App\Http\Controllers\ReferencesController;
//use App\Http\Controllers\WompiController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ActionTypeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\EmmailController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderTransactionController;
use App\Http\Controllers\MetaDataController;
use App\Http\Controllers\AudienceController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WhatsAppAPIController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CustomerTagController;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Resend\Laravel\Facades\Resend;

use App\Http\Controllers\LandingController;



use App\Http\Controllers\Madrid2025Controller;


Route::get('/', function () {
    return view('home');
});


Route::middleware('auth')->get('/faq', [HomeController::class, 'indexFaq'])->name('faq');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')
    ->prefix('admin/tags')
    ->name('tags.')
    ->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::get('/create', [TagController::class, 'create'])->name('create');
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::get('/{tag}/edit', [TagController::class, 'edit'])->name('edit');
        Route::put('/{tag}', [TagController::class, 'update'])->name('update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
    });

require __DIR__.'/auth.php';

// Customer Routes
/*
Route::middleware('auth')->prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'leads'])->name('customers');
    Route::get('/create', [CustomerController::class, 'create']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::get('/{customer}/edit', [CustomerController::class, 'edit']);
    Route::post('/{customer}/update', [CustomerController::class, 'update']);
    Route::get('/{customer}/show', [CustomerController::class, 'show']);
    Route::get('/{customer}/show/{action}', [CustomerController::class, 'showAction']);
    Route::get('/{customer}/destroy', [CustomerController::class, 'destroy']);
    Route::post('/{customer}/action/store', [CustomerController::class, 'storeAction']);
    Route::post('/{customer}/action/sale', [CustomerController::class, 'saleAction']);
    Route::post('/{customer}/action/opportunity', [CustomerController::class, 'opportunityAction']);
    Route::post('/{customer}/action/poorly_rated', [CustomerController::class, 'poorlyRatedAction']);
    Route::post('/{customer}/action/pqr', [CustomerController::class, 'pqrAction']);
    Route::post('/{customer}/action/spare', [CustomerController::class, 'spareAction']);
    Route::post('/{customer}/action/mail', [CustomerController::class, 'storeMail']);
    Route::get('/{customer}/assignMe', [CustomerController::class, 'assignMe']);
    Route::post('/{customer}/audience', [CustomerController::class, 'storeAudience']);
    Route::get('/history/{customer}/show', [CustomerController::class, 'showHistory']);
    Route::get('/ajax/update_user', [CustomerController::class, 'updateAjax']);
    Route::get('/logistica', [CustomerController::class, 'newIndex']);
    Route::get('/{customer}/action/updateAjax', [CustomerController::class, 'updateAjaxStatus']);
    Route::get('/phase/{pid}', [CustomerController::class, 'customers']);
    Route::get('/{pid}', [CustomerController::class, 'dragleads']);
    Route::get('/{pid}', [CustomerController::class, 'dragleads']);
    Route::post('/start-chat', [CustomerController::class, 'startConversationFromCRM'])
        ->name('customers.start-chat');
});
*/

// Customer Routes
Route::middleware('auth')->prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
    
    Route::get('/create', [CustomerController::class, 'create']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::post('/{customer}/tags', [CustomerTagController::class, 'update'])->name('customers.tags.update');
    Route::post('/{customer}/tags/mql', [CustomerTagController::class, 'addMql'])->name('customers.tags.add_mql');
    // Chat
    Route::post('/start-chat', [CustomerController::class, 'startConversationFromCRM'])->name('customers.start-chat');

    // Rutas con parámetros específicos
    Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->whereNumber('customer');
    Route::post('/{customer}/update', [CustomerController::class, 'update'])->whereNumber('customer');
    Route::get('/{customer}/show', [CustomerController::class, 'show'])->whereNumber('customer')->name('customers.show');
    Route::get('/{customer}/show/{action}', [CustomerController::class, 'showAction'])->whereNumber('customer');
    Route::get('/{customer}/destroy', [CustomerController::class, 'destroy'])->whereNumber('customer');
    Route::post('/{customer}/action/store', [CustomerController::class, 'storeAction'])->whereNumber('customer');
    Route::post('/{customer}/action/sale', [CustomerController::class, 'saleAction'])->whereNumber('customer');
    Route::post('/{customer}/action/opportunity', [CustomerController::class, 'opportunityAction'])->whereNumber('customer');
    Route::post('/{customer}/action/poorly_rated', [CustomerController::class, 'poorlyRatedAction'])->whereNumber('customer');
    Route::post('/{customer}/action/pqr', [CustomerController::class, 'pqrAction'])->whereNumber('customer');
    Route::post('/{customer}/action/spare', [CustomerController::class, 'spareAction'])->whereNumber('customer');
    Route::post('/{customer}/action/mail', [CustomerController::class, 'storeMail'])->whereNumber('customer');
    Route::get('/{customer}/assignMe', [CustomerController::class, 'assignMe'])->whereNumber('customer');
    Route::post('/{customer}/audience', [CustomerController::class, 'storeAudience'])->whereNumber('customer');
    Route::get('/history/{customer}/show', [CustomerController::class, 'showHistory'])->whereNumber('customer');
    Route::post('/{customer}/notes', [CustomerController::class, 'updateNotes'])->whereNumber('customer');

    // Específicas
    Route::get('/ajax/update_user', [CustomerController::class, 'updateAjax']);
    Route::get('/logistica', [CustomerController::class, 'newIndex']);
    Route::get('/{customer}/action/updateAjax', [CustomerController::class, 'updateAjaxStatus'])->whereNumber('customer');

    // Rutas de fases
    Route::get('/phase/{pid}', [CustomerController::class, 'customers'])->whereNumber('pid');

    // Esta debe ir al final para no interceptar las anteriores
    Route::get('/{pid}', [CustomerController::class, 'dragleads'])->whereNumber('pid');

    Route::get('/{customer}/email/1', [CustomerController::class, 'mail']);
    Route::get('/{customer}/actions/createMail/{email}', [ActionController::class, 'trackEmail']);
    Route::get('/{customer}/actions/trackEmail/{email}', [ActionController::class, 'trackEmail']);
});


// Optimizer Routes
Route::middleware('auth')
    ->prefix('optimize/customers')
    ->name('optimizer.')
    ->group(function () {

        // Buscar duplicados (por email o teléfono)
        Route::get('/consolidateDuplicates', [OptimizerController::class, 'consolidateDuplicates'])
            ->name('consolidate');

        // Consolida (POST)
        Route::post('/merge', [OptimizerController::class, 'mergeDuplicates'])
            ->name('merge');

        // (Opcional) tus rutas antiguas, si aún las usas
        Route::get('/findDuplicates', [OptimizerController::class, 'findDuplicates']);
        Route::get('/findDuplicates/show', [OptimizerController::class, 'showDuplicates']);

        // Legacy (si quieres mantener el viejo flujo GET)
        Route::get('/mergeDuplicates', [OptimizerController::class, 'mergeDuplicates_'])
            ->name('merge_legacy');
    });

// Lead Routes
Route::middleware('auth')->get('/leads', [CustomerController::class, 'leads'])->name('leads');
Route::middleware('auth')->get('/leads/excel', [CustomerController::class, 'excel']);
Route::middleware('auth')->get('/leads/change_status', [CustomerController::class, 'change_status']);

// Role Routes
Route::middleware('auth')->prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('roles');
    Route::get('/create', [RoleController::class, 'create']);
    Route::post('/', [RoleController::class, 'store']);
    Route::post('/{role}/update', [RoleController::class, 'update']);
    Route::get('/{role}/edit', [RoleController::class, 'edit']);
    Route::get('/{role}/show', [RoleController::class, 'show']);
    Route::get('/{role}/destroy', [RoleController::class, 'destroy']);
});

// Site Routes
Route::get('/config', [SiteController::class, 'config']);
Route::get('/report', [SiteController::class, 'report']);
Route::middleware('auth')->prefix('whatsapp-accounts')->name('whatsapp-accounts.')->group(function () {
    Route::get('/', [\App\Http\Controllers\WhatsAppAccountController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\WhatsAppAccountController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\WhatsAppAccountController::class, 'store'])->name('store');
    Route::post('/send-test', [\App\Http\Controllers\WhatsAppAccountController::class, 'sendTestTemplate'])->name('send-test');
    Route::post('/{whatsappAccount}/default', [\App\Http\Controllers\WhatsAppAccountController::class, 'makeDefault'])->name('default');
    Route::delete('/{whatsappAccount}', [\App\Http\Controllers\WhatsAppAccountController::class, 'destroy'])->name('destroy');
    Route::put('/{whatsappAccount}', [\App\Http\Controllers\WhatsAppAccountController::class, 'update'])->name('update');
    Route::post('/{whatsappAccount}/sync-templates', [\App\Http\Controllers\WhatsAppAccountController::class, 'syncTemplates'])->name('sync-templates');
});

// User Routes
Route::middleware('auth')->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users');
    Route::get('/create', [UserController::class, 'create']);
    Route::get('/{user}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{user}/edit', [UserController::class, 'edit']);
    Route::post('/{user}/edit', [UserController::class, 'edit']);
    Route::post('/{user}/update', [UserController::class, 'update']);
});

// Customer Status Routes
Route::middleware('auth')->prefix('customer_statuses')->group(function () {
    Route::get('/', [CustomerStatusController::class, 'index']);
    Route::get('/create', [CustomerStatusController::class, 'create']);
    Route::get('/{customer_status}', [CustomerStatusController::class, 'show']);
    Route::post('/', [CustomerStatusController::class, 'store']);
    Route::post('/{customer_status}/edit', [CustomerStatusController::class, 'edit']);
    Route::get('/{customer_status}/edit', [CustomerStatusController::class, 'edit']);
    Route::post('/{customer_status}/update', [CustomerStatusController::class, 'update']);
    Route::post('/{customer_status}/updateStatus', [CustomerStatusController::class, 'updateStatus']);
    Route::get('/{customer_status}/destroy', [CustomerStatusController::class, 'destroy']);
});




Route::middleware('auth')
    ->prefix('customer_files')
    ->name('customer_files.')
    ->group(function () {

        Route::get('/', function () {
            return redirect()->route('reports.missing_customer_files');
        })->name('index');
        
        // Crear (subir uno o varios)
        Route::post('/', [CustomerFileController::class, 'store'])
            ->name('store');

        // Reponer un archivo faltante (manteniendo el mismo nombre)
        Route::post('{file}/reupload', [CustomerFileController::class, 'reupload'])
            ->name('reupload');

        // Eliminar (mejor DELETE; deja el GET solo si ya lo usas en enlaces antiguos)
        Route::delete('{file}', [CustomerFileController::class, 'destroy'])
            ->name('destroy');

            /*
        // Compatibilidad con enlaces antiguos tipo GET
        Route::get('{file}/delete', [CustomerFileController::class, 'destroy'])
            ->name('delete.legacy');
    */
    });

// API Routes
Route::middleware('auth')->prefix('api')->group(function () {
    Route::post('/customers/saveCustomer', [APIController::class, 'saveApiWatoolbox']);
    
    Route::get('/customers/saveCheckout', [APIController::class, 'saveApiCheckout']);
    Route::get('/products/{id}/get', [APIController::class, 'getProduct']);
    Route::get('/products/get', [APIController::class, 'getProducts']);
    Route::get('/rd/saveCustomer', [APIController::class, 'saveFromRD']);
    Route::post('/rd/saveCustomer', [APIController::class, 'saveFromRD']);
    Route::get('/customers/save-calculate', [APIController::class, 'saveCustomerCalculate']);
    Route::post('/customers/referenceWompi', [APIController::class, 'getWompiReference']);
    Route::get('/histories/{id}/get', [APIController::class, 'getHistory']);
    Route::get('/actions/{id}/get', [APIController::class, 'getAction']);
    Route::get('/update_lead_score', [APIController::class, 'saveFromRD2']);
    Route::post('/update_lead_score', [APIController::class, 'saveFromRD2']);
});
Route::get('api/customers/saveCustomer', [APIController::class, 'saveApi'])->withoutMiddleware(['auth']);
Route::post('api/customers/update', [APIController::class, 'updateFromRD'])->withoutMiddleware(['auth'])->name('updateRD');

// References Routes
Route::middleware('auth')->prefix('references')->group(function () {
    Route::get('/', [ReferencesController::class, 'show']);
    Route::get('/{customer}/create', [ReferencesController::class, 'create']);
    Route::post('/', [ReferencesController::class, 'store']);
    Route::get('/{id}/destroy', [ReferencesController::class, 'destroy']);
    Route::get('/{id}/edit', [ReferencesController::class, 'edit']);
    Route::post('/{id}/update', [ReferencesController::class, 'update']);
    Route::get('/{id}/wompi_link', [ReferencesController::class, 'getLink']);
});

// Wompi Routes
//Route::get('/wompi_link/{reference}', [WompiController::class, 'getLink']);

// Mail Routes
Route::get('/testMail', [SiteController::class, 'testMail']);
Route::get('/mail/send', [MailController::class, 'send']);
Route::get('/emails/store', [EmailController::class, 'store']);
Route::get('/emails/{email}/store', [EmailController::class, 'store']);
Route::get('/emails/send', [EmailController::class, 'send']);

// Email Routes
Route::middleware('auth')->prefix('emails')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('emails');
    Route::get('/create', [UserController::class, 'create']);
    Route::get('/{user}', [UserController::class, 'show']);
});

// Job Routes
Route::get('/jobs', [JobController::class, 'index']);

// Action Routes
Route::middleware('auth')->prefix('actions')->group(function () {
    Route::get('/', [ActionController::class, 'index'])->name('actions');
    Route::get('/{action}/show', [ActionController::class, 'show']);
    Route::get('/{action}/edit', [ActionController::class, 'edit']);
    Route::get('/{action}/update', [ActionController::class, 'update']);
    Route::get('/{action}/destroy', [ActionController::class, 'destroy']);
    Route::get('/schedule', [ActionController::class, 'schedule']);
});

Route::post('/customers/action/pending', [ActionController::class, 'completePendingAction'])->middleware('auth');

// Pending Actions Routes
Route::middleware('auth')->prefix('pending_actions')->group(function () {
    Route::get('/', [ActionController::class, 'indexPending'])->name('pending_actions');
    Route::get('/paginacion', [ActionController::class, 'paginacion']);
    Route::get('/notifications', [ActionController::class, 'getPendingActions']);
});

// Action Type Routes
Route::middleware('auth')->prefix('action_type')->group(function () {
    Route::get('/', [ActionTypeController::class, 'index']);
    Route::post('/create', [ActionTypeController::class, 'store']);
    Route::get('/{id}/destroy', [ActionTypeController::class, 'destroy']);
    Route::get('/{id}/edit', [ActionTypeController::class, 'edit']);
    Route::post('/{id}/update', [ActionTypeController::class, 'update']);
    Route::get('/{id}/show', [ActionTypeController::class, 'show']);
});

// Report Routes
Route::middleware('auth')->prefix('reports')->group(function () {
    Route::get('/users', [ReportController::class, 'users']);
    Route::get('/users/customer/status', [ReportController::class, 'userCustomerStatus']);
    Route::get('/users/customer/actions', [ReportController::class, 'userCustomerActions']);
    Route::get('/customers_time', [ReportController::class, 'customersTime']);
    Route::get('/views/customers_followup', [ReportController::class, 'RFM_CustomersFollowups']);
    Route::get('/views/daily_customers_followup', [CustomerController::class, 'daily']);
    Route::get('/fm', [ReportController::class, 'RFM']);
});

// Configs
Route::middleware('auth')->get('/configs', [\App\Http\Controllers\ConfigController::class, 'index'])->name('configs.index');

// Dashboard Routes

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard/scroll_active', [ReportController::class, 'scrollActive']);
Route::get('/dashboard/scroll_inactive', [ReportController::class, 'scrollInactive']);
Route::get('/dashboard/scroll_inactive_without_user', [ReportController::class, 'scrollInactiveWithOutUser']);

// Order Routes
Route::middleware('auth')->prefix('orders')->group(function () {
    Route::post('/{id}/update', [OrderController::class, 'update']);
    Route::post('/', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/create/{cid}', [OrderController::class, 'create'])->name('orders.create');
    
    
    //Route::get('/{cid}/quotes/create', [OrderController::class, 'create'])->name('orders.create');
    //Route::get('/create/sid/{sid}', [OrderController::class, 'create'])->name('orders.create');
    Route::get('/{oid}/add/product', [OrderController::class, 'addProducts']);
    Route::get('/sid/{sid}', [OrderController::class, 'index']);
    Route::post('/search_customer', [OrderController::class, 'searchCustomer']);
    Route::get('/product/{pid}', [OrderController::class, 'orderProduct']);
    Route::post('/save', [OrderController::class, 'SaveOrder']);
    Route::get('/show/sid/{id}', [OrderController::class, 'showQuote']);
    Route::post('/storeproduct', [OrderController::class, 'storeProduct']);
    Route::get('/product/{oid}/destroy', [OrderController::class, 'destroy']);
    Route::get('/{oid}/proforma', [OrderController::class, 'showProforma'])->name('orders.showProforma');
    Route::get('/{oid}/proforma_co', [OrderController::class, 'showProformaCO'])->name('orders.showProformaCO');
    Route::get('/sid/{sid}/report', [OrderController::class, 'indexReport']);
    Route::get('/{id}/show', [OrderController::class, 'show']);
    
    Route::get('/{id}/quote', [OrderController::class, 'quote']);
    Route::get('/{id}/edit', [OrderController::class, 'edit']);
    Route::get('/{id}/destroy', [OrderController::class, 'destroy']);
});

// Order Transaction Routes
Route::middleware('auth')->prefix('orders/transactions')->group(function () {
    Route::get('/{id}/destroy', [OrderTransactionController::class, 'destroy']);
    Route::get('/{tid}/edit', [OrderTransactionController::class, 'edit']);
    Route::post('/', [OrderTransactionController::class, 'store']);
    Route::post('/{tid}/update', [OrderTransactionController::class, 'update']);
});

// Product Routes
Route::middleware('auth')->prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products');
    Route::get('/show/{id}', [ProductController::class, 'show'])->name('product-show');
    Route::get('/create', [ProductController::class, 'create']);
    Route::post('/store', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'show']);
    Route::get('/{id}/edit', [ProductController::class, 'edit']);
    Route::post('/edit/{id}', [ProductController::class, 'update']);
});

// Import Routes
Route::get('/import', [ImportController::class, 'index']);

// Mail Routes
Route::get('/send-to-teacheable', [APIController::class, 'sendToTeacheable']);
Route::get('/send-mail', [MailController::class, 'send']);

Route::get('/wa/test', [WhatsAppAPIController::class, 'test']);

// Clientify Contacts Routes
Route::get('/contacts_clientify', [APIController::class, 'getContacts']);

// Track Send Routes
Route::get('/track_send/{cid}/{aid}/{sid}/{str}', [APIController::class, 'trackWPAction']);

// Survey Routes
Route::middleware('auth')->prefix('metadata')->group(function () {
    Route::get('/1/pollAll', [MetaDataController::class, 'poll']);
    Route::get('/1/pollAll/{id}', [MetaDataController::class, 'pollId']);
    Route::get('/{id}/create', [MetaDataController::class, 'createSurvey']);
    Route::post('/{id}/save', [MetaDataController::class, 'save']);
    Route::get('/{id}/datapolicy', [MetaDataController::class, 'datapolicy']);
    Route::post('/{id}/store', [MetaDataController::class, 'store']);
    Route::get('/2/{customer}', [MetaDataController::class, 'freeClass']);
    Route::post('/2/{customer}/update', [MetaDataController::class, 'freeClassUpdate']);
    Route::get('/2/{customer}/update', [MetaDataController::class, 'freeClassUpdate']);
    Route::get('/3/{customer}', [MetaDataController::class, 'schedule']);
    Route::post('/3/{customer}/update', [MetaDataController::class, 'freeClassUpdate']);
    Route::get('/3/{customer}/update', [MetaDataController::class, 'freeClassUpdate']);
    // Route::get('/{id}/create/poe/{cid}', [MetaDataController::class, 'createpoe']);
    // Route::post('/{id}/store/poe', [MetaDataController::class, 'storepoe']);
});

// Define the route without the 'auth' middleware
Route::get('metadata/{id}/create/poe/{cid}', [MetaDataController::class, 'createpoe'])->withoutMiddleware(['auth']);
Route::post('metadata/{id}/store/poe', [MetaDataController::class, 'storepoe'])->withoutMiddleware(['auth']);


// Audience Routes
Route::middleware('auth')->prefix('audiences')->group(function () {
    Route::get('/', [AudienceController::class, 'index']);
    Route::get('/{id}/show', [AudienceController::class, 'show']);
    Route::get('/{id}/whatsapp', [AudienceController::class, 'whatsapp']);
    Route::get('/{id}/send', [AudienceController::class, 'send']);
    Route::get('/create', [AudienceController::class, 'create']);
    Route::post('/', [AudienceController::class, 'store']);
    Route::get('/{id}/customers', [AudienceController::class, 'createCustomers']);
    Route::post('/{id}/customers', [AudienceController::class, 'storeCustomers']);
    Route::get('/{id}/customers/{cid}/destroy', [AudienceController::class, 'destroyCustomer']);
    Route::get('/{id}/campaign/{cid}/show', [AudienceController::class, 'whatsapp']);
    Route::get('/{id}/campaign/{cid}/show_rpa', [AudienceController::class, 'showRpa']);
});

// Campaign Routes
Route::middleware('auth')->prefix('campaigns')->group(function () {
    Route::get('/', [CampaignController::class, 'index']);
    Route::get('/{id}/show', [CampaignController::class, 'show']);
    Route::get('/{id}/edit', [CampaignController::class, 'edit']);
    Route::post('/{id}/update', [CampaignController::class, 'update']);
    Route::get('/{id}/send', [CampaignController::class, 'send']);
    Route::get('/create', [CampaignController::class, 'create']);
    Route::post('/', [CampaignController::class, 'store']);
    Route::post('/message/{mid}/delete', [CampaignController::class, 'destroyMessage']);
    Route::post('/message/{mid}/update', [CampaignController::class, 'updateMessage']);
    Route::post('/message/store', [CampaignController::class, 'storeMessage']);
    Route::get('/{mid}/update', [CampaignController::class, 'updateCampaign']);
    Route::get('/{id}', [CampaignController::class, 'getMessages']);
    Route::get('/{id}/getPhone/setMessage/{message}', [CampaignController::class, 'getPhone']);
});

// Callback Routes
Route::post('/auth/callback', [APIController::class, 'callBack']);
Route::get('/auth/callback', [APIController::class, 'callBack']);

// Reports
Route::get('/reports/fm', [ReportController::class, 'RFM']);
Route::get('/customer-files/{file}/open', [CustomerFileController::class, 'open'])
     ->name('customer_files.open'); 


Route::get('{file}/open', [CustomerFileController::class, 'open'])->name('open');




Route::get('/watoolbox/test', function(){

    return view('test');
});


Route::get('/reset_password', function(){
    // Generar un hash de prueba
    $hash = Hash::make('myseo2025');
    echo $hash."<br>";

    $contraseñaPlana = "myseo2025"; // La contraseña a verificar
    $usuario = User::find(8);

    if (!$usuario) {
        echo "Usuario con ID 8 no encontrado.";
        return;
    }

    $hashAlmacenado = $usuario->password;

    if (Hash::check($contraseñaPlana, $hashAlmacenado)) {
        echo "El hash es correcto.";
    } else {
        echo "El hash no coincide.";
    }

});

Route::get('/rdtest', [RDTestController::class, 'test']);
Route::post('/rdtest', [RDTestController::class, 'handleRequest'])->name('rdtest.post');

Route::get('/test-rd', function () {
    $payload = json_decode(file_get_contents(base_path('lead.json')), true);

    //dd(file_get_contents(base_path('lead.json')));

    $request = Request::create('/api/customers/update', 'POST', [], [], [], [], json_encode($payload));
    $request->headers->set('Content-Type', 'application/json');

    // Llama directamente al controlador
    $controller = app(APIController::class);
    return $controller->updateFromRD($request);
});


// routes/web.php

Route::middleware(['auth'])->group(function () {
    Route::get('/madrid2025',        [Madrid2025Controller::class, 'dashboard'])->name('madrid2025.dashboard');
    Route::get('/madrid2025/export', [Madrid2025Controller::class, 'export'])->name('madrid2025.export');
    Route::get('/madrid2025/schedule', [Madrid2025Controller::class, 'schedule'])->name('madrid2025.schedule');

});





// routes/web.php
Route::get('/reportes/madrid', [\App\Http\Controllers\MadridDashboardController::class, 'index'])
    ->name('madrid.dashboard');


Route::get('/actions', [ActionController::class, 'index'])->name('actions.index');    

Route::put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
    ->name('roles.updatePermissions');

// Feed de eventos para FullCalendar
Route::get('/actions/calendar/feed', [ActionController::class, 'calendarFeed'])
    ->name('actions.calendar.feed');

// Reprogramar arrastrando en el calendario
Route::put('/actions/{action}/reschedule', [ActionController::class, 'reschedule'])
    ->name('actions.reschedule');

Route::get('/actions/{id}/show', [ActionController::class, 'show'])
     ->name('actions.show');

Route::get('/test-spaces', function () {
    Storage::disk('spaces')->put('test.txt', 'hola DO Spaces', 'public');
    return Storage::disk('spaces')->url('test.txt');
});


Route::get('/reports/missing-customer-files/verify-month', [ReportController::class, 'verifyMonth'])
    ->name('reports.missing_customer_files.verify_month'); // ?year=2025&month=2&user_id=


 Route::get('/reports/missing-customer-files', [ReportController::class, 'missingCustomerFiles'])
    ->name('reports.missing_customer_files');

Route::get('/reports/missing-customer-files/verify/{customer}', [ReportController::class, 'verifyCustomerFiles'])
    ->name('reports.missing_customer_files.verify'); // AJAX   

Route::get(
    '/reports/missing-customer-files/{customer}/verify',
    [ReportController::class, 'verifyCustomer']
)->name('reports.missing_customer_files.verify');

Route::get('/landing/checkin', [LandingController::class,'checkinForm'])->name('landing.checkin');
Route::post('/landing/checkin', [LandingController::class,'checkinSubmit'])->name('landing.checkin.submit');
Route::post('/landing/rebook', [LandingController::class,'rebook'])->name('landing.rebook');
Route::post('/landing/cancel', [LandingController::class,'cancel'])->name('landing.cancel');


Route::get('/test-email', function () {
    Resend::emails()->send([
        'from' => 'Maquiempanadas <marketing@maquiempanadas.com>',
        'to' => 'nicolas@myseocompany.co',
        'subject' => 'Test Resend desde Laravel OK',
        'text' => 'Test Resend desde Laravel OK',
    ]);
    /*
    Mail::raw('Test Mailtrap desde Laravel OK', function ($m) {
        $m->to('nicolas@myseocompany.co')->subject('Mailtrap funcionando');
    });

    */
    return 'Email enviado por resend (si todo está bien)';
});

