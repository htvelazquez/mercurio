<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Baseline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('related_salesforce_user')->nullable();
            $table->boolean('allow_automatic_backlog');
            $table->boolean('active');
            $table->boolean('tester');
            $table->integer('cap');
            $table->timestamps();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('priority');
            $table->integer('status'); // ready, done, paused, deleted
            $table->integer('client_id')->unsigned();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('priority');
            $table->string('url')->unique();
            $table->string('external_id')->nullable();
            $table->dateTime('time_pulled')->nullable();
            $table->dateTime('crawled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('processes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('status'); // running, done, failed
            $table->string('detail')->nullable();
            $table->timestamps();
        });

        Schema::create('jobs_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contact_id')->unsigned();
            $table->integer('job_id')->unsigned();
            $table->integer('status'); // ready, done, no-response, failed
            $table->integer('attempts');
            $table->timestamps();

            $table->foreign('contact_id')->references('id')->on('contacts');
            $table->foreign('job_id')->references('id')->on('jobs');
        });

        Schema::create('backlog_distributions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('priority');
            $table->integer('amount');
            $table->smallInteger('offset_days');
            $table->timestamps();
        });

        Schema::create('client_caps', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id')->unsigned();
            $table->integer('cap');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_caps');
        Schema::dropIfExists('backlog_distributions');
        Schema::dropIfExists('jobs_contacts');
        Schema::dropIfExists('processes');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
}
