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
    Schema::create('news_sentiments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('news_article_id')->constrained()->cascadeOnDelete();
        $table->unsignedInteger('positive_count')->default(0);
        $table->unsignedInteger('negative_count')->default(0);
        $table->string('sentiment_label');
        $table->decimal('sentiment_score', 5, 2)->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_sentiments');
    }
};
