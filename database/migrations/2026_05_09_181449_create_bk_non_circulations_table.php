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
        Schema::create('bk_non_circulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('usr_student_details')->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('usr_employee_details')->onDelete('cascade');
            $table->string('subject');
            $table->timestamp('borrowed_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bk_non_circulations');
    }
};
