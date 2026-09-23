<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('idempotency_key', 100);
            // sha256 of method + path + body: the same key must not be reused for another request
            $table->string('request_fingerprint', 64);
            // null while the first request is still being processed
            $table->unsignedSmallInteger('response_status_code')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamps();
            // guarantees that only one request with a given key can be processed, even concurrently
            $table->unique(['user_id', 'idempotency_key']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
