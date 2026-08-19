<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('keys')) {
            return;
        }

        Schema::create('keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_id')->unique();
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('assigned_to')->nullable();
            $table->enum('status', ['available', 'checked_out'])->default('available');
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keys');
    }
}
