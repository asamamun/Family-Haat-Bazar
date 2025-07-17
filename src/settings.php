<?php
// Set default timezone for the application
date_default_timezone_set('Asia/Dhaka');

if (!function_exists('settings')) {
    function settings()
    {
        $root = "http://localhost/Family-Haat-Bazar/";
        return [
            'root' => $root,
            'companyname' => 'Shopno',
            'logo' => $root . "admin/assets/img/logo.png",
            'homepage' => $root,
            'adminpage' => $root . 'admin/',
            'hostname' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'haatbazar',
            'timezone' => 'Asia/Dhaka',
            'physical_path' => 'D:\xampp8212\htdocs\Family-Haat-Bazar',
        ];
    }
}
if (!function_exists('testfunc')) {
    function testfunc()
    {
        return "<h3>testing common functions</h3>";
    }
}
if (!function_exists('config')) {
    function config($param)
    {
        $parts = explode(".", $param);
        $configFile = __DIR__ . "/../config/" . $parts[0] . ".php";

        if (!file_exists($configFile)) {
            error_log("Config file not found: " . $configFile);
            return null;
        }

        $inc = include $configFile;

        if (!is_array($inc) || !isset($inc[$parts[1]])) {
            error_log("Config key not found: " . $param);
            return null;
        }

        return $inc[$parts[1]];
    }
}
