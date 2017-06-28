<?php
/**
 * 控制器
 * Created by dengxiaolong
 * Date: 2017/6/22
 */
namespace Admin\Controller;

use Think\Controller;

class RoleController extends CommonController {

    public function index(){	
		$wid=$_GET['wid'];
		$this->assign('wid',$wid);
		$this->display();
	}
	
}