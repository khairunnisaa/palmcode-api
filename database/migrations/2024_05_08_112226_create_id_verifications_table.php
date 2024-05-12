<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdVerificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('id_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('link_url_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_verifications');
    }
}
