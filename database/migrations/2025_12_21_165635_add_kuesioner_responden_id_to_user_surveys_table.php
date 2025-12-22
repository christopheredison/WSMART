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
        Schema::table('user_surveys', function (Blueprint $table) {
            $table->foreignId('kuesioner_responden_id')->nullable()->constrained('kuesioner_respondens')->onDelete('set null')->after('id');
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('user_answers', function (Blueprint $table) {
            $table->foreignId('kuesioner_responden_id')->nullable()->constrained('kuesioner_respondens')->onDelete('set null')->after('id');
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_surveys', function (Blueprint $table) {
            $table->dropForeign(['kuesioner_responden_id']);
            $table->dropColumn('kuesioner_responden_id');
        });

        Schema::table('user_answers', function (Blueprint $table) {
            $table->dropForeign(['kuesioner_responden_id']);
            $table->dropColumn('kuesioner_responden_id');
        });
    }
};
