<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'soporterapido@myseocompany.co',
            'password' => Hash::make('myseo2025')
        ]);
    

         DB::table('message_sources')->insert([
            [
                'type' => 'WhatsApp',
                'is_default' => true,
                'settings' => json_encode([
                    'webhook_url' => 'https://api.watoolbox.com/webhooks/19YC5Q41W',
                    'phone_number' => '573004410097',
                    
                ]),
                'APIKEY' => 'pHPC9TbqDGWVAPRGpzX0VxxNGPJeuXj03uWqt0QQ9b1e9bdf',
                
                
            ]
        ]);
        
        DB::table('message_sources')->insert([
            [
                'type' => 'WhatsApp ',
                'is_default' => false,
                'settings' => json_encode([
                    'webhook_url' => 'https://api.watoolbox.com/webhooks/5383ZPOSC',
                    'phone_number' => '573044483357',
                    
                ]),
                'APIKEY' => '7CH5>pya;\.5!)@j-g_b.J|@[$%|r~0S7{-)`@IUR,?9_011;Q',
                
                
            ]
        ]);
        
        DB::table('message_sources')->insert([
            [
                'type' => 'WhatsApp ',
                'is_default' => true,
                'settings' => json_encode([
                    'webhook_url' => 'https://api.watoolbox.com/webhooks/UOKEGPO4Q',
                    'phone_number' => '573206945548',
                    
                ]),
                'APIKEY' => 'II([:{~Lm}+FXA}$Hmc+90`ZBVca[Wo42}a.(bg1sX!Oo5)X',
                
                
            ]
        ]);
        DB::table('message_sources')->insert([
            [
                'type' => 'WhatsApp ',
                'is_default' => true,
                'settings' => json_encode([
                    'webhook_url' => 'https://api.watoolbox.com/webhooks/HZKQYNGET',
                    'phone_number' => '573142749156',
                    
                ]),
                'APIKEY' => '2y67l+^mq/|0#m(OU{|rmTU-FO{tTG9&{4_2UOQcv*89^d?5%',
                
                
            ]
        ]);
        DB::table('message_sources')->insert([
            [
                'type' => 'WhatsApp ',
                'is_default' => true,
                'settings' => json_encode([
                    'webhook_url' => 'https://api.watoolbox.com/webhooks/YCSKQA0CJ',
                    'phone_number' => '573148358924',
                    
                ]),
                'APIKEY' => 'fL2@£H@knd13b9JSBN;H\b;#bBz_67FQX*f^0bEKAF.n7-QfPp%',
                
                
            ]
        ]);
    }

}
