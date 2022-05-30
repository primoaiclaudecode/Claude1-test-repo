<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Menu extends Model
{
    const CACHE_ALL_KEY = 'menuGetAll_';
    const TTL           = 30 * 3600;
    public $table = "menu";
    public $timestamps = false;

    public static function getAll(): Collection
    {
        $values = Cache::get(self::CACHE_ALL_KEY);
        if (empty($values)) {
            $values = Menu::orderBy('weight')->get();
            Cache::put(self::CACHE_ALL_KEY, json_encode($values->toArray()), self::TTL);

            return $values;
        }

        $values = new Collection(json_decode($values));

        return $values;
    }
}
