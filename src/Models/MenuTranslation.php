<?php

namespace jCube\Models;

use Illuminate\Database\Eloquent\Model;

class MenuTranslation extends Model
{

  public $timestamps = false;
  protected $fillable = [
    'menu_id',
    'name',
    'locale',
  ];
}
