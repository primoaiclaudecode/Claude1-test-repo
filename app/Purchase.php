<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
	protected $primaryKey = 'purchase_id';
	public $timestamps = false;

	public static function getPurchasesSales($month = 0, $year = 0, $unit_id = 0)
	{
		$purchasesData = \DB::select(
			"SELECT SUM(goods_total) AS total_goods, SUM(vat_total) AS total_vat, SUM(gross_total) AS total_gross
              FROM `purchases`
              WHERE EXTRACT( MONTH FROM receipt_invoice_date ) = '" . $month . "'
              AND EXTRACT( YEAR FROM receipt_invoice_date ) = '" . $year . "'
              AND unit_id='" . $unit_id . "' GROUP BY unique_id"
		);
		return ($purchasesData ? $purchasesData : []);
	}

	public static function getCashSales($month = 0, $year = 0, $unit_id = 0)
	{
		$cashSalesData = \DB::select(
			"SELECT SUM(cash_credit_card) AS cash_credit_card
              FROM `cash_sales`
              WHERE EXTRACT( MONTH FROM sale_date ) = '" . $month . "'
              AND EXTRACT( YEAR FROM sale_date ) = '" . $year . "'
              AND unit_id='" . $unit_id . "'"
		);
		return ($cashSalesData ? $cashSalesData : []);
	}

	public static function getCreditSales($month = 0, $year = 0, $unit_id = 0)
	{
		$creditSalesData = \DB::select(
			"SELECT SUM( goods_total ) AS goods_total
              FROM `credit_sales`
              WHERE EXTRACT( MONTH FROM sale_date ) = '" . $month . "'
              AND EXTRACT( YEAR FROM sale_date ) = '" . $year . "'
              AND unit_id='" . $unit_id . "'"
		);
		return ($creditSalesData ? $creditSalesData : []);
	}

	public static function getVendingSales($month = 0, $year = 0, $unit_id = 0)
	{
		$vendingSalesData = \DB::select(
			"SELECT SUM( total ) AS total
              FROM `vending_sales`
              WHERE EXTRACT( MONTH FROM sale_date ) = '" . $month . "'
              AND EXTRACT( YEAR FROM sale_date ) = '" . $year . "'
              AND unit_id='" . $unit_id . "'"
		);
		return ($vendingSalesData ? $vendingSalesData : []);
	}
}
