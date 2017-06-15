<?php
$dbconfig = include('dbconfig.php');
$config = array(
	//'配置项'=>'配置值'
    'APP_SUB_DOMAIN_DEPLOY' =>  true,   // 是否开启子域名部署
    'APP_SUB_DOMAIN_RULES'  =>  array(
        'www.crm.me' =>  'Home',
        'admin.crm.me' =>  'Admin'
    ), // 子域名部署规则

);
return array_merge($dbconfig, $config);