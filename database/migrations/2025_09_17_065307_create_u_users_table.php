<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('u_users', function (Blueprint $table) {
            // @note PK(accounts.id)と FK(u_users.id)を紐づけ
            $table->foreignId('id')->primary()->constrained('accounts')->onDelete('cascade');
            $table->string('public_id', 12)->unique()->comment('外部公開用ユーザ識別子');
            $table->string('nick_name', 64)->nullable()->comment('渾名');
            $table->json('meta_data')->nullable()->default(new Expression('(JSON_OBJECT())'))->comment('クライアント自由保存領域');
            $table->timestamps();

            $table->unique(['public_id'], 'unique_public_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('u_users');
    }
};
