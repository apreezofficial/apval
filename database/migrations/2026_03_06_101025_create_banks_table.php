<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('banks')) {
            return;
        }

        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->index();      // monnify
            $table->string('code', 20)->index();          // destination bank code
            $table->string('name', 120);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['provider','code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
