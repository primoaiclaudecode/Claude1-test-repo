<?php

/**
 * Using of this model is deprecated.
 *
 * Use ReportHiddenColumn instead.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportColumnVisible extends Model
{
    public $table = "report_column_visible";
    public static $purchasesColumns = [
        'ID' => 0,
        'Sheet ID' => 1,
        'Purch Type' => 2,
        'Unit Name' => 3,
        'Supplier' => 4,
        'Supervisor' => 5,
        'Inv / Ref #' => 6,
        'Inv / Rec Date' => 7,
        'Purchase Details' => 8,
        'Net Ext' => 9,
        'Goods' => 10,
        'VAT' => 11,
        'Gross' => 12,
        'Tax Title' => 13,
        'Tax Rate' => 14,
        'Goods Total' => 15,
        'VAT Total' => 16,
        'Gross Total' => 17,
        'Status' => 18,
        'Created on' => 19,
        'Last modified' => 20,
        'Last Updated by' => 21,
        'Stmt_ok' => 22,
        'Stmnt_chk' => 23,
        'Stmnt_chk_user' => 24,
        'Stmnt_chk_date' => 25,
        'Action' => 26,
    ];
    public static $cashColumns = [
        'ID' => 0,
        'Date of Entry' => 1,
        'Unit Name' => 2,
        'Supervisor' => 3,
        'Reg Number' => 4,
        'Sale Date' => 5,
        'Z Number' => 6,
        'Z Food' => 7,
        'Z Conf. Food' => 8,
        'Z Fruit Juice' => 9,
        'Z Minerals' => 10,
        'Z Confectionary' => 11,
        'Cash Count' => 12,
        'Credit Card' => 13,
        'Staff Card' => 14,
        'Total Receipts' => 15,
        'Z Read' => 16,
        'Variance' => 17,
        'Cash Purchase' => 18,
        'Credit Sale' => 19,
        'Over Ring' => 20,
        'Cash +/-' => 21,
        'Lodgement Cash' => 22,
        'Lodgement Coin' => 23,
        'Lodgement Total' => 24,
        'Lodgement Date' => 25,
        'Lodgement Number' => 26,
        'G4S Bag #' => 27,
        'Remarks' => 28,
        'Updated By' => 29,
        'Updated' => 30,
        'Action' => 31,
    ];
    public static $creditColumns = [
        'ID' => 0,
        'Date of Entry' => 1,
        'Unit Name' => 2,
        'Supervisor' => 3,
        'Docket Number' => 4,
        'Sale Date' => 5,
        'Credit Reference' => 6,
        'Cost Centre' => 7,
        'Goods 13.5%' => 8,
        'VAT 13.5%' => 9,
        'Gross 13.5%' => 10,
        'Goods 21%' => 11,
        'VAT 21%' => 12,
        'Gross 21%' => 13,
        'Goods 23%' => 14,
        'VAT 23%' => 15,
        'Gross 23%' => 16,
        'Total Goods' => 17,
        'Total VAT' => 18,
        'Total Gross' => 19,
        'Visible' => 20,
        'Vis By' => 21,
        'Date Vis' => 22,
        'Action' => 23,
    ];
    public static $vendingColumns = [
        'ID' => 0,
        'Date of Entry' => 1,
        'Unit Name' => 2,
        'Supervisor' => 3,
        'Sale Date' => 4,
        'Machine Name' => 5,
        'Opening' => 6,
        'Closing' => 7,
        'Reg Number' => 8,
        'Z Read' => 9,
        'Cash' => 10,
        'Food' => 11,
        'Confectionary' => 12,
        'Minerals' => 13,
        'Total' => 14,
        'Action' => 15,
    ];
    public static $labourHoursColumns = [
        'ID' => 0,
        'Sheet ID' => 1,
        'Unit Name' => 2,
        'Supervisor' => 3,
        'Labour Hours' => 4,
        'Labour Date' => 5,
        'Labour Type' => 6,
        'Action' => 7,
    ];
    public static $stockControlColumns = [
        'ID' => 0,
        'Unit Name' => 1,
        'User' => 2,
        'Stock Date' => 3,
        'Foods' => 4,
        'Min./Alc.' => 5,
        'Snacks' => 6,
        'Vending' => 7,
        'Prev. Food/Min. Total' => 8,
        'Food/Min. Total' => 9,
        'Food/Min. +/-' => 10,
        'Chemicals' => 11,
        'Disposables' => 12,
        'Free Issues' => 13,
        'Prev. Chem/Disp./F.I.' => 14,
        'Chem/Disp./F.I.' => 15,
        'Chem/Disp./F.I. +/-' => 16,
        'Prev. Overall Total' => 17,
        'Overall Total' => 18,
        'Overall Total +/-' => 19,
        'Comments' => 20,
        'Action' => 21,
    ];
    public static $problemColumns = [
        'CAR #' => 0,
        'CAR Status' => 1,
        'Date' => 2,
        'User' => 3,
        'Unit' => 4,
        'Problem Type' => 5,
        'Suppliers / Feedback' => 6,
        'Details' => 7,
        'RCA' => 8,
        'RCA Desc' => 9,
        'RCA Action' => 10,
        'Closing Comments' => 11,
        'Closed By' => 12,
        'Closed Date' => 13,
        'Action' => 14,
    ];
    public static $operationsScorecardColumns = [
        'Unit Name' => 0,
        'Status' => 1,
        'Contract type' => 2,
        'Operations Manager' => 3,
        'Region' => 4,
        'Aggregate score' => 5,
        'Ops scorecards reports' => 6,
        'Client contacts' => 7,
        'Customer Feedback' => 8,
        'Budget Performance' => 9,
        'Food Offer / Presentation' => 10,
        'Food Costings / account awareness / GP' => 11,
        'HR Issues' => 12,
        'Staff Morale' => 13,
        'Purchasing Compliance, Check CARS on SAM against deliveries' => 14,
        'Haccp - Check records for compliance' => 15,
        'H&S Compliance / ISO Audit completed' => 16,
        'Accidents / Incidents on site' => 17,
        'Site security and cash control' => 18,
        'Marketing / Evidence of upselling / promotions' => 19,
        'Training' => 20,
        'GDPR on site' => 21,
        'Objectives for month' => 22,
        'Issues Outstanding' => 23,
        'Special Projects / Functions' => 24,
        'Innovation / chefs What\'s App Group' => 25,
        'Additional support required' => 26,
    ];
    public static $lodgementsColumns = [
        'ID' => 0,
        'Date' => 1,
        'Unit Name' => 2,
        'Supervisor' => 3,
        'Slip Number' => 4,
        'G4S Bag Number' => 5,
        'Cash' => 6,
        'Coin' => 7,
        'Action' => 8,
    ];
}
