<?php

namespace App\Http\Controllers;

use Cookie;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;


class PurchasesDataUtils {

    public static function GetPurchasesSummaryTable($purchases, $nominalCodesArr, $currencySymbol){
        $uniqueIds = [];
        $goodsTotal = 0;
        $vatTotal = 0;
        $grossTotal = 0;

        $table = '
            <table class="table table-hover table-striped margin-bottom-0">
                <thead>
                    <tr>
                        <th class="text-center">Sheet ID</th>
                        <th class="text-center">Unit Name</th>
                        <th class="text-center">Purch Type</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Inv/Ref #</th>
                        <th class="text-center">Inv/Rcpt date</th>
                        <th class="text-center">Purch Details</th>';

        foreach ($nominalCodesArr as $ncVal) {
            $table .= '<th class="text-center">' . ucwords($ncVal['title']) . '</th>';
        }

        $table .= '
                        <th class="text-center">Total Goods</th>
                        <th class="text-center">Total VAT</th>
                        <th class="text-center">Total Gross</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($purchases as $pVal) {
            $table .= '<tr>
                <td>' . $pVal->unique_id . '</td>
                <td>' . $pVal->unit_name . '</td>
                <td>' . $pVal->purch_type . '</td>
                <td>' . $pVal->supplier . '</td>
                <td>' . $pVal->reference_invoice_number . '</td>
                <td class="text-center">' . $pVal->receipt_invoice_date . '</td>
                <td>' . $pVal->purchase_details . '</td>';

            foreach ($nominalCodesArr as $ncKey => $ncVal) {
                $table .= '<td>';
                if ($ncKey == $pVal->net_ext_ID) {
                    $table .= \Helpers::formatCurrencyAmount($currencySymbol, $pVal->goods);
                    $nominalCodesArr[$ncKey]['total'] += $pVal->goods;
                } else
                    $table .= \Helpers::formatCurrencyAmount($currencySymbol, '0.00');
                $table .= '</td>';
            }

            if (!in_array($pVal->unique_id, $uniqueIds)) {
                $uniqueIds[] = $pVal->unique_id;
                $goodsTotal += $pVal->goods_total;
                $vatTotal += $pVal->vat_total;
                $grossTotal += $pVal->gross_total;
                $table .= '<td>' . \Helpers::formatCurrencyAmount($currencySymbol, $pVal->goods_total) . '</td>
                    <td>' . \Helpers::formatCurrencyAmount($currencySymbol, $pVal->vat_total) . '</td>
                    <td>' . \Helpers::formatCurrencyAmount($currencySymbol, $pVal->gross_total) . '</td>';
            } else {
                $table .= '<td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>';
            }

            $table .= '
                </tr>';
        }

        $table .= '<tr><td colspan="7"><strong>Totals</strong></td>';
        foreach ($nominalCodesArr as $ncVal) {
            $table .= '<td><strong>' . \Helpers::formatCurrencyAmount($currencySymbol, $ncVal['total']) . '</strong></td>';
        }

        $table .= '<td><strong>' . \Helpers::formatCurrencyAmount($currencySymbol, $goodsTotal) . '</strong></td>';
        $table .= '<td><strong>' . \Helpers::formatCurrencyAmount($currencySymbol, $vatTotal) . '</strong></td>';
        $table .= '<td><strong>' . \Helpers::formatCurrencyAmount($currencySymbol, $grossTotal) . '</strong></td>';
        $table .= '</tr>';

        $table .= '
                </tbody>
            </table>
        ';

        return $table;
    }

    public static function GetNominalCodesForSummary($fromDate, $toDate, $unitIDStr, $purchaseTypeStr){
        return DB::table('nominal_codes')
        ->select('*',
            DB::raw("
                        (SELECT COUNT(*) FROM purchases p WHERE nominal_codes.net_ext_ID = p.net_ext_ID AND p.receipt_invoice_date
                        BETWEEN '$fromDate' AND '$toDate' $unitIDStr $purchaseTypeStr AND deleted = 0) AS total
                    ")
        )
        ->having('total', '>', 0)
        ->get();
    }

    public static function GetPurchasesForSummary($fromDate, $toDate, $unitId, $unitCurrency, $purchaseType){
        return DB::table('purchases AS P')
            ->select(
                'P.unique_id', 
                'P.purch_type', 
                'P.supplier', 
                'P.reference_invoice_number', 
                DB::raw('DATE_FORMAT(P.receipt_invoice_date,"%d-%m-%Y") receipt_invoice_date'), 
                'P.purchase_details', 
                'P.net_ext_ID', 
                DB::raw('P.goods * er.exchange_rate as goods'),
                DB::raw('P.goods_total * er.exchange_rate as goods_total'),
                DB::raw('P.vat_total * er.exchange_rate as vat_total'),
                DB::raw('P.gross_total * er.exchange_rate as gross_total'),
                'UN.unit_name')
            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
            {
                $join->on('er.domestic_currency_id', 'P.currency_id')
                    ->where('er.foreign_currency_id', $unitCurrency)
                    ->whereRaw('er.date = P.receipt_invoice_date');
            })
            ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
            ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
            ->when($unitId, function ($query) use ($unitId)
            {
                return $query->where('P.unit_id', $unitId);
            })
            ->when(isset($purchaseType) && $purchaseType != 'both', function ($query) use ($purchaseType)
            {
                return $query->where('purch_type', $purchaseType);
            })
            ->where('P.deleted', 0)
            ->get();
    }
    
