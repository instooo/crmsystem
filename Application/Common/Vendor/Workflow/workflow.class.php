<?php
/*
*name:工作流引擎
*author:dengxiaolong
*/
namespace Common\Vendor\Workflow;

class workflow{	
	
	//获取当前用户拥有实例列表
	public function caselist($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{	
			$type = $data['type'];
			$nowuid = $data['user'];		
			$map['c_create_uid'] = $nowuid;			
			$result = M('work_case a')			
			->where($map)			
			->select();
			if($result){
				$ret['code'] = '1';
				$ret['msg'] = '获得数据';
				$ret['data'] = $result;
			}	
			break;
		}while(0);
		return $ret;		
	}	
	
	//获取当前用户拥有实例列表
	public function caselist_detail($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{	
			$type = $data['type'];
			$nowuid = $data['user'];
			if($type == 'done'){//完成的实例
				$map['c_create_uid'] = $nowuid;
				$map['c_state'] = 2;
				$result = M('work_case a')			
				->where($map)			
				->select();
			}else if($type == 'doing'){//正在进行的实例
						
			}
			if($result){
				$ret['code'] = '1';
				$ret['msg'] = '获得数据';
				$ret['data'] = $result;
			}	
			break;
		}while(0);
		return $ret;		
	}	
	
	//添加实例	
	public function addCase($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{			
			$data['uid'] = $data['uid'];//用户id
			$data['wid'] = $data['wid'];//添加合同时候，选择哪种流程
			$data['title']=$data['title'];			
			if( $data['uid']=='' || $data['wid']==''||$data['title']==''){
				$ret['code'] = '-40';
				$ret['msg'] = '参数不全';
				break;
			}			
			$adddata['c_id']='';
			$adddata['w_id']=$data['wid'];
			$adddata['c_title']=$data['title'];
			$adddata['c_state']	=0;
			$adddata['c_create_uid']=$data['uid'];
			$adddata['c_create_time']=time();
			$result  =M ('work_case')->add($adddata);
			if($result){
				$ret['code'] = '1';
				$ret['msg'] = '添加成功';
				break;
			}else{
				$ret['code'] = '1';
				$ret['msg'] = '添加失败';
				break;
			}
			
		}while(0);
		return $ret;
	}
	
