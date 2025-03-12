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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type_event');
            $table->string('ref_command');
            $table->json('custom_field')->nullable();
            $table->string('item_name')->nullable();
            $table->decimal('item_price', 10, 2)->nullable();
            $table->string('devise', 10)->nullable();
            $table->string('command_name')->nullable();
            $table->string('env')->nullable();
            $table->string('token')->nullable();
            $table->string('api_key_sha256', 64)->nullable();
            $table->string('api_secret_sha256', 64)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
