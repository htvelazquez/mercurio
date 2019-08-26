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

        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id',30)->unique();
            $table->timestamps();
        });

        Schema::create('snapshots', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contact_id')->unsigned();
            $table->tinyInteger('status')->unsigned()->default(0); // 0 = new, 1 = processed
            $table->tinyInteger('priority')->unsigned();
            $table->timestamps();
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('label');
            $table->integer('linkedin_id')->unsigned()->nullable();
            $table->string('link')->nullable();
            $table->timestamps();
        });

        Schema::create('study_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('snapshot_educations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('snapshot_id')->unsigned();
            $table->integer('school_id')->unsigned()->nullable();
            $table->integer('study_field_id')->unsigned()->nullable();
            $table->string('degree')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('snapshots');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('study_field_id')->references('id')->on('study_fields');
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('snapshot_metadatas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('snapshot_id')->unsigned();
            $table->string('name', 128)->nullable();
            $table->string('firstName', 128)->nullable();
            $table->string('lastName', 128)->nullable();
            $table->integer('location_id')->unsigned();
            $table->string('summary')->nullable();
            $table->integer('totalConnections')->nullable();
            $table->string('twitter',50)->nullable();
            $table->date('birthday')->nullable();
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('snapshots');
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 128);
            $table->string('label', 128);
            $table->integer('linkedin_id');
            $table->string('link', 128)->nullable();
            $table->timestamps();
        });

        Schema::create('snapshot_experiences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('snapshot_id')->unsigned();
            $table->integer('company_id')->unsigned();
            $table->string('jobTitle');
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->boolean('before_diff')->nullable();
            $table->boolean('after_diff')->nullable();
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('snapshots');
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('snapshot_skills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('snapshot_id')->unsigned();
            $table->integer('skill_id')->unsigned();
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('snapshots');
            $table->foreign('skill_id')->references('id')->on('skills');
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('snapshot_languages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('snapshot_id')->unsigned();
            $table->integer('language_id')->unsigned();
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('snapshots');
            $table->foreign('language_id')->references('id')->on('languages');
        });

        Schema::create('snapshot_phones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('snapshot_id')->unsigned();
            $table->string('phone');
            $table->integer('type')->nullable(); //
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('snapshots');
        });

        Schema::create('snapshot_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('snapshot_id')->unsigned();
            $table->string('email');
            $table->integer('type')->nullable(); //
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('snapshots');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('before_snapshot_id')->unsigned()->nullable();
            $table->integer('after_snapshot_id')->unsigned();
            $table->smallInteger('status')->unsigned()->default(0); // 0 = new, 1 = processed
            $table->tinyInteger('case')->default(0);
            $table->boolean('checked_by_solver')->default(0);
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
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('snapshot_emails');
        Schema::dropIfExists('snapshot_phones');
        Schema::dropIfExists('snapshot_languages');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('snapshot_skills');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('snapshot_experiences');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('snapshot_metadatas');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('snapshot_educations');
        Schema::dropIfExists('study_fields');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('snapshots');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
}
