<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bk_subject_access_codes', function (Blueprint $table) {
            $table->dropIndex('bk_subject_access_codes_access_code_index');
            $table->text('access_code')->change();
        });
        
        \Illuminate\Support\Facades\DB::statement('CREATE INDEX bk_subject_access_codes_access_code_index ON bk_subject_access_codes (access_code(255))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_subject_access_codes', function (Blueprint $table) {
            $table->dropIndex('bk_subject_access_codes_access_code_index');
            $table->string('access_code', 255)->change();
            $table->index('access_code');
        });
    }
};
