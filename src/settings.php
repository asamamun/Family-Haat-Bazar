<?php
if (!function_exists('settings')) {
    function settings()
    {
       $root = "http://localhost/ROUND64/PHP/Family-Haat-Bazar/"; 
        return [
            'root'  => $root,
            'companyname'=> 'Family Haat Bazaar',
            'logo'=>$root."admin/assets/img/logo.svg",
            'homepage'=> $root,
            'adminpage'=>$root.'admin/',
            'hostname'=> 'localhost',
            'user'=> 'root',
            'password'=> '',
            'database'=> 'haatbazar'
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
      $parts = explode(".",$param);
      $inc = include(__DIR__."/../config/".$parts[0].".php");
      return $inc[$parts[1]];
    }
}
