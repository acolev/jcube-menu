<?php

use Illuminate\Support\Facades\Route;

// Menu Manager
Route::controller('MenuController')
  ->name('menu.')
  ->prefix('menu')
  ->group(function () {
    Route::get('/', 'index')->name('all');
    Route::get('list', 'list')->name('list');
    Route::post('create/{id}', 'store')->name('store')->where('id', '[0-9]+');
    Route::post('delete/{id}', 'delete')->name('delete')->where('id', '[0-9]+');
    Route::post('move', 'move')->name('move');
  });