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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->string('qr_code')->nullable(); // Path to QR code image
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->unsignedBigInteger('photographer_id')->index();
            
            // Toggle Controls
            $table->boolean('watermark_enabled')->default(true);
            $table->boolean('download_enabled')->default(false);
            
            // Image Processing Settings
            $table->unsignedInteger('compression_quality')->default(80);
            $table->string('watermark_position')->default('bottom-right'); // bottom-right, bottom-left, center, etc.
            $table->unsignedInteger('watermark_opacity')->default(80);
            
            // Event Status
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            
            // Storage
            $table->string('storage_disk')->default('local'); // local or s3
            $table->string('storage_path')->default('events');
            
            // Statistics
            $table->unsignedInteger('total_photos')->default(0);
            $table->unsignedInteger('total_downloads')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('photographer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
