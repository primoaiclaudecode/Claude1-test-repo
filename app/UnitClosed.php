<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use App\User;

class UnitClosed extends Model
{
    protected $table = 'unit_closed';
    /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'id';
    /**
     * Maintain created_at and updated_at automatically
     *
     * @var boolean
     */
    public $timestamps = true;
    /**
     * Maintain created_at and updated_at automatically
     *
     * @var boolean
     */
    protected $hidden = [ 'created_at', 'updated_at' ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [ 'created_at', 'updated_at' ];

    /**
     * storeData
     * @param $arrData
     * @return array arrDiscountData
     * @since  0.1
     * @author Ravindra Kumar
     */
    public static function storeData($input, $creditUnion_id)
    {
        return self::updateOrCreate([ 'id' => (int)$creditUnion_id ], $input);
    }

    public static function getUnitClosedByID($unit_id = 0, $month = 0, $year = 0)
    {
        return self::select('*')
            ->where('unit_id', '=', $unit_id)
            ->where('month', '=', $month)
            ->where('year', '=', $year)
            ->first();
    }

    public static function getUnitClosedByAll($unit_id = 0, $month = 0, $year = 0)
    {
        return self::select('*')
            ->where('unit_all', '=', $unit_id)
            ->where('month', '=', $month)
            ->where('year', '=', $year)
            ->first();
    }
}