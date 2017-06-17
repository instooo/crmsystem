<?php
/**
 * Created by PhpStorm.
 * User: qf19910623
 * Date: 2017/6/16 0016
 * Time: 0:04
 */
namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;

class CommonController extends Controller {

    public function _initialize() {
        /*
        //rbac 自带的游客验证
        Rbac::checkLogin();
        //如果是Index模块另外判断
        if(strtoupper(CONTROLLER_NAME)=="INDEX"){
            if(!isset($_SESSION['userid'])){
                redirect(PHP_FILE.C('USER_AUTH_GATEWAY'));
            }
        }
        //验证权限
        if(!Rbac::AccessDecision()){
            if(IS_AJAX){
                $this->ajaxReturn(array("code"=>403,"msg"=>'没有权限',"data"=>""));
            }else
                $this->error("没有权限");
        }

        if($_SESSION[C('ADMIN_AUTH_KEY')])
            $datalist   =   D('Node')->getNodeList();
        else
            $datalist   =   D('Node')->getNodeListByUid($_SESSION[C('USER_AUTH_KEY')]);
        $tree       =   D('Node')->getChildNode(0,$datalist);
        $this->assign("tree",$tree);
        */
    }

}