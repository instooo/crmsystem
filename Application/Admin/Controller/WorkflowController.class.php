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
		$this->assign('result',$result);
		$this->display();
	}
	//节点添加
    public function workflow_add()
    {		
		$ret = array('code'=>-1,'msg'=>'');
        do{
            if (!IS_POST) {
                $ret['code'] = -1;
                $ret['msg'] = '非法请求';
                break;
            }
            $data = array();
            $data['w_name'] = I('post.w_name'); 
			$data['w_createtime']=time();
            if (!$data['w_name']) {
                $ret['code'] = -2;
                $ret['msg'] = '参数不全';
                break;
            }			
			$map['w_name']=$data['w_name'];			
			$l = M('workflow')->where($map)->find();         
            if ($l) {
                $ret['code'] = -3;
                $ret['msg'] = '流程已存在';
                break;
            } 			
            $rs = M('workflow')->add($data);
			
            if (!$rs) {
                $ret['code'] = -4;
                $ret['msg'] = '添加失败';
                break;
            }
            $ret['code'] = 1;
            $ret['msg'] = '添加成功';
            break;
        }while(0);
        exit(json_encode($ret));		
    }
}