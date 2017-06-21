<?php
/**
 * 控制器
 * Created by dengxiaolong
 * Date: 2017/6/22
 */
namespace Admin\Controller;

use Think\Controller;

class WorkflowController extends CommonController {

    public function index(){
		$result =M('workflow')->select();
		print_r($result);
	}
}