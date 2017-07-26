<?php
/*
*name:工作流引擎
*author:dengxiaolong
*/
namespace Common\Vendor\Workflow;
use \Common\Vendor\Workflow\caselog;

class workflow{	
	
	//获取单个实例的具体信息
	public function onecase($data){
		$map['c_id']=$data['c_id'];
		$result['info'] = M('work_case a')			
			->where($map)			
			->find();
		$result['history'] = M('work_case_log')			
			->where($map)
			->order('log_id desc')
			->select();
		return $result;
	}
	
	//获取当前用户拥有实例列表
	public function caselist($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{	
			$type = $data['type'];
			$nowuid = $data['user'];
			if($type == 'mine'){//完成的实例
				$map['a.c_create_uid'] = $nowuid;				
				$result = M('work_case a')
						  ->join('crm_work_case_log b on b.c_id=a.c_id and a.step=b.step')
						  ->where($map)			
						  ->select();				
			}else if($type == 'done'){//已完成的审核
				//查找审核过的合同
				$map['a.uid'] = $nowuid;
				$map['a.step'] = array('not in',array(-1,0));		
				$result_tmp = M('work_case_log a')
						  ->field('a.c_id')									  
						  ->where($map)			
						  ->select();				
				if(!$result_tmp){
					break;
				}
				//查找合同的状态		  
				$c_idarr = array_column($result_tmp,'c_id');				
				$mapnew['a.c_id'] = array('in',$c_idarr);				
				$result = M('work_case_log a')						 
						  ->join('crm_work_case b on b.c_id=a.c_id and a.step=b.step')
						  ->where($mapnew)			
						  ->select();				
				
			}else if($type == 'doing'){//正在进行的实例
				$map['a.uid'] = array('like','%'.$nowuid.'%');
				$map['a.st_status'] = array('in',array(0));				
				$result = M('work_case_step a')	
						  ->join('crm_work_case b on a.c_id=b.c_id')
						  ->join('crm_work_case_log c on c.c_id=a.c_id and b.step=c.step')
						  ->where($map)			
						  ->select();				
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
			$adddata['step']	=-1;
			$adddata['c_state']	=0;
			$adddata['c_create_uid']=$data['uid'];
			$adddata['c_create_time']=time();
			$result  =M ('work_case')->add($adddata);
			
			//添加日志			
			$logdata['c_id']=$result;
			$logdata['w_id']=$data['wid'];			
			$logdata['step']	=-1;
			$logdata['uid']	=$data['uid'];			
			$logdata['act_id']=0;				
			$logdata['des']="添加合同";
			$logdata['status']=0;			
			$workcase = new caselog();	
			$workcase->addCaselog($logdata);
			
			if($result){
				$ret['code'] = '1';
				$ret['msg'] = '添加成功';
				$redata=$adddata;
				$redata['c_id'] = $result;
				$ret['data'] = $redata;
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
			//一个步骤当中，可能有不通过情况
			$stepresult  = M('work_case_step')->where($stepmap)->order('st_id desc')->find();			
			//如果ACT为so_start，说明刚提交审核
			if(!$stepresult){
				if($data['act'] != 'so_start'){
					$ret['code'] = '-1';
					$ret['msg'] = '系统报错，请联系管理员';
					break;
				}else if($data['act'] == 'so_start'){
					
					//判断当前是否为传创建人
					if($caseresult['c_create_uid']!=$data['uid']){
						$ret['code'] = '-32';
						$ret['msg'] = '你没权限提交审批';
						break;
					}					
					
					//查找流程第一步
					$extendmap['w_id'] = $caseresult['w_id'];
					$extendmap['step_id'] = 1;
					$extendresult = M ('workflow_extend')->where($extendmap)->find();
					
					
					$adddata['c_id']=$data['c_id'];
					$adddata['e_id']=$extendresult['e_id'];
					$adddata['w_id']=$caseresult['w_id'];
					$adddata['uid']=$extendresult['uid'];
					$adddata['step']	=1;
					$adddata['st_status']=0;
					$adddata['st_create_time']=time();				
					$result  =M ('work_case_step')->add($adddata);
					
					//添加日志			
					$logdata['c_id']=$data['c_id'];
					$logdata['w_id']=$caseresult['w_id'];	
					$logdata['step']	=0;
					$logdata['uid']	=$data['uid'];//用户id		
					$logdata['act_id']=0;				
					$logdata['des']="提交审核";
					$logdata['status']=0;			
					$workcase = new caselog();	
					$workcase->addCaselog($logdata);
					
					if(!$result){
						$ret['code'] = '-1';
						$ret['msg'] = '系统错误';
						break;
					}				
				}
				//修改当前实例状态
				$cdata['c_id']=$data['c_id'];
				$cdata['c_state'] = 1;
				$cdata['step'] = 0;
				$this->case_status($cdata);
				$ret['code'] = '1';
				$ret['msg'] = '提交审核成功';
				break;
			}
			else
			{	
				//如果是通过
				if($data['act'] == 'pass'){
					//查找流程最大步骤
					$extendmap['w_id'] = $caseresult['w_id'];
					$extendresult = M ('workflow_extend')->where($extendmap)->order('e_id desc')->find();
					$nowstep = $caseresult['step']+1;		
					if($nowstep <= $extendresult['step_id']){					
						//修改上一步的状态
						$ssmap['st_id']=$stepresult['st_id'];						
						$updatess['st_status']=1;
						$result  =M ('work_case_step')->where($ssmap)->save($updatess);					
						
						//添加日志记录					
						$logdata['c_id']=$data['c_id'];
						$logdata['w_id']=$caseresult['w_id'];	
						$logdata['step']	=$nowstep;
						$logdata['uid']	=$data['uid'];//用户id		
						$logdata['act_id']=0;				
						$logdata['des']= "审核通过";
						$logdata['status']=0;			
						$workcase = new caselog();	
						$workcase->addCaselog($logdata);
						
						//添加下一个流程的记录
						if($nowstep < $extendresult['step_id']){						
							//查找下一步流程
							$extendmap['w_id'] = $caseresult['w_id'];
							$extendmap['step_id']=$nowstep+1;
							$nextresult = M ('workflow_extend')->where($extendmap)->order('step_id desc')->find();							
							
							$adddata['c_id']=$data['c_id'];
							$adddata['e_id']=$nextresult['e_id'];
							$adddata['w_id']=$caseresult['w_id'];
							$adddata['uid']=$nextresult['uid'];
							$adddata['step']	=$nowstep+1;
							$adddata['st_status']=0;
							$adddata['st_create_time']=time();
							$result  =M ('work_case_step')->add($adddata);		
						}						

						//修改当前实例状态
						$cdata['c_id']=$data['c_id'];
						$cdata['c_state'] = ($nowstep ==$extendresult['step_id'])?2:1;
						$cdata['step'] = $nowstep;
						$this->case_status($cdata);
						$ret['code'] = '1';
						$ret['msg'] = '提交审核成功';
						break;
					}else{
						$ret['code'] = '1';
						$ret['msg'] = '项目已完成';
						break;
					}
				}else if($data['act'] == 'nopass'){				
					
					//添加日志记录					
					$logdata['c_id']=$data['c_id'];
					$logdata['w_id']=$caseresult['w_id'];	
					$logdata['step']=$stepresult['step'];
					$logdata['uid']	=$data['uid'];//用户id		
					$logdata['act_id']=0;				
					$logdata['des']= "审核不通过";
					$logdata['status']=-1;			
					$workcase = new caselog();	
					$workcase->addCaselog($logdata);
					
					//修改上一步的状态
					$ssmap['st_id']=$stepresult['st_id'];						
					$updatess['st_status']=-1;
					$result  =M ('work_case_step')->where($ssmap)->save($updatess);
					
					$ret['code'] = '1';
					$ret['msg'] = '提交审核成功';
					break;
				}else if($data['act'] == 'so_start'){
					//判断当前是否为传创建人
					if($caseresult['c_create_uid']!=$data['uid']){
						$ret['code'] = '-32';
						$ret['msg'] = '你没权限提交审批';
						break;
					}					
					//添加日志			
					$logdata['c_id']=$data['c_id'];
					$logdata['w_id']=$caseresult['w_id'];	
					$logdata['step']	=0;
					$logdata['uid']	=$data['uid'];//用户id		
					$logdata['act_id']=0;				
					$logdata['des']="重新提交审核";
					$logdata['status']=0;			
					$workcase = new caselog();	
					$workcase->addCaselog($logdata);
					
					//修改上一步的状态
					$ssmap['st_id']=$stepresult['st_id'];						
					$updatess['st_status']=0;
					$result  =M ('work_case_step')->where($ssmap)->save($updatess);				
					
					if(!$result){
						$ret['code'] = '-1';
						$ret['msg'] = '系统错误';
						break;
					}			
							
				}else{
					
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
			$uid = $data['user'];	
			$caseremap['c_id'] = $data['work_case'];			
			$casere = M('work_case a')					
					->where($caseremap)
					->find();			
			if(!$casere){
				$ret['code'] = '-1';
				$ret['msg'] = '实例不存在';	
				break;
			}else{
				if($casere['step']==-1 && $uid ==$caseremap['c_create_uid']){
					$ret['code'] = '1';
					$ret['msg'] = '提交审批';				
					$ret['data'][]= array('action'=>'so_start','des'=>'提交审批',);
					break;
				}else{						
					//查找当前的操作
					if($uid == $casere['c_create_uid'] && $casere['step']>-1){
						$map['c_id'] = $data['work_case'];
						$sresult = M('work_case_step')->where($map)->order('st_id desc')->find();if($sresult['st_status'] == -1){
							$ret['code'] = '1';
							$ret['msg'] = '提交审批';				
							$ret['data'][]= array('action'=>'so_start','des'=>'提交审批',);
							break;
						}else{
							$ret['code'] = '-1';
							$ret['msg'] = '已完成';	
						}
					}else{
						$map['uid'] = array('like','%'.$uid.'%');
						$map['c_id'] = $data['work_case'];
						$map['step'] = $casere['step']+1;
						$map['st_status'] = 0;
						$sresult = M('work_case_step')->where($map)->find();
						if($sresult){
							$ret['code'] = '-1';
							$ret['msg'] = '已完成';	
							$ret['data'][]= array(
								'action'=>'pass',
								'des'=>'通过'
							);	
							$ret['data'][]= array(
								'action'=>'nopass',
								'des'=>'不通过'
							);					
							break;	
						}else{
							$ret['code'] = '-1';
							$ret['msg'] = '已完成';	
						}	
					}					
				}
			}			
		}while(0);
		return $ret;
	}
	
}
?>