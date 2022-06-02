<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TaxCode extends Model
{
    public $table = "tax_codes";
    protected $primaryKey = 'tax_code_ID';
    public $timestamps = false;

    public function vendingSaleTaxCodes()
    {
        return $this->belongsToMany('App\NetExt', 'vending_sale_tax_codes');
    }

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public static function getTitlesByType(string $purchType): Collection
    {
        $dbField = $purchType . '_purch';
        $taxCodes = TaxCode::select('tax_code_display_rate', 'tax_code_ID')
            ->where($dbField, 1);
        $taxCodes = $taxCodes->orderBy('tax_code_display_rate')->get();

        $taxCodes = self::makeSortByTitles($taxCodes);

        return $taxCodes->values();
    }

    public static function getTitlesByCurrency(string $purchType, int $currency_id): Collection
    {
        $dbField = $purchType . '_purch';
        $taxCodes = TaxCode::select('tax_code_display_rate', 'tax_code_ID')
            ->where($dbField, 1);

        $taxCodes->when(!empty($request->currency_id), function ($q) use ($currency_id)
        {
            return $q->where('currency_id', $currency_id);
        });
        $taxCodes = $taxCodes->orderBy('tax_code_display_rate')->get();

        $taxCodes = self::makeSortByTitles($taxCodes);

        return $taxCodes->values();
    }

    private static function makeSortByTitles(Collection $taxCodes): Collection
    {
        return $taxCodes->sort(function ($a, $b)
        {
            if ($a->tax_code_display_rate === "0") {
                return -1;
            }
            if (floatval($a->tax_code_display_rate) == 0 && floatval($b->tax_code_display_rate) == 0) {
                return 0;
            }
            if (floatval($a->tax_code_display_rate) == 0 && floatval($b->tax_code_display_rate) > 0) {
                return 1;
            }
            if (floatval($a->tax_code_display_rate) > 0 && floatval($b->tax_code_display_rate) == 0) {
                return -1;
            }
            if (floatval($b->tax_code_display_rate) === 0) {
                return 1;
            }
            if (floatval($a->tax_code_display_rate) === floatval($b->tax_code_display_rate)) {
                return 0;
            }

            return (floatval($a->tax_code_display_rate) < floatval($b->tax_code_display_rate)) ? -1 : 1;
        });
    }
}
