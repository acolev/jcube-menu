<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(\jCube\Models\Menu::class, 'parent_id')->nullable();
            $table->integer('position');
            $table->string('name', 50);
            $table->morphs('object');
            $table->string('icon', 100);
            $table->string('type');
            $table->json('params');
        });

        Schema::create('menu_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\jCube\Models\Menu::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('locale', 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_translations');
    }
};