	//执行步骤
	public function doStep($data){	
		$ret = array('code'=>-1,'msg'=>'');
        do{
			$data['uid'] = $data['uid'];//用户id
			$data['c_id'] = $data['c_id'];//添加合同时候，选择哪种流程
			$data['act'] = $data['act'];//用户id
			if( $data['uid']=='' || $data['c_id']==''|| $data['act']==''){
				$ret['code'] = '-40';
				$ret['msg'] = '参数不全';
				break;
			}
			//查找当前流程实例
			$casemap['c_id']=$data['c_id'];			
			$caseresult = M ('work_case')->where($casemap)->find();
			if(!$caseresult){
				$ret['code'] = '-30';
				$ret['msg'] = '无当前实例';
				break;
			}
			if($caseresult['c_state']==2){
				$ret['code'] = '-31';
				$ret['msg'] = '该流程已完成';
				break;
			}
			//查找当前到了哪一步			
			$stepmap['e_id']=$data['c_id'];
			$stepresult  = M('work_case_step')->where($stepmap)->order('step desc')->find();
			//如果ACT为so_start，说明刚提交审核
			if(!$stepresult){
				if($data['act'] != 'so_start'){
					$ret['code'] = '-1';
					$ret['msg'] = '系统报错，请联系管理员';
					break;
				}else if($data['act'] == 'so_start'){
					//查找流程第一步
					$extendmap['w_id'] = $caseresult['w_id'];
					$extendresult = M ('workflow_extend')->where($extendmap)->order('step_id asc')->find();
					$adddata['e_id']=$data['c_id'];
					$adddata['w_id']=$caseresult['w_id'];
					$adddata['uid']=$extendresult['uid'];
					$adddata['step']	=1;
					$adddata['st_status']=0;
					$adddata['st_create_time']=time();
					$result  =M ('work_case_step')->add($adddata);
					if($result){
						$ret['code'] = '1';
						$ret['msg'] = '提交审核成功';
						break;
					}				
				}
			}
			if($data['act'] != 'so_start'){
				$ret['code'] = '-1';
				$ret['msg'] = '系统报错，请联系管理员';
				break;
			}else if($data['act'] == 'so_start'){
				//查找流程第一步
				$extendmap['w_id'] = $caseresult['w_id'];
				$extendresult = M ('workflow_extend')->where($extendmap)->order('step_id asc')->find();
				$adddata['e_id']=$data['c_id'];
				$adddata['w_id']=$caseresult['w_id'];
				$adddata['uid']=$extendresult['uid'];
				$adddata['step']	=1;
				$adddata['st_status']=0;
				$adddata['st_create_time']=time();
				$result  =M ('work_case_step')->add($adddata);
				if($result){
					$ret['code'] = '1';
					$ret['msg'] = '提交审核成功';
					break;
				}				
			}else{
				
			}
			
			
			//查找流程最大步骤
			$extendmap['w_id'] = $caseresult['w_id'];
			$extendresult = M ('workflow_extend')->where($extendmap)->order('step_id desc')->find();
			$nowstep = $stepresult['step']?$stepresult['step']:0;				
			if($nowstep < $extendresult['step_id']){
				$adddata['e_id']=$data['c_id'];
				$adddata['w_id']=$caseresult['w_id'];
				$adddata['uid']=$data['uid'];
				$adddata['step']	=$nowstep+1;
				$adddata['st_status']=0;
				$adddata['st_create_time']=time();
				$result  =M ('work_case_step')->add($adddata);
				($adddata['step']==$extendresult['step_id'])?$updatedata['c_state']=3:$updatedata['c_state']=2;				
				$updatedata['c_id']=$data['c_id'];									
				$this->case_status($updatedata);
		
			}else{
				$ret['code'] = '1';
				$ret['msg'] = '项目已完成';
				break;
			}
		}while(0);
		return $ret;
	}	
	
	//修改实例状态
	private function case_status($data){		
		$map['c_id']=$data['c_id'];
		$update_data['c_state']=$data['c_state'];
		M('work_case')->where($map)->save($update_data);
	}
	
	//是否达到下一步条件
	private function shenpi_map(){
		
	}
	//获取当前流程可以执行的操作
	public function get_act($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{					
			//查看相关实例是否存在
			$caseremap['c_id'] = $data['work_case'];			
			$casere = M('work_case')->where($caseremap)->find();			
			if(!$casere){
				$ret['code'] = '-1';
				$ret['msg'] = '实例不存在';	
				break;
			}
			//查找实例目前目前进行到哪一步
			$stepmap['uid'] = $data['user'];
			$stepmap['e_id'] = $data['work_case'];
			$stepresult  = M('work_case_step')->where($stepmap)->order('step desc')->find();			
			//如果还未送审
			if(!$stepresult){
				if($stepmap['uid'] == $casere['c_create_uid']){
					$ret['code'] = '1';
					$ret['msg'] = '完成';				
					$ret['data'][]= array('action'=>'so_start','des'=>'提交审批',);
					break;
				}else{
					$ret['code'] = '-1';
					$ret['msg'] = '无当前实例数据';		
					break;
				}		
			}
			if($stepresult['w_id'] && ($stepresult['st_status']==0||$stepresult['st_status']==1)){//如果不是已完成
				$map['step_id'] =$stepresult['step']; 
				$map['w_id'] =$stepresult['w_id'];
				$workflow_extendresult = M('workflow_extend')->where($map)->find();
				//
				$action = explode('|',$workflow_extendresult['action']);
				//返回按钮、
				$actmap['id']=array('in',$action);
				$actresult = M('workflow_action')->where($actmap)->select();				
				$ret['code'] = '1';
				$ret['msg'] = '完成';				
				$ret['data']= $actresult;
				break;
			}else{
				$ret['code'] = '-1';
				$ret['msg'] = '已完成';	
				break;	
			}
		}while(0);
		return $ret;
	}
	
}
?>