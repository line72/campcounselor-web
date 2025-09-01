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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('bandcamp_id', 50)->unique();
            $table->string('bandcamp_band_id', 50);
            $table->string('album', 4096);
            $table->string('artist', 4096);
            $table->string('url', 4096);
            $table->string('thumbnail_url', 4096)->nullable();
            $table->string('artwork_url', 4096)->nullable();
            $table->boolean('purchased')->default(false);
            $table->integer('rating')->default(-1);
            $table->text('comment')->default(''); // NOT NULL with default empty string to match Vala schema
            $table->integer('created_at'); // Integer timestamp like Vala
            $table->integer('updated_at'); // Integer timestamp like Vala
            
            // Create the same index as Vala schema
            $table->unique('bandcamp_id', 'bandcamp_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