    public static function GetPurchasesOld($fromDate, $toDate, $unitId, $unitCurrency, $userUnits, $all_records){
        $purchases = '';

        if ($unitId == '' && Gate::allows('su-user-group')) {
            if ($all_records) {
                $purchases = DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.deleted', 0);
            } else {
                $purchases = DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                    ->where('P.deleted', 0);
            }
        } elseif ($unitId == '' && Gate::allows('hq-user-group')) {
            if ($all_records) {
                $purchases = DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.deleted', 0);
            } else {
                $purchases = DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                    ->where('P.deleted', 0);
            }
        } elseif ($unitId == '' && Gate::allows('operations-user-group')) {
            $purchases = DB::table('purchases AS P')
                ->select(
                    [
                        'P.purchase_id',
                        'P.unique_id',
                        'P.purch_type',
                        'UN.unit_name',
                        'P.supplier',
                        'P.supervisor',
                        'P.reference_invoice_number',
                        'P.receipt_invoice_date',
                        'P.purchase_details',
                        'N.net_ext',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'P.goods',
                        'P.vat',
                        'P.gross',
                        'TC.tax_rate',
                        'TC.tax_code_display_rate',
                        'P.goods_total',
                        'P.vat_total',
                        'P.gross_total',
                        'P.record_status',
                        'P.time_inserted',
                        'P.time_updated',
                        'UU.username as updated_by',
                        'P.stmt_ok',
                        'P.stmnt_chk',
                        'U.username as stmnt_chk_user',
                        'P.date_stmnt_chk',
                        'P.purchase_id',
                    ]
                )
                ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'P.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = P.date');
                })
                ->whereIn('P.unit_id', $userUnits)
                ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                ->where('P.deleted', 0);
        } elseif ($unitId == '' && Gate::allows('unit-user-group')) {
            $purchases = DB::table('purchases AS P')
                ->select(
                    [
                        'P.purchase_id',
                        'P.unique_id',
                        'P.purch_type',
                        'UN.unit_name',
                        'P.supplier',
                        'P.supervisor',
                        'P.reference_invoice_number',
                        'P.receipt_invoice_date',
                        'P.purchase_details',
                        'N.net_ext',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'P.goods',
                        'P.vat',
                        'P.gross',
                        'TC.tax_rate',
                        'TC.tax_code_display_rate',
                        'P.goods_total',
                        'P.vat_total',
                        'P.gross_total',
                        'P.record_status',
                        'P.time_inserted',
                        'P.time_updated',
                        'UU.username as updated_by',
                        'P.stmt_ok',
                        'P.stmnt_chk',
                        'U.username as stmnt_chk_user',
                        'P.date_stmnt_chk',
                        'P.purchase_id',
                    ]
                )
                ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'P.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = P.date');
                })
                ->whereIn('P.unit_id', $userUnits)
                ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                ->where('P.deleted', 0);
        } elseif ($unitId != '' && Gate::allows('su-user-group')) {
            if ($all_records) {
                $purchases = DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.unit_id', $unitId)
                    ->where('P.deleted', 0);
            } else {
                $purchases = DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.foreign_currency_id', '=', 'P.currency_id')
                            ->where('ER.domestic_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.unit_id', $unitId)
                    ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                    ->where('P.deleted', 0);
            }
        } elseif ($unitId != '' && Gate::allows('hq-user-group')) {
            if ($all_records) {
                $purchases = DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.unit_id', $unitId)
                    ->where('P.deleted', 0);
            } else {
                $purchases = DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.unit_id', $unitId)
                    ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                    ->where('P.deleted', 0);
            }
        } else {
            $purchases = DB::table('purchases AS P')
                ->select(
                    [
                        'P.purchase_id',
                        'P.unique_id',
                        'P.purch_type',
                        'UN.unit_name',
                        'P.supplier',
                        'P.supervisor',
                        'P.reference_invoice_number',
                        'P.receipt_invoice_date',
                        'P.purchase_details',
                        'N.net_ext',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'P.goods',
                        'P.vat',
                        'P.gross',
                        'TC.tax_rate',
                        'TC.tax_code_display_rate',
                        'P.goods_total',
                        'P.vat_total',
                        'P.gross_total',
                        'P.record_status',
                        'P.time_inserted',
                        'P.time_updated',
                        'UU.username as updated_by',
                        'P.stmt_ok',
                        'P.stmnt_chk',
                        'U.username as stmnt_chk_user',
                        'P.date_stmnt_chk',
                        'P.purchase_id',
                    ]
                )
                ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'P.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = P.date');
                })
                ->where('P.unit_id', $unitId)
                ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                ->where('P.deleted', 0);
        }

        return $purchases;
    }

    public static function GetPurchases($fromDate, $toDate, $unitId, $unitCurrency, $userUnits, $all_records){
        $purchases = DB::table('purchases AS P')
            ->select(
                [
                    'P.purchase_id',
                    'P.purchase_id',
                    'P.unique_id',
                    'P.purch_type',
                    'UN.unit_name',
                    'P.supplier',
                    'P.supervisor',
                    'P.reference_invoice_number',
                    'P.receipt_invoice_date',
                    'P.purchase_details',
                    'N.net_ext',
                    'C.currency_code',
                    'ER.exchange_rate',
                    'P.goods',
                    'P.vat',
                    'P.gross',
                    'TC.tax_rate',
                    'TC.tax_code_display_rate',
                    'P.goods_total',
                    'P.vat_total',
                    'P.gross_total',
                    'P.record_status',
                    'P.time_inserted',
                    'P.time_updated',
                    'UU.username as updated_by',
                    'P.stmt_ok',
                    'P.stmnt_chk',
                    'U.username as stmnt_chk_user',
                    'P.date_stmnt_chk',
                    'P.purchase_id',
                ]
            )
            ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
            ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
            ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
            ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
            ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
            ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
            ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
            {
                $join->on('ER.domestic_currency_id', 'P.currency_id')
                    ->where('ER.foreign_currency_id', $unitCurrency)
                    ->whereRaw('ER.date = P.date');
            })
            ->where('P.deleted', 0);

        if (Gate::allows('su-user-group') || Gate::allows('hq-user-group')) {
            if (!$all_records) {
                $purchases = $purchases->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ]);
            } 

            if($unitId != '' ){
                $purchases = $purchases->where('P.unit_id', $unitId);
            }
        } elseif ($unitId == '' && ((Gate::allows('operations-user-group')  || Gate::allows('unit-user-group')))) {
            $purchases = $purchases
                ->whereIn('P.unit_id', $userUnits)
                ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ]);

        } else {
            $purchases = $purchases
                ->where('P.unit_id', $unitId)
                ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ]);
        }

        return $purchases;
    }

    public static function GetPurchasesDataTables($purchases) {
        if (Gate::allows('su-user-group')) {
            return Datatables::of($purchases)
                ->setRowId(function ($purchase)
                {
                    return 'tr_' . $purchase->purchase_id;
                })
                ->setRowClass(function ($purchase)
                {
                    if ($purchase->stmt_ok == 1) {
                        return 'orange-row';
                    }

                    if ($purchase->stmnt_chk == 1) {
                        return 'green-row';
                    }

                    if ($purchase->exchange_rate != 1) {
                        return 'blue-row';
                    }

                    return '';
                })
                ->addColumn('checkbox', function ($purchase)
                {
                    return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $purchase->purchase_id . '">';
                }, 0)
                ->addColumn('action', function ($purchase)
                {
                    $purchasesData = DB::table('purchases')
                        ->select('stmt_ok', 'stmnt_chk')
                        ->where('purchase_id', $purchase->purchase_id)
                        ->first();
                    if ($purchasesData->stmt_ok == 0 && $purchasesData->stmnt_chk == 0) {
                        return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                    } else {
                        return '-';
                    }
                })
                ->editColumn('unique_id', function ($purchase)
                {
                    if ($purchase->record_status == 'Frozen') {
                        return '<a  href="javascript:void(0);">' . $purchase->unique_id . '</a>';
                    } else {
                        return '<a target="_blank" href="/sheets/purchases/' . $purchase->purch_type . '/' . $purchase->unique_id . '">' . $purchase->unique_id . '</a>';
                    }
                })
                ->editColumn('receipt_invoice_date', function ($purchase)
                {
                    return $purchase->receipt_invoice_date ? with(new Carbon($purchase->receipt_invoice_date))->format('d-m-Y') : '';
                })
                ->editColumn('date_stmnt_chk', function ($purchase)
                {
                    return $purchase->date_stmnt_chk != '0000-00-00 00:00:00' ? Carbon::parse($purchase->date_stmnt_chk)
                        ->format('d-m-Y  H:i:s') : '';
                })
                ->editColumn('time_inserted', function ($purchase)
                {
                    return Carbon::parse($purchase->time_inserted)->format('d-m-Y H:i:s');
                })
                ->editColumn('time_updated', function ($purchase)
                {
                    return !is_null($purchase->time_updated) ? Carbon::parse($purchase->time_updated)
                        ->format('d-m-Y H:i:s') : '';
                })
                ->editColumn('goods', function ($purchase)
                {
                    return $purchase->goods * $purchase->exchange_rate;
                })
                ->editColumn('vat', function ($purchase)
                {
                    return $purchase->vat * $purchase->exchange_rate;
                })
                ->editColumn('gross', function ($purchase)
                {
                    return $purchase->gross * $purchase->exchange_rate;
                })
                ->editColumn('goods_total', function ($purchase)
                {
                    return $purchase->goods_total * $purchase->exchange_rate;
                })
                ->editColumn('vat_total', function ($purchase)
                {
                    return $purchase->vat_total * $purchase->exchange_rate;
                })
                ->editColumn('gross_total', function ($purchase)
                {
                    return $purchase->gross_total * $purchase->exchange_rate;
                })
                ->filterColumn('P.receipt_invoice_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(receipt_invoice_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.date_stmnt_chk', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.date_stmnt_chk,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_inserted', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_inserted,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_updated', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_updated,'%d-%m-%Y  %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('record_status', function ($purchase)
                {
                    $unitId = Cookie::get('purchasesReportUnitIdCookie') != '' ? Cookie::get('purchasesReportUnitIdCookie') : 'All';
                    $fromDate = Cookie::get('purchasesReportFromDateCookie');
                    $toDate = Cookie::get('purchasesReportToDateCookie');

                    if ($purchase->stmt_ok == 0 && $purchase->stmnt_chk == 0) {
                        return $purchase->record_status;
                    } else {
                        return '<a onclick="return confirm_before_unfreeze()" href="/reports/purchases/grid/' . $purchase->unique_id . '/' . $unitId . '/' . $fromDate . '/' . $toDate . '">' . $purchase->record_status . '</a>';
                    }
                })
                ->make();
        } elseif (Gate::allows('hq-user-group')) {
            return Datatables::of($purchases)
                ->setRowId(function ($purchase)
                {
                    return 'tr_' . $purchase->purchase_id;
                })
                ->setRowClass(function ($purchase)
                {
                    if ($purchase->stmt_ok == 1) {
                        return 'orange-row';
                    }

                    if ($purchase->stmnt_chk == 1) {
                        return 'green-row';
                    }

                    if ($purchase->exchange_rate != 1) {
                        return 'blue-row';
                    }

                    return '';
                })
                ->addColumn('action', function ($purchase)
                {
                    $purchasesData = DB::table('purchases')
                        ->select('stmt_ok', 'stmnt_chk')
                        ->where('purchase_id', $purchase->purchase_id)
                        ->first();
                    if ($purchasesData->stmt_ok == 0 && $purchasesData->stmnt_chk == 0) {
                        return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                    } else {
                        return '-';
                    }
                })
                ->editColumn('unique_id', function ($purchase)
                {
                    if ($purchase->record_status == 'Frozen') {
                        return '<a  href="javascript:void(0);">' . $purchase->unique_id . '</a>';
                    } else {
                        return '<a target="_blank" href="/sheets/purchases/' . $purchase->purch_type . '/' . $purchase->unique_id . '">' . $purchase->unique_id . '</a>';
                    }
                })
                ->editColumn('receipt_invoice_date', function ($purchase)
                {
                    return $purchase->receipt_invoice_date ? with(new Carbon($purchase->receipt_invoice_date))->format('d-m-Y') : '';
                })
                ->editColumn('date_stmnt_chk', function ($purchase)
                {
                    return $purchase->date_stmnt_chk != '0000-00-00 00:00:00' ? Carbon::parse($purchase->date_stmnt_chk)
                        ->format('d-m-Y H:i:s') : '';
                })
                ->editColumn('time_inserted', function ($purchase)
                {
                    return Carbon::parse($purchase->time_inserted)->format('d-m-Y H:i:s');
                })
                ->editColumn('time_updated', function ($purchase)
                {
                    return !is_null($purchase->time_updated) ? Carbon::parse($purchase->time_updated)
                        ->format('d-m-Y H:i:s') : '';
                })
                ->editColumn('goods', function ($purchase)
                {
                    return $purchase->goods * $purchase->exchange_rate;
                })
                ->editColumn('vat', function ($purchase)
                {
                    return $purchase->vat * $purchase->exchange_rate;
                })
                ->editColumn('gross', function ($purchase)
                {
                    return $purchase->gross * $purchase->exchange_rate;
                })
                ->editColumn('goods_total', function ($purchase)
                {
                    return $purchase->goods_total * $purchase->exchange_rate;
                })
                ->editColumn('vat_total', function ($purchase)
                {
                    return $purchase->vat_total * $purchase->exchange_rate;
                })
                ->editColumn('gross_total', function ($purchase)
                {
                    return $purchase->gross_total * $purchase->exchange_rate;
                })
                ->filterColumn('P.receipt_invoice_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(receipt_invoice_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.date_stmnt_chk', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.date_stmnt_chk,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_inserted', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_inserted,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_updated', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_updated,'%d-%m-%Y  %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->make();
        } else {
            return Datatables::of($purchases)
                ->setRowId(function ($purchase)
                {
                    return 'tr_' . $purchase->purchase_id;
                })
                ->setRowClass(function ($purchase)
                {
                    if ($purchase->stmt_ok == 1) {
                        return 'orange-row';
                    }

                    if ($purchase->stmnt_chk == 1) {
                        return 'green-row';
                    }

                    if ($purchase->exchange_rate != 1) {
                        return 'blue-row';
                    }

                    return '';
                })
                ->editColumn('stmnt_chk_user', function ($purchase)
                {
                    return '';
                })
                ->editColumn('date_stmnt_chk', function ($purchase)
                {
                    return '';
                })
                ->addColumn('action', function ($purchase)
                {
                    $purchasesData = DB::table('purchases')
                        ->select('stmt_ok', 'stmnt_chk')
                        ->where('purchase_id', $purchase->purchase_id)
                        ->first();
                    if ($purchasesData->stmt_ok == 0 && $purchasesData->stmnt_chk == 0) {
                        return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                    } else {
                        return '-';
                    }
                })
                ->editColumn('unique_id', function ($purchase)
                {
                    if ($purchase->record_status == 'Frozen') {
                        return '<a  href="javascript:void(0);">' . $purchase->unique_id . '</a>';
                    } else {
                        return '<a target="_blank" href="/sheets/purchases/' . $purchase->purch_type . '/' . $purchase->unique_id . '">' . $purchase->unique_id . '</a>';
                    }
                })
                ->editColumn('receipt_invoice_date', function ($purchase)
                {
                    return $purchase->receipt_invoice_date ? with(new Carbon($purchase->receipt_invoice_date))->format('d-m-Y') : '';
                })
                ->editColumn('time_inserted', function ($purchase)
                {
                    return Carbon::parse($purchase->time_inserted)->format('d-m-Y H:i:s');
                })
                ->editColumn('time_updated', function ($purchase)
                {
                    return !is_null($purchase->time_updated) ? Carbon::parse($purchase->time_updated)
                        ->format('d-m-Y H:i:s') : '';
                })
                ->editColumn('goods', function ($purchase)
                {
                    return $purchase->goods * $purchase->exchange_rate;
                })
                ->editColumn('vat', function ($purchase)
                {
                    return $purchase->vat * $purchase->exchange_rate;
                })
                ->editColumn('gross', function ($purchase)
                {
                    return $purchase->gross * $purchase->exchange_rate;
                })
                ->editColumn('goods_total', function ($purchase)
                {
                    return $purchase->goods_total * $purchase->exchange_rate;
                })
                ->editColumn('vat_total', function ($purchase)
                {
                    return $purchase->vat_total * $purchase->exchange_rate;
                })
                ->editColumn('gross_total', function ($purchase)
                {
                    return $purchase->gross_total * $purchase->exchange_rate;
                })
                ->filterColumn('P.receipt_invoice_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.receipt_invoice_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_inserted', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_inserted,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_updated', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_updated,'%d-%m-%Y  %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->make();
        }
    }
}