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
	
	//获取单个实例的具体信息
	public function onecase($data){
		$map['c_id']=$data['c_id'];
		$result = M('work_case a')			
			->where($map)			
			->find();
		return $result;
	}
	//获取当前用户拥有实例列表
	public function caselist_detail($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{	
			$type = $data['type'];
			$nowuid = $data['user'];
			if($type == 'mine'){//完成的实例
				$map['a.c_create_uid'] = $nowuid;				
				$result = M('work_case a')			
						  ->join('crm_work_case_step b on a.c_id=b.c_id')
						  ->join('crm_workflow_extend c on c.e_id=b.e_id and c.w_id=b.w_id')						
						  ->where($map)			
						  ->select();
			}else if($type == 'done'){//已完成的审核
				$map['uid'] = $nowuid;
				$result = M('work_case_step')
						  ->where($map)
						  ->select();
			}else if($type == 'doing'){//正在进行的实例
				$map['a.uid'] = array('like','%'.$nowuid.'%');
				$map['a.st_status'] = array('in',array(0,1));				
				$result = M('work_case_step a')	
						  ->join('crm_work_case b on a.c_id=b.c_id')
						  ->join('crm_workflow_extend c on c.e_id=a.e_id and c.w_id=a.w_id')						
						  ->where($map)			
						  ->select();
				print_r($result);
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
			$adddata['step']	=0;
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
			$stepmap['c_id']=$data['c_id'];
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
					
					$adddata[0]['c_id']=$data['c_id'];
					$adddata[0]['e_id']=0;
					$adddata[0]['w_id']=0;
					$adddata[0]['uid']=$data['uid'];
					$adddata[0]['step']	=0;
					$adddata[0]['st_status']=2;
					$adddata[0]['st_create_time']=time();	
					
					
					$adddata[1]['c_id']=$data['c_id'];
					$adddata[1]['e_id']=$extendresult['e_id'];
					$adddata[1]['w_id']=$caseresult['w_id'];
					$adddata[1]['uid']=$extendresult['uid'];
					$adddata[1]['step']	=1;
					$adddata[1]['st_status']=0;
					$adddata[1]['st_create_time']=time();				
					$result  =M ('work_case_step')->addAll($adddata);
					if(!$result){
						$ret['code'] = '-1';
						$ret['msg'] = '系统错误';
						break;
					}				
				}
				//修改当前实例状态
				$cdata['c_id']=$data['c_id'];
				$cdata['c_state'] = 1;
				$cdata['step'] = 1;
				$this->case_status($cdata);
				$ret['code'] = '1';
				$ret['msg'] = '提交审核成功';
				break;
			}else{					
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
			}	
			
		}while(0);
		return $ret;
	}	
	
	//修改实例状态
	private function case_status($data){		
		$map['c_id']=$data['c_id'];
		$update_data['c_state']=$data['c_state'];
		$update_data['step']=$data['step'];
		M('work_case')->where($map)->save($update_data);
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
			$stepmap['uid'] = array('like','%'.$data['user'].'%');
			$stepmap['c_id'] = $data['work_case'];			
			$stepresult  = M('work_case_step')->where($stepmap)->order('step desc')->find();			
			//如果还未送审
			if(!$stepresult){
				if($data['user'] == $casere['c_create_uid']){
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