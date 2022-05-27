<?php


class Helpers
{
    public static function getControllerName()
    {
        $action = app('request')->route()->getAction();
        $controller = class_basename($action['controller']);
        $exp_controller = explode('@', $controller);

        return $exp_controller[0];
    }

    public static function getActionName()
    {
        $action = app('request')->route()->getAction();
        $controller = class_basename($action['controller']);
        $exp_controller = explode('@', $controller);

        return strtolower(str_replace('Controller', '', $exp_controller[0]) . 's.' . $exp_controller[1]);
    }

    public static function formatEuroAmounts($num)
    {
        return "&euro;" . number_format($num, 2);
    }

    public static function formatEuroAmountsForCSV($num)
    {
        return "€" . number_format($num, 2);
    }

    public static function rootDirNames()
    {
        $userId = auth()->user()->user_id;

        $groupId = DB::table('users')
            ->select('user_group_member')
            ->where('user_id', '=', $userId)
            ->value('user_group_member');

        if (strpos($groupId, ',') !== false) {
            $groupIdExplode = explode(',', $groupId);
            $checkGroupPermission = '';

            foreach ($groupIdExplode as $grpId) {
                $checkGroupPermission .= 'FIND_IN_SET(' . $grpId . ',group_id_read) > 0 OR ';
            }

            $checkGroupPermission = rtrim($checkGroupPermission, 'OR ');
        } else {
            if ($groupId == '') {
                $groupId = 0;
            }

            $checkGroupPermission = 'FIND_IN_SET(' . $groupId . ',group_id_read) > 0';
        }

        if (Gate::allows('su-user-group')) {
            $files = \DB::select("SELECT id, dir_file_name FROM file_system where parent_dir_id = 0 AND is_dir = 1 ORDER BY dir_file_name");
        } else {
            $files = \DB::select("SELECT id, dir_file_name FROM file_system where parent_dir_id = 0 AND is_dir = 1 AND (FIND_IN_SET($userId,user_id_read) > 0 OR $checkGroupPermission) ORDER BY dir_file_name");
        }

        return $files;
    }

    public static function menuLinkTitles()
    {
        $linkTitles = [
            '/dashboard'                          => 'Dashboard',
            '/events'                             => 'Events',
            '/netexts'                            => 'Net Ext Management',
            '/regions'                            => 'Region Management',
            '/registers'                          => 'Register Management',
            '/suppliers'                          => 'Supplier Management',
            '/taxcodes'                           => 'Tax Code Management',
            '/units'                              => 'Unit Management',
            '/users'                              => 'User Management',
            '/vendings'                           => 'Vending Management',
            '/accounts/bsi-report'                => 'BSI Report',
            '/accounts/sage-confirm'              => 'Sage Confirmation',
            '/accounts/statement-check'           => 'Statement Check',
            '/accounts/unit-month-end-closing'    => 'Unit Month End Closing',
            '/sheets/purchases/cash'              => 'Cash Purchases',
            '/sheets/cash-sales'                  => 'Cash Sales',
            '/sheets/customer-feedback'           => 'Client Feedback',
            '/sheets/problem-report'              => 'Corrective Action Report',
            '/sheets/purchases/credit'            => 'Credit Purchases',
            '/sheets/credit-sales'                => 'Credit Sales',
            '/sheets/labour-hours'                => 'Labour Hours',
            '/sheets/lodgements'                  => 'Lodgements',
            '/sheets/operations-scorecard'        => 'Operations Scorecard',
            '/sheets/phased-budget'               => 'Phased Budget',
            '/sheets/stock-control'               => 'Stock Control',
            '/sheets/vending-sales'               => 'Vending Sales',
            '/reports/cash-sales'                 => 'Cash Sales Report',
            '/reports/client-feedback'            => 'Client Feedback Report',
            '/reports/problem-report'             => 'Corrective Action Report',
            '/reports/credit-sales'               => 'Credit Sales Report',
            '/reports/labour-hours'               => 'Labour Hours Report',
            '/reports/lodgements'                 => 'Lodgements Report',
            '/reports/operations-scorecard'       => 'Operations Scorecard Report',
            '/reports/purchases'                  => 'Purchases Report',
            '/reports/purchases-summary'          => 'Purchases Summary Report',
            '/reports/sales-summary'              => 'Sales Summary Report',
            '/reports/stock-control'              => 'Stock Control Report',
            '/reports/unit-trading-account'       => 'UTA Report',
            '/reports/unit-trading-account-stock' => 'UTA + Stock Report',
            '/reports/vending-sales'              => 'Vending Sales Report',
            '/files'                              => 'Files',
        ];

        foreach (self::rootDirNames() as $dirName) {
            $linkTitles["/files/{$dirName->id}"] = $dirName->dir_file_name;
        }

        return $linkTitles;
    }

    public static function multiAttrsWhere(string $field, array $values): string
    {
        return "$field REGEXP '(^|,)(" . implode('|', $values) . ")(,|$)'";
    }
}