<?php

namespace jCube\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Lang;

class Menu extends Model
{

    public $timestamps = false;
    protected $guarded = ['id'];
    protected $hidden = ['translation'];
    protected $appends = ['name', 'route'];
    public static $lang;


    public function name(): Attribute
    {
        return Attribute::make(
            get: fn() => $this?->translation?->name,
            set: fn($value) => $this->id && $this->translation()
                    ->updateOrcreate(
                        [
                            'menu_id' => $this->id,
                            'locale' => self::$lang
                        ],
                        ['name' => $value]
                    )
        );
    }

    public function route(): Attribute
    {
        if (function_exists('getUrl')) {
            return Attribute::make(fn() => getUrl($this->object_type, $this->object_id, $this->lang ?: Lang::getLocale()));
        }
        return Attribute::make(fn() => SeoName::getUrl($this->object_type, $this->object_id, $this->lang ?: Lang::getLocale()));

    }


    public function subItems(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    public function allSubItems()
    {
        return $this->subItems()->with('subItems');
    }


    public function translation()
    {
        return $this->belongsTo(MenuTranslation::class, 'id', 'menu_id')
            ->where(function (Builder $query) {
                $query->where('locale', self::$lang ?: Lang::getLocale());
            });
    }

}
