<?php
$rbac = include('rbac.php');
$config = array(
	//'配置项'=>'配置值'
    'STATIC_URL'    =>  '/Public/Admin',
	'SHOW_PAGE_TRACE'=>false,
);
return array_merge($rbac, $config);