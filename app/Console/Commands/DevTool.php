<?php

namespace App\Console\Commands;

use App\Menu;
use App\UserMenuLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DevTool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:tool {type}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Developer tool for run scripts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        switch ($this->argument('type')) {
            case 'dir_path':
                $this->dirPath();
                break;
            case 'fill_menu':
                $this->fillMenu();
                break;
        }
    }

    private function dirPath()
    {
        $files = DB::table('file_system')->get();
        foreach ($files as $file) {
            $dir_path = str_replace('/opt/bitnami/apache2/htdocs/file_share/', '', $file->dir_path);
            $update = DB::table('file_system')->where('id', $file->id)->update([ 'dir_path' => $dir_path ]);
            dump($update);
        }
    }

    private function fillMenu()
    {
        $items = [
            [
                'Administration',
                '/currencies',
                'Currency Management',
                'currencys.index,currencys.create,currencys.edit',
                'su-user-group' ],
            [ 'Administration', '/events', 'Events', 'events.index', 'su-user-group' ],
            [ 'Administration',
                '/exchange-rates',
                'Exchange Rate Management',
                'exchangerates.index,exchangerates.create,exchangerates.edit',
                'su-user-group' ],
            [ 'Administration',
                '/netexts',
                'Net Ext Management',
                'netexts.index,netexts.create,netexts.edit',
                'su-user-group' ],
            [ 'Administration',
                '/regions',
                'Region Management',
                'regions.index,regions.create,regions.edit',
                '' ],
            [ 'Administration',
                '/registers',
                'Register Management',
                'registers.index,registers.create,registers.edit',
                '' ],
            [ 'Administration',
                '/suppliers',
                'Supplier Management',
                'suppliers.index,suppliers.create,suppliers.edit',
                '' ],
            [ 'Administration',
                '/taxcodes',
                'Tax Code Management',
                'taxcodes.index,taxcodes.create,taxcodes.edit',
                'su-user-group', ],
            [ 'Administration', '/units', 'Unit Management', 'units.index,units.create,units.edit', '', ],
            [ 'Administration', '/users', 'User Management', 'users.index,users.create,users.edit', '', ],
            [ 'Administration',
                '/vendings',
                'Vending Management',
                'vendings.index,vendings.create,vendings.edit',
                '', ],
            [ 'Accounts',
                '/accounts/bsi-report',
                'BSI Report',
                'accountss.bsireport,accountss.bsireportgrid',
                'hq-user-group', ],
            [ 'Accounts',
                '/accounts/sage-confirm',
                'Sage Confirmation',
                'accountss.sageconfirm,accountss.sageconfirmgrid',
                'hq-user-group', ],
            [ 'Accounts',
                '/accounts/statement-check',
                'Statement Check',
                'accountss.statementcheck',
                'hq-user-group', ],
            [ 'Accounts',
                '/accounts/unit-month-end-closing',
                'Unit Month End Closing',
                'accountss.unitmonthendclosing,accountss.unitmonthendclosingconfimation',
                'hq-user-group', ],
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
                'operations-user-group', ],
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
                'operations-user-group', ],
            [ 'Sheets',
                '/sheets/phased-budget',
                'Phased Budget',
                'sheets.phasedbudget,sheets.phasedbudgetconfimation',
                'hq-user-group', ],
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
                'admin-user-group,management-user-group', ],
            [ 'Reports',
                '/reports/purchases',
                'Purchases Report',
                'reports.purchases,reports.purchasesgrid',
                '', ],
            [ 'Reports',
                '/reports/purchases-summary',
                'Purchases Summary Report',
                'reports.purchasessummar,reports.purchasessummarygrid',
                'hq-user-group', ],
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
            [ 'Files', '/files', 'Files', '', 'su-user-group', ], ];
        $weight = 10;
        foreach ($items as $item) {
            $insert = DB::table('menu')->insert([
                'section' => strtolower($item[0]),
                'link' => $item[1],
                'name' => $item[2],
                'action' => $item[3],
                'gate' => $item[4],
                'weight' => $weight,
            ]);
            $this->info($insert);
            $weight = $weight + 10;
        }
    }
}
