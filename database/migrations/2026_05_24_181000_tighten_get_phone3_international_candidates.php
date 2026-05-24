<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS getPhone3');

        DB::unprepared(<<<'SQL'
CREATE FUNCTION `getPhone3`(phone VARCHAR(255), phone2 VARCHAR(255), contact_phone2 VARCHAR(255))
RETURNS varchar(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
DETERMINISTIC
BEGIN
    DECLARE candidate VARCHAR(255);
    DECLARE digits VARCHAR(255);
    DECLARE result VARCHAR(255) DEFAULT NULL;

    SET candidate = phone;
    SET digits = REGEXP_REPLACE(COALESCE(candidate, ''), '[^0-9]', '');
    IF digits REGEXP '^3[0-9]{9}$' THEN
        SET result = CONCAT('57', digits);
    ELSEIF digits REGEXP '^573[0-9]{9}$' THEN
        SET result = digits;
    ELSEIF digits REGEXP '^60[1-8][0-9]{7}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[2-8][0-9]{6}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[1-9][0-9]{10,14}$' THEN
        SET result = digits;
    END IF;

    IF result IS NOT NULL THEN
        RETURN result;
    END IF;

    SET candidate = phone2;
    SET digits = REGEXP_REPLACE(COALESCE(candidate, ''), '[^0-9]', '');
    IF digits REGEXP '^3[0-9]{9}$' THEN
        SET result = CONCAT('57', digits);
    ELSEIF digits REGEXP '^573[0-9]{9}$' THEN
        SET result = digits;
    ELSEIF digits REGEXP '^60[1-8][0-9]{7}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[2-8][0-9]{6}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[1-9][0-9]{10,14}$' THEN
        SET result = digits;
    END IF;

    IF result IS NOT NULL THEN
        RETURN result;
    END IF;

    SET candidate = contact_phone2;
    SET digits = REGEXP_REPLACE(COALESCE(candidate, ''), '[^0-9]', '');
    IF digits REGEXP '^3[0-9]{9}$' THEN
        SET result = CONCAT('57', digits);
    ELSEIF digits REGEXP '^573[0-9]{9}$' THEN
        SET result = digits;
    ELSEIF digits REGEXP '^60[1-8][0-9]{7}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[2-8][0-9]{6}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[1-9][0-9]{10,14}$' THEN
        SET result = digits;
    END IF;

    RETURN result;
END
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS getPhone3');

        DB::unprepared(<<<'SQL'
CREATE FUNCTION `getPhone3`(phone VARCHAR(255), phone2 VARCHAR(255), contact_phone2 VARCHAR(255))
RETURNS varchar(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
DETERMINISTIC
BEGIN
    DECLARE candidate VARCHAR(255);
    DECLARE digits VARCHAR(255);
    DECLARE result VARCHAR(255) DEFAULT NULL;

    SET candidate = phone;
    SET digits = REGEXP_REPLACE(COALESCE(candidate, ''), '[^0-9]', '');
    IF digits REGEXP '^3[0-9]{9}$' THEN
        SET result = CONCAT('57', digits);
    ELSEIF digits REGEXP '^573[0-9]{9}$' THEN
        SET result = digits;
    ELSEIF digits REGEXP '^60[1-8][0-9]{7}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[2-8][0-9]{6}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[1-9][0-9]{7,14}$' THEN
        SET result = digits;
    END IF;

    IF result IS NOT NULL THEN
        RETURN result;
    END IF;

    SET candidate = phone2;
    SET digits = REGEXP_REPLACE(COALESCE(candidate, ''), '[^0-9]', '');
    IF digits REGEXP '^3[0-9]{9}$' THEN
        SET result = CONCAT('57', digits);
    ELSEIF digits REGEXP '^573[0-9]{9}$' THEN
        SET result = digits;
    ELSEIF digits REGEXP '^60[1-8][0-9]{7}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[2-8][0-9]{6}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[1-9][0-9]{7,14}$' THEN
        SET result = digits;
    END IF;

    IF result IS NOT NULL THEN
        RETURN result;
    END IF;

    SET candidate = contact_phone2;
    SET digits = REGEXP_REPLACE(COALESCE(candidate, ''), '[^0-9]', '');
    IF digits REGEXP '^3[0-9]{9}$' THEN
        SET result = CONCAT('57', digits);
    ELSEIF digits REGEXP '^573[0-9]{9}$' THEN
        SET result = digits;
    ELSEIF digits REGEXP '^60[1-8][0-9]{7}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[2-8][0-9]{6}$' THEN
        SET result = NULL;
    ELSEIF digits REGEXP '^[1-9][0-9]{7,14}$' THEN
        SET result = digits;
    END IF;

    RETURN result;
END
SQL);
    }
};
