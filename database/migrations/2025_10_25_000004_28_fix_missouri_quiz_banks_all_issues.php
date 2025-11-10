<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop columns safely
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            if (Schema::hasColumn('missouri_quiz_banks', 'chapter')) $table->dropColumn('chapter');
            if (Schema::hasColumn('missouri_quiz_banks', 'question')) $table->dropColumn('question');
            if (Schema::hasColumn('missouri_quiz_banks', 'difficulty')) $table->dropColumn('difficulty');
            if (Schema::hasColumn('missouri_quiz_banks', 'category')) $table->dropColumn('category');

            if (!Schema::hasColumn('missouri_quiz_banks', 'option_e')) {
                $table->string('option_e')->nullable()->after('option_d');
            }
            if (!Schema::hasColumn('missouri_quiz_banks', 'is_final_exam')) {
                $table->boolean('is_final_exam')->default(false)->after('state_required');
            }
        });

        // Drop foreign key if exists
        $fk = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'missouri_quiz_banks' 
              AND COLUMN_NAME = 'chapter_id' 
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        if (!empty($fk)) {
            DB::statement("ALTER TABLE missouri_quiz_banks DROP FOREIGN KEY `{$fk[0]->CONSTRAINT_NAME}`");
        }

        // Make chapter_id nullable
        DB::statement("ALTER TABLE missouri_quiz_banks MODIFY chapter_id BIGINT UNSIGNED NULL");

        // Drop old check constraints (MySQL 8+)
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

        // Re-add correct_answer check
        DB::statement("
            ALTER TABLE missouri_quiz_banks 
            ADD CONSTRAINT missouri_quiz_banks_correct_answer_check 
            CHECK (correct_answer IN ('A','B','C','D','E'))
        ");

        // Re-add foreign key
        Schema::table('missouri_quiz_banks', function (Blueprint $table) {
            $table->foreign('chapter_id')
                  ->references('id')
                  ->on('missouri_course_structures')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        // Drop foreign key
        $fk = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'missouri_quiz_banks' 
              AND COLUMN_NAME = 'chapter_id' 
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        if (!empty($fk)) {
            DB::statement("ALTER TABLE missouri_quiz_banks DROP FOREIGN KEY `{$fk[0]->CONSTRAINT_NAME}`");
        }

        // Drop check constraint
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

        // Reset correct_answer check
        DB::statement("
            ALTER TABLE missouri_quiz_banks 
            ADD CONSTRAINT missouri_quiz_banks_correct_answer_check 
            CHECK (correct_answer IN ('A','B','C','D'))
        ");
    }
};
