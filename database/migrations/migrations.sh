#!/bin/bash
set -x
#php artisan migrate --path=database/migrations/2025_05_15_100501_create_rips_services_table.php
#php artisan migrate --path=database/migrations/2025_05_15_103326_create_rips_technology_purposes_table.php
#php artisan migrate --path=database/migrations/2025_05_15_110219_create_rips_collection_concept_table.php
#php artisan migrate --path=database/migrations/2025_05_15_110806_create_rips_service_reasons_table.php
#php artisan migrate --path=database/migrations/2025_05_17_010755_create_rips_admission_routes_table.php


php artisan migrate --path=database/migrations/2025_08_19_204715_add_channels_id_to_users_table.php