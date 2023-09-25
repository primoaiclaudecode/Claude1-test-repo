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

    public static function formatCurrencyAmount($symbol, $num){
        return $symbol . number_format($num, 2);
    }

    public static function formatEuroAmounts($num)
    {
        return "&euro;" . number_format($num, 2);
    }

    public static function formatEuroAmountsForCSV($num)
    {
        return "€" . number_format($num, 2);
    }

    public static function multiAttrsWhere(string $field, array $values): string
    {
        return "$field REGEXP '(^|,)(" . implode('|', $values) . ")(,|$)'";
    }
}