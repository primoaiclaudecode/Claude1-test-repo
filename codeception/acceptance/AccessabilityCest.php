<?php
namespace codeception;
use codeception\AcceptanceTester;
class AccessabilityCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function accessSuperUserTest(AcceptanceTester $I)
    {
        $I->authSU($I);
        foreach ($this->access as $item){
            $I->amGoingTo('Test access to '.$item[1]);
            $I->amOnPage($item[1]);
            if(intval($item[4]) > 5 ){
                $I->seeResponseCodeIs(403);
            } else {
                $I->seeResponseCodeIs(200);
            }
        }
    }
    public function accessAdminUserTest(AcceptanceTester $I)
    {
        $I->authAdmin($I);
        foreach ($this->access as $item){
            $I->amGoingTo('Test access to '.$item[1]);
            $I->amOnPage($item[1]);
            if(intval($item[4]) > 4 ){
                $I->seeResponseCodeIs(403);
            } else {
                $I->seeResponseCodeIs(200);
            }
        }
    }
    public function accessHeadQuarterUserTest(AcceptanceTester $I)
    {
        $I->authHQ($I);
        foreach ($this->access as $item){
            $I->amGoingTo('Test access to '.$item[1]);
            $I->amOnPage($item[1]);
            if(intval($item[4]) > 3 ){
                $I->seeResponseCodeIs(403);
            } else {
                $I->seeResponseCodeIs(200);
            }
        }
    }
    public function accessOperationsUserTest(AcceptanceTester $I)
    {
        $I->authOps($I);
        foreach ($this->access as $item){
            $I->amGoingTo('Test access to '.$item[1]);
            $I->amOnPage($item[1]);
            if(intval($item[4]) > 2 ){
                $I->seeResponseCodeIs(403);
            } else {
                $I->seeResponseCodeIs(200);
            }
        }
    }
    public function accessUnitUserTest(AcceptanceTester $I)
    {
        $I->authUnit($I);
        foreach ($this->access as $item){
            $I->amGoingTo('Test access to '.$item[1]);
            $I->amOnPage($item[1]);
            if(intval($item[4]) > 1 ){
                $I->seeResponseCodeIs(403);
            } else {
                $I->seeResponseCodeIs(200);
            }
        }
    }
    
    private $access = [
        [
            'Administration',
            '/currencies',
            'Currency Management',
            'currencys.index,currencys.create,currencys.edit',
            '5'
        ],
        [ 'Administration', '/events', 'Events', 'events.index', '5' ],
        [ 'Administration',
            '/exchange-rates',
            'Exchange Rate Management',
            'exchangerates.index,exchangerates.create,exchangerates.edit',
            '5' ],
        [ 'Administration',
            '/netexts',
            'Net Ext Management',
            'netexts.index,netexts.create,netexts.edit',
            '5' ],
        [ 'Administration',
            '/regions',
            'Region Management',
            'regions.index,regions.create,regions.edit',
            '4' ],
        [ 'Administration',
            '/registers',
            'Register Management',
            'registers.index,registers.create,registers.edit',
            '4' ],
        [ 'Administration',
            '/suppliers',
            'Supplier Management',
            'suppliers.index,suppliers.create,suppliers.edit',
            '4' ],
        [ 'Administration',
            '/taxcodes',
            'Tax Code Management',
            'taxcodes.index,taxcodes.create,taxcodes.edit',
            '5', ],
        [ 'Administration', '/units', 'Unit Management', 'units.index,units.create,units.edit', '4', ],
        [ 'Administration', '/users', 'User Management', 'users.index,users.create,users.edit', '4', ],
        [ 'Administration',
            '/vendings',
            'Vending Management',
            'vendings.index,vendings.create,vendings.edit',
            '4', ],
        [ 'Accounts',
            '/accounts/bsi-report',
            'BSI Report',
            'accountss.bsireport,accountss.bsireportgrid',
            '3', ],
        [ 'Accounts',
            '/accounts/sage-confirm',
            'Sage Confirmation',
            'accountss.sageconfirm,accountss.sageconfirmgrid',
            '3', ],
        [ 'Accounts',
            '/accounts/statement-check',
            'Statement Check',
            'accountss.statementcheck',
            '3', ],
        [ 'Accounts',
            '/accounts/unit-month-end-closing',
            'Unit Month End Closing',
            'accountss.unitmonthendclosing,accountss.unitmonthendclosingconfimation',
            '3', ],
        [ 'Sheets',
            '/sheets/purchases/cash',
            'Cash Purchases',
            'sheets.purchase,sheets.purchaseconfirmation',
            '', ],
        [ 'Sheets',
            '/sheets/cash-sales',
            'Cash Sales',
            'sheets.cashsales,sheets.cashsalesconfimation',
            '', ],
        [ 'Sheets',
            '/sheets/customer-feedback',
            'Client Feedback',
            'sheets.customerfeedback,sheets.customerfeedbackconfirmation',
            '2', ],
        [ 'Sheets',
            '/sheets/problem-report',
            'Corrective Action Report',
            'sheets.problemreport,sheets.problemreportconfimation',
            '', ],
        [ 'Sheets',
            '/sheets/purchases/credit',
            'Credit Purchases',
            'sheets.purchase,sheets.purchaseconfirmation',
            '', ],
        [ 'Sheets',
            '/sheets/credit-sales',
            'Credit Sales',
            'sheets.creditsales,sheets.creditsalesconfirmation',
            '', ],
        [ 'Sheets',
            '/sheets/labour-hours',
            'Labour Hours',
            'sheets.labourhours,sheets.labourhoursconfimation',
            '', ],
        [ 'Sheets',
            '/sheets/lodgements',
            'Lodgements',
            'sheets.lodgements,sheets.lodgementsconfirmation',
            '', ],
        [ 'Sheets',
            '/sheets/operations-scorecard',
            'Operations Scorecard',
            'sheets.operationsscorecard,sheets.operationsscorecardconfirmation,sheets.operationsscorecardedit',
            '2', ],
        [ 'Sheets',
            '/sheets/phased-budget',
            'Phased Budget',
            'sheets.phasedbudget,sheets.phasedbudgetconfimation',
            '3', ],
        [ 'Sheets',
            '/sheets/stock-control',
            'Stock Control',
            'sheets.stockcontrol,sheets.stockcontrolconfimation',
            '', ],
        [ 'Sheets',
            '/sheets/vending-sales',
            'Vending Sales',
            'sheets.vendingsales,sheets.vendingsalesconfirmation',
            '', ],
        [ 'Reports',
            '/reports/cash-sales',
            'Cash Sales Report',
            'reports.cashsales,reports.cashsalesgrid',
            '', ],
        [ 'Reports',
            '/reports/client-feedback',
            'Client Feedback Report',
            'reports.clientfeedback,reports.clientfeedbackgrid',
            '', ],
        [ 'Reports',
            '/reports/problem-report',
            'Corrective Action Report',
            'reports.problemreport,reports.problemreportgrid',
            '', ],
        [ 'Reports',
            '/reports/credit-sales',
            'Credit Sales Report',
            'reports.creditsales,reports.creditsalesgrid',
            '', ],
        [ 'Reports',
            '/reports/labour-hours',
            'Labour Hours Report',
            'reports.labourhours,reports.labourhoursgrid',
            '', ],
        [ 'Reports',
            '/reports/lodgements',
            'Lodgements Report',
            'reports.lodgements,reports.lodgementsgrid',
            '', ],
        [ 'Reports',
            '/reports/operations-scorecard',
            'Operations Scorecard Report',
            'reports.operationsscorecard,reports.operationsscorecardgrid',
            '5', ],
        [ 'Reports',
            '/reports/purchases',
            'Purchases Report',
            'reports.purchases,reports.purchasesgrid',
            '', ],
        [ 'Reports',
            '/reports/purchases-summary',
            'Purchases Summary Report',
            'reports.purchasessummar,reports.purchasessummarygrid',
            '3', ],
        [ 'Reports',
            '/reports/sales-summary',
            'Sales Summary Report',
            'reports.salessummary,reports.salessummarygrid',
            '', ],
        [ 'Reports',
            '/reports/stock-control',
            'Stock Control Report',
            'reports.stockcontrol,reports.stockcontrolgri',
            '', ],
        [ 'Reports',
            '/reports/unit-trading-account',
            'UTA Report',
            'reports.unittradingaccount,reports.postunittradingaccount',
            '', ],
        [ 'Reports',
            '/reports/unit-trading-account-stock',
            'UTA + Stock Report',
            'reports.unittradingaccountstock,reports.postunittradingaccountstock',
            '', ],
        [ 'Reports',
            '/reports/vending-sales',
            'Vending Sales Report',
            'reports.vendingsales,reports.vendingsalesgrid',
            '', ],
        [ 'Files', '/files', 'Files', '', '5', ], ];
}
