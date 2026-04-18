<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_books', function (Blueprint $table) {
            $table->id();
            $table->string('accession', 20);
            $table->string('call_number', 50)->nullable();
            $table->text('author')->nullable();
            $table->string('title', 150);
            $table->enum('book_type', ['physical', 'ebook'])->default('physical');
            $table->text('description')->nullable();
            $table->string('edition', 50)->nullable();
            $table->string('isbn', 50)->nullable();
            $table->string('place_of_publication', 50)->nullable();
            $table->string('publisher', 100)->nullable();
            $table->string('copyrights', 50)->nullable();
            $table->enum('remarks', ['On Shelf', 'Unreturned', 'Missing', 'Lost', 'Discarded', 'Lost And Paid For', 'Lost And Replaced'])->default('on shelf');
            $table->bigInteger('category_id')->unsigned();
            $table->binary('cover_image')->nullable();
            $table->string('digital_copy_url')->nullable();
            $table->string('barcode')->nullable();
            $table->enum('availability_status', ['Available', 'Unavailable', 'Borrowed', 'In Use', 'Reserved'])->default('available');
            $table->enum('condition_status', ['New', 'Good', 'Fair', 'Poor'])->default('good');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();
            $table->string('active_accession', 20)->nullable();
            $table->unique('active_accession');
            $table->index('category_id');
            $table->foreign('category_id')->references('id')->on('bk_categories')->onDelete('cascade');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `bk_books` MODIFY `cover_image` LONGBLOB NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_books');
    }
};
