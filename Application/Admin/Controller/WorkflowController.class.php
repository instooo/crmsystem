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
			//[step] => steps1:user1|user2,steps2:user1
					
            $data = array();
            $data['w_name'] = I('post.w_name'); 
			$data['w_createtime']=time();
			//处理流程的几个步骤
			$steps=I('post.step');
			$steps_arr = explode(',',$steps);	
			
			
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
			//添加流程步骤
			$extend_data=array();
			foreach($steps_arr as $key=>$val){
				$tmp_ar=explode(':',$val);	
				$extend_data[$key]['step_id']=str_replace('steps','',$tmp_ar[0]);
				$extend_data[$key]['uid']=str_replace('user','',$tmp_ar[1]);
				$extend_data[$key]['status']=1;
				$extend_data[$key]['create_time']=time();
				$extend_data[$key]['child_wid']=0;
				$extend_data[$key]['w_id']=$rs;
			}
			$map['w_id']=$rs;			
			$extend = M('workflow_extend')->where($map)->find(); 
			if ($extend) {
               M('workflow_extend')->where($map)->delete();
            } 
			$result = M('workflow_extend')->addAll($extend_data);
            $ret['code'] = 1;
            $ret['msg'] = '添加成功';
            break;
        }while(0);
        exit(json_encode($ret));		
    }
}