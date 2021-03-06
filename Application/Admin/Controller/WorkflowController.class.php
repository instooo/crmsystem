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
			//[acts] => steps1:act1|act2,steps2:act1
            $data = array();
            $data['w_name'] = I('post.w_name'); 
			$data['w_createtime']=time();
			//处理流程的几个步骤
			$steps=I('post.step');
			$acts=rtrim(I('post.act'),',');	
			$des=rtrim(I('post.des'),',');
			$field=rtrim(I('post.field'),',');	
			
			$steps_arr = explode(',',$steps);
			$acts_arr = explode(',',$acts);			
			$dess_arr = explode(',',$des);
			$field_arr = explode(',',$field);
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
			foreach($acts_arr as $key=>$val ){
				$tmp_ar=explode(':',$val);					
				$extend_data[$key]['step_id']=str_replace('steps','',$tmp_ar[0]);
				$extend_data[$key]['action']=str_replace('user','',rtrim($tmp_ar[1],'|'));
				$extend_data[$key]['status']=1;
			}
			foreach($dess_arr as $key=>$val ){
				$tmp_ar=explode(':',$val);					
				$extend_data[$key]['step_id']=str_replace('steps','',$tmp_ar[0]);
				$extend_data[$key]['des']=str_replace('user','',rtrim($tmp_ar[1],'|'));				
			}
			foreach($field_arr as $key=>$val ){
				$tmp_ar=explode(':',$val);					
				$extend_data[$key]['step_id']=str_replace('steps','',$tmp_ar[0]);
				$extend_data[$key]['field']=str_replace('user','',rtrim($tmp_ar[1],'|'));	
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

	//节点添加
    public function workflow_del()
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
            $data['wid'] = I('post.wid'); 			
            if (!$data['wid']) {
                $ret['code'] = -2;
                $ret['msg'] = '参数不全';
                break;
            }			
			$map['w_id']=$data['wid'];
			//查看流程中是否有正在执行的流程
			$result = M('work_case_log')->where($map)->find();
			if($result){
				$ret['code'] = -2;
                $ret['msg'] = '拥有数据，不可以删除';
                break;
			}
			M('workflow')->where($map)->delete(); 
			$mapa['w_id']=$data['wid'];	
			M('workflow_extend')->where($map)->delete();					
            $ret['code'] = 1;
            $ret['msg'] = '成功';
            break;
        }while(0);
        exit(json_encode($ret));		
    }
 
	//流程的展示
	public function detail(){
		$data['wid'] = I('get.wid'); 			
		if (!$data['wid']) {
            echo "参数不全";die;
		}
		$map['w_id']=$data['wid'];			
		$result = M('workflow')->where($map)->find(); 
		if($result){			
			$extend = M('workflow_extend')->where($map)->order('e_id desc')->select();			
			foreach($extend as $key=>$val){
				$extend[$key]['left'] =20+($val['step_id']-1)*180;
			}			
		}else{
			echo "流程不存在";die;
		}
		$this->assign('result',$result);
		$this->assign('extend',$extend);
		$this->display();
	}

	//编辑节点
	public function edit(){
		if(IS_POST){
			$ret = array('code'=>-1,'msg'=>'');
			do{				
				//[step] => steps1:user1|user2,steps2:user1
				//[acts] => steps1:act1|act2,steps2:act1
				$data = array();
				$data['w_id'] = I('post.w_id'); 
				$data['w_name'] = I('post.w_name'); 
				$data['w_createtime']=time();
				//处理流程的几个步骤
				$steps=I('post.step');
				$acts=rtrim(I('post.act'),',');	
				$des=rtrim(I('post.des'),',');
				$field=rtrim(I('post.field'),',');	
				
				$steps_arr = explode(',',$steps);
				$acts_arr = explode(',',$acts);			
				$dess_arr = explode(',',$des);
				$field_arr = explode(',',$field);
				if (!$data['w_name']) {
					$ret['code'] = -2;
					$ret['msg'] = '参数不全';
					break;
				}			
				$map['w_id']=$data['w_id'];			
				$l = M('workflow')->where($map)->find();         
				if (!$l) {
					$ret['code'] = -3;
					$ret['msg'] = '流程不存在';
					break;
				} 			
				$rs = M('workflow')->where($map)->save($data);			
				if (!$rs) {
					$ret['code'] = -4;
					$ret['msg'] = '保存失败';
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
					$extend_data[$key]['w_id']=$data['w_id'];
				}				
				foreach($acts_arr as $key=>$val ){
					$tmp_ar=explode(':',$val);					
					$extend_data[$key]['step_id']=str_replace('steps','',$tmp_ar[0]);
					$extend_data[$key]['action']=str_replace('user','',rtrim($tmp_ar[1],'|'));
					$extend_data[$key]['status']=1;
				}
				foreach($dess_arr as $key=>$val ){
					$tmp_ar=explode(':',$val);					
					$extend_data[$key]['step_id']=str_replace('steps','',$tmp_ar[0]);
					$extend_data[$key]['des']=str_replace('user','',rtrim($tmp_ar[1],'|'));				
				}
				foreach($field_arr as $key=>$val ){
					$tmp_ar=explode(':',$val);					
					$extend_data[$key]['step_id']=str_replace('steps','',$tmp_ar[0]);
					$extend_data[$key]['field']=str_replace('user','',rtrim($tmp_ar[1],'|'));	
				}									
				$map['w_id']=$data['w_id'];			
				$extend = M('workflow_extend')->where($map)->find(); 
				if ($extend) {
				   M('workflow_extend')->where($map)->delete();
				} 
				$result = M('workflow_extend')->addAll($extend_data);
				$ret['code'] = 1;
				$ret['msg'] = '修改成功';
				break;
			}while(0);
			exit(json_encode($ret));
		}else{
			$data['wid'] = I('get.wid'); 			
			if (!$data['wid']) {
                echo "参数不全";die;
			}
			$map['w_id']=$data['wid'];			
			$result = M('workflow')->where($map)->find(); 
			if($result){	
				//查找所有用户
				$usertmp = M('user')->select();
				$userarr = array();
				foreach($usertmp as $key=>$val){
					$userarr[$val['user_number']]=$val;	
				}			
				$extend = M('workflow_extend')->where($map)->order('step_id asc')->select();
				foreach($extend as $key=>$val){
					$exuser = explode('|',$val['uid']);				
					foreach($exuser as $k=>$v){
						$extend[$key]['username'] .= $userarr[$v]['nickname'].",";
					}
				}			
				$result['child'] = $extend;			
			}else{
				echo "流程不存在";die;
			}	
			//print_r($result);die;
			//print_r($extend);die;
			$this->assign('result',$result);
			$length = count($extend)*130+55;
			$this->assign('length',$length);
			
			$this->display();	
		}
		
	}
}