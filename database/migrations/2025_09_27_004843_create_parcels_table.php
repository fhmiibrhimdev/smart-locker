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
        Schema::create('parcel', function (Blueprint $table) {
            $table->id();
            $table->text('id_loker')->nullable();
            $table->text('id_kurir')->nullable();
            $table->text('no_penerima')->nullable();
            $table->text('kode_paket')->nullable();
            $table->text('kode_pengambilan')->nullable();
            $table->enum('status', ['STORED', 'CLAIMED', 'EXPIRED'])->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel');
    }
};
