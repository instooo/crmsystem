<?php
/*
*name:工作流引擎
*author:dengxiaolong
*/
namespace Common\Vendor\Workflow;

class workflow{	
	
	//添加实例	
	public function doActive($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{
			//require_once 'work_config.php';
			$data['uid'] = $data['uid'];//用户id
			$data['wid'] = $data['wid'];//添加合同时候，选择哪种流程
			$data['c_id']='';//为空的时候,则添加新的实例
			if( $data['uid']=='' || $data['wid']==''){
				$ret['code'] = '-40';
				$ret['msg'] = '参数不全';
				break;
			}
			//添加新实例
			if($data['c_id']==''){
				$adddata['c_id']='';
				$adddata['w_id']=$data['wid'];
				$adddata['c_title']=$data['title'];
				$adddata['c_state']	=0;
				$adddata['c_create_uid']=$data['uid'];
				$adddata['c_create_time']=time();
				$result  =M ('work_case')->add($adddata);
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
			if( $data['uid']=='' || $data['c_id']==''){
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
			//查找当前到了哪一步
			$stepmap['uid']=$data['uid'];
			$stepmap['e_id']=$data['c_id'];
			$stepresult  = M('work_case_step')->where($stepmap)->order('step desc')->find();
			
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
			$stepmap['uid'] = $data['user'];
			$stepmap['e_id'] = $data['work_case'];
			$stepresult  = M('work_case_step')->where($stepmap)->order('step desc')->find();			
			if($stepresult['w_id'] && $stepresult['w_id']!=2){//如果不是已完成
				$map['step_id'] =$stepresult['step']; 
				$map['w_id'] =$stepresult['w_id'];
				$workflow_extendresult = M('workflow_extend')->where($map)->find();
				//返回按钮、
				$actmap['id']=array('in',$workflow_extendresult['action']);
				$actresult = M('workflow_action')->where($actmap)->select();				
				$ret['code'] = '1';
				$ret['msg'] = '完成';				
				$ret['data']= $actresult;
				break;
			}
		}while(0);
		return $ret;
	}
	
}
?>