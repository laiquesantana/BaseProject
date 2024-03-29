<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('photo')->default('profile.svg');
            $table->enum('tipo',['admin','default','gerente'])->nullable();
            $table->string('email')->unique();
            $table->string('cpf',11)->unique();
            $table->unsignedBigInteger('orgao_id');
            $table->foreign('orgao_id')->references('id')->on('orgao');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->softDeletes();
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
}
