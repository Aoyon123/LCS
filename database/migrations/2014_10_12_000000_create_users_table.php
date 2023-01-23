<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string("phone", 15)->comment("phone always unique")->nullable();
            $table->string('email', 50)->nullable();
            $table->string('password')->nullable();
            $table->string('nid', 50)->nullable();
            $table->string('dob', 50)->nullable();
            $table->string('profile_image')->nullable();
            $table->string('gender', 10)->nullable();
            $table->tinyInteger('status')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('type', 20);
            $table->string('is_nid_verified', 255)->nullable();
            $table->string('is_email_verified', 255)->nullable();
            $table->string('is_phone_verified', 255)->nullable();
            $table->string('years_of_experience')->nullable();
            $table->string('current_profession')->nullable();
            $table->string('nid_front')->nullable();
            $table->string('nid_back')->nullable();
            $table->string('code')->nullable();
            $table->string('rates')->nullable();
            $table->tinyInteger('approval')->comment("
            0 intial,
            1 pending,
            2 approval,
            3 Reject,
            4 Deactivated
            ");
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('schedule', 250)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
