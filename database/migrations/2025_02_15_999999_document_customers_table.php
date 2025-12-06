<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Documentation-only migration (no schema changes).
     *
     * Existing `customers` table (MySQL):
     * - id int PK auto_increment (signed)
     * - source_id int nullable
     * - maker int nullable
     * - count_empanadas varchar(255)
     * - scoring int nullable (0 bad, 1 opportunity)
     * - session_id varchar(250)
     * - custom_fields text
     * - rd_station_response text
     * - status_id int nullable
     * - inquiry_product_id int nullable
     * - name varchar(100)
     * - document varchar(100)
     * - position varchar(100)
     * - user_id int nullable
     * - lead_id varchar(20)
     * - phone varchar(50)
     * - phone2 varchar(50)
     * - contact_phone2 varchar(50)
     * - phone_wp varchar(255)
     * - area_code varchar(10)
     * - postal_code varchar(255)
     * - business varchar(100)
     * - business_document varchar(255)
     * - business_phone varchar(255)
     * - business_area_code varchar(255)
     * - business_address varchar(255)
     * - business_email varchar(255)
     * - business_city varchar(255)
     * - email varchar(100)
     * - address varchar(200)
     * - city varchar(100)
     * - country varchar(100)
     * - department varchar(200)
     * - contact_name varchar(250)
     * - contact_email varchar(250)
     * - contact_position varchar(250)
     * - bought_products varchar(250)
     * - purchase_date date nullable
     * - notes longtext
     * - request text
     * - technical_visit text
     * - gender varchar(2)
     * - scoring_interest int nullable
     * - scoring_profile varchar(1)
     * - rd_public_url varchar(250)
     * - src varchar(100)
     * - cid varchar(100)
     * - vas int nullable
     * - rd_source varchar(250)
     * - product_id int nullable
     * - updated_user_id int nullable
     * - creator_user_id int nullable
     * - country2 varchar(250)
     * - company_type varchar(250)
     * - number_venues varchar(100)
     * - empanadas_size varchar(250)
     * - utm_source varchar(100)
     * - utm_medium varchar(100)
     * - utm_campaign varchar(100)
     * - utm_term varchar(100)
     * - utm_content varchar(100)
     * - image_url varchar(250)
     * - linkedin_url varchar(250)
     * - company_description text
     * - created_at timestamp default CURRENT_TIMESTAMP
     * - updated_at timestamp default CURRENT_TIMESTAMP
     * - ad_name varchar(100)
     * - adset_name varchar(100)
     * - campaign_name varchar(100)
     * - country_temp varchar(100)
     * - facebook_id bigint unsigned nullable
     * - total_sold int nullable
     * - contact_phone2_last9 char(9) generated always as (right(contact_phone2, 9))
     * - phone_last9 char(9) generated always as (right(phone, 9))
     * - phone2_last9 char(9) generated always as (right(phone2, 9))
     * - phone_wp_last9 char(9) generated always as (right(phone_wp, 9))
     * Indexes: PK(id); several secondary indexes on email, phones, status_id, product_id, country, timestamps, source_id/maker, generated columns, etc.
     */
    public function up(): void
    {
        // Intentionally left blank to avoid modifying the existing production table.
    }

    public function down(): void
    {
        // Nothing to roll back; this migration is documentation-only.
    }
};
