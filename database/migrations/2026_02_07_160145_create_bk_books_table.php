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
        Schema::create('bk_books', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('accession', 20);
            $table->string('call_number', 50)->nullable();
            $table->text('author')->nullable()->fulltext('ftx_book_author');
            $table->string('title', 150)->fulltext('ftx_book_title');
            $table->enum('book_type', ['physical', 'ebook'])->default('physical');
            $table->text('description')->nullable();
            $table->string('edition', 50)->nullable();
            $table->string('place_of_publication', 50)->nullable();
            $table->string('publisher', 100)->nullable();
            $table->string('copyrights', 50)->nullable();
            $table->enum('remarks', ['On Shelf', 'Missing', 'Lost'])->nullable()->default('On Shelf');
            $table->bigInteger('category_id')->index('idx_book_category_id');
            $table->binary('cover_image')->nullable();
            $table->string('digital_copy_url')->nullable();
            $table->binary('barcode')->nullable();
            $table->unsignedTinyInteger('is_printed')->default(0);
            $table->enum('availability_status', ['Available', 'Unavailable', 'Borrowed', 'In Use', 'Reserved'])->nullable()->default('Unavailable');
            $table->enum('condition_status', ['New', 'Good', 'Fair', 'Poor'])->nullable()->default('Good');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();
            $table->string('accession_active')->nullable()->unique('unique_accession_not_deleted');

            $table->unique(['accession', 'deleted_at'], 'uniq_book_accession');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bk_books');
    }
};
