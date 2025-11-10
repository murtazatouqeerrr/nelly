<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add option_e if it doesn't exist
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            if (!Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
                $table->string('option_e')->nullable()->after('option_d');
            }
        });

        // Drop existing correct_answer check constraint
        $checks = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'missouri_quiz_banks' 
              AND CONSTRAINT_TYPE = 'CHECK'
        ");
        foreach ($checks as $check) {
            DB::statement("ALTER TABLE missouri_quiz_banks DROP CHECK `{$check->CONSTRAINT_NAME}`");
        }

        // Add new correct_answer check constraint
        DB::statement("
            ALTER TABLE missouri_quiz_banks 
            ADD CONSTRAINT missouri_quiz_banks_correct_answer_check 
            CHECK (correct_answer IN ('A','B','C','D','E'))
        ");
    }

    public function down()
    {
        // Drop option_e if exists
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            if (Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
                $table->dropColumn('option_e');
            }
        });

        // Drop the current correct_answer check constraint
        $checks = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'missouri_quiz_banks' 
              AND CONSTRAINT_TYPE = 'CHECK'
        ");
        foreach ($checks as $check) {
            DB::statement("ALTER TABLE missouri_quiz_banks DROP CHECK `{$check->CONSTRAINT_NAME}`");
        }

        // Restore original check constraint
        DB::statement("
            ALTER TABLE missouri_quiz_banks 
            ADD CONSTRAINT missouri_quiz_banks_correct_answer_check 
            CHECK (correct_answer IN ('A','B','C','D'))
        ");
    }
};
