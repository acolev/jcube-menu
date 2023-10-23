<?php

namespace jCube\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  
  protected $namespace = 'jCube\Http\Controllers';
  
  public function boot(): void
  {
    $this->registerLoads();
    $this->registerComponents();
    $this->registerRoutes();
  }
  
  
  protected function registerLoads()
  {
    $this->loadMigrationsFrom(dirname(dirname(__DIR__)) . '/database/migrations');
    $this->loadViewsFrom(dirname(__DIR__) . '/Views/admin', 'admin');
  }
  
  protected function registerComponents()
  {
    Blade::anonymousComponentPath(dirname(__DIR__) . '/Views/components');
  }
  
  protected function registerRoutes()
  {
    Route::namespace($this->namespace)->group(function () {
      // admin routes
      Route::middleware('web', 'admin')
        ->namespace('Admin')
        ->prefix(env('ADMIN_PREFIX') ?: 'admin')
        ->name('admin.')
        ->group(dirname(__DIR__) . '/Routes/admin.php');
    });
  }
}