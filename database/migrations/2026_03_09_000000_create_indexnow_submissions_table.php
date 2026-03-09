<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indexnow_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('entry_id')->index();
            $table->string('url');
            $table->string('batch_id')->index();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->timestamp('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indexnow_submissions');
    }
};
