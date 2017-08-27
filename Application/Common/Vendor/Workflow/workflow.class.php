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
			->join('crm_agreement b on b.e_id = a.c_id')
			->join('crm_partner c on c.id = b.partner_id')
			->where($map)			
			->find();
			
		//查找所有的用户
		$userinfo = M('user')->select();
		$userinfoarr = array();
		foreach($userinfo as $val){
			$userinfoarr[$val['user_number']]=$val;
		}	
		//查看当前实例的具体情况
		$log = M('work_case_log')			
			->where($map)
			->order('log_id asc')
			->select();	
		$tmp_arr = array();
		foreach($log as $key=>$val){
			if($val['pid']==0){
				$tmp_arr[$val['log_id']]['log_id']=$val['log_id'];	
				$tmp_arr[$val['log_id']]['pid']=$val['pid'];
				$tmp_arr[$val['log_id']]['c_id']=$val['c_id'];
				$tmp_arr[$val['log_id']]['w_id']=$val['w_id'];
				$tmp_arr[$val['log_id']]['step']=$val['step'];
				$tmp_arr[$val['log_id']]['uid']=$val['uid'];
				$tmp_arr[$val['log_id']]['user']=$userinfoarr[$val['uid']]['nickname'];
				$tmp_arr[$val['log_id']]['re_uid']=$val['re_uid'];
				$tmp_arr[$val['log_id']]['re_user']=$userinfoarr[$val['re_uid']]['nickname'];
				$tmp_arr[$val['log_id']]['act_id']=$val['act_id'];
				$tmp_arr[$val['log_id']]['create_time']=$val['create_time'];
				$tmp_arr[$val['log_id']]['des']=$val['des'];
				$tmp_arr[$val['log_id']]['comment']=$val['comment'];
				$tmp_arr[$val['log_id']]['status']=$val['status'];
			}else{		
				$pid = $val['pid'];
				$tmp_arr[$pid]['sub'][$key]=$val;
				$tmp_arr[$pid]['sub'][$key]['user']=$userinfoarr[$val['uid']]['nickname'];
				$tmp_arr[$pid]['sub'][$key]['re_user']=$userinfoarr[$val['re_uid']]['nickname'];
			}
		}			
		$result['history'] = $tmp_arr;
		return $result;
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
			$data['comment'] = $data['comment'];//用户id
			$data['nextuid'] = trim($data['nextuid'],',');	
			$data['nextuid'] = str_replace(',','|',$data['nextuid']);			
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
					if(strpos($extendresult['uid'],$data['nextuid'])===false){
						$ret['code'] = '-1';
						$ret['msg'] = '账号不在流程中';
						break;
					}
					
					$adddata['c_id']=$data['c_id'];
					$adddata['e_id']=$extendresult['e_id'];
					$adddata['w_id']=$caseresult['w_id'];
					$adddata['uid']=$data['nextuid'];
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
					$logdata['comment']=$data['comment'];				
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
						$logdata['comment']=$data['comment'];						
						$workcase = new caselog();	
						$workcase->addCaselog($logdata);
						
						//添加下一个流程的记录
						if($nowstep < $extendresult['step_id']){						
							//查找下一步流程
							$extendmap['w_id'] = $caseresult['w_id'];
							$extendmap['step_id']=$nowstep+1;
							$nextresult = M ('workflow_extend')->where($extendmap)->order('step_id desc')->find();							
							if(strpos($nextresult['uid'],$data['nextuid'])===false){
								$ret['code'] = '-1';
								$ret['msg'] = '账号不在流程中';
								break;
							}
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
					$logdata['comment']=$data['comment'];				
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
					$logdata['comment']=$data['comment'];					
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
					$ret['code'] = '1';
					$ret['msg'] = '提交审核成功';
					break;		
				}else{
					$ret['code'] = '-1';
					$ret['msg'] = '错误';
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
				if($casere['step']==-1 && $uid ==$casere['c_create_uid']){
					$ret['code'] = '1';
					$ret['msg'] = '提交审批';				
					$ret['data'][]= array('action'=>'so_start','des'=>'提交审批',);
					break;
				}else{						
					//查找当前的操作
					if($uid == $casere['c_create_uid']){						
						$map['c_id'] = $data['work_case'];
						$sresult = M('work_case_step')->where($map)->order('st_id desc')->find();
						$uidflag = strpos ($sresult['uid'],$casere['c_create_uid']);
						$uidflag = ($uidflag===false)?0:1;
						if($sresult['st_status'] == -1){
							$ret['code'] = '1';
							$ret['msg'] = '提交审批';				
							$ret['data'][]= array('action'=>'so_start','des'=>'提交审批',);
							break;
						}else if($sresult['st_status'] == 0 && $uidflag){
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
	
	//获取每步流程的具体信息
	public function get_step_info($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{	
			$cid = $data['cid'];
			$act = $data['act'];
			if($cid=='' || $act=='' ){
				$ret['code'] = '-1';
				$ret['msg'] = '参数不全';
				break;				
			}
			//查找当前实例是否存在		
			$map['c_id']=$cid;
			$result = M('work_case a')
				->join('crm_user b on a.c_create_uid = b.user_number')			
				->where($map)
				->find();
			if(!$result){
				$ret['code'] = '-2';
				$ret['msg'] = '实例不存在';
				break;	
			}	
			if($act=="nopass"){				
				unset($map);
				$nowuid = $this->get_num_uid();				
				$map['user_number'] = $nowuid;
				$nowuser = M('user')->where($map)->find();
				
				$redata['nowuser'] = $nowuser['nickname'] ;
				$redata['step'] = $result['step']+1 ;				
				$redata['flag'] = 'nopass';
				$ret['code'] = '1';
				$ret['msg'] = 'success';
				$ret['data'] =$redata;				
				break;				
			}else{		
				unset($map);
				if($result['step']==-1){//提交审核
					$map['w_id']=$result['w_id'];
					$next = M('workflow_extend')->where($map)->order('e_id asc')->find();
					//查找下一步的处理人员
					unset($map);
					$uidarr = explode('|',$next['uid']);
					$map['user_number'] = array('in',$uidarr);
					$user = M('user')->where($map)->select();
					
					$redata['nowuser'] = $result['nickname'] ;			
					$redata['step'] = $result['step'] ;
					$redata['stepdes'] = "草稿" ;
					$redata['des'] = $next['des'];
					$redata['nextuser'] = $user;
					$redata['flag'] = 'first';	
					$ret['code'] = '1';
					$ret['msg'] = 'success';
					$ret['data'] =$redata;
					break;	
				}else{
					unset($map);
					$nowuid = $this->get_num_uid();				
					$map['user_number'] = $nowuid;
					$nowuser = M('user')->where($map)->find();
					//查找最大步骤
					unset($map);
					$map['w_id']=$result['w_id'];
					$max = M('workflow_extend')->where($map)->order('step_id desc')->find();
					if(($result['step']+1) >=$max['step_id'] )
					{						
						$redata['nowuser'] = $nowuser['nickname'] ;
						$redata['step'] = $result['step']+1 ;				
						$redata['flag'] = 'last';
						$ret['code'] = '1';
						$ret['msg'] = 'success';
						$ret['data'] =$redata;
						break;		
					}else{
						//查找下一步处理人
						unset($map);
						$map['step_id']=$result['step']+2;
						$map['w_id']=$result['w_id'];
						$next = M('workflow_extend')->where($map)->find();
						unset($map);
						$uidarr = explode('|',$next['uid']);
						$map['user_number'] = array('in',$uidarr);
						$user = M('user')->where($map)->select();
						//查找当前人				
						$redata['nextuser'] = $user;
						$redata['des'] = $next['des'];
						$redata['step'] = $result['step']+1 ;
						$redata['nowuser'] = $nowuser['nickname'] ;	
						$redata['flag'] = 'middle';
						$ret['code'] = '1';
						$ret['msg'] = 'success';
						$ret['data'] =$redata;
						break;		
					}
				
				}	
			}
		}while(0);
		return $ret;
	}

	//获取当前账号
	public function get_num_uid(){
		if($_SESSION['tem_num']){
			//查找当前用户
			$nowuid = $_SESSION['tem_num'];			
		}else if($_SESSION['authId']){
			//查找当前用户
			$nowuid = $_SESSION['user_number'];
		}else{
			echo "未登录";die;
		}
		return $nowuid;	
	}

	//获取可修改字段
	public function get_cedit($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{		
			//查看相关实例是否存在
			$uid = $data['user'];	
			$caseremap['a.c_id'] = $data['work_case'];			
			$casere = M('work_case a')				
					->where($caseremap)
					->find();
			
			if($casere){
				$map['step_id'] = $casere['step']+1;
				$map['w_id'] = $casere['w_id'];
				$result = M('workflow_extend')->where($map)->find();
				
				if($result['field']!=0){
					//查找相应字段名称
					$result['field'] = str_replace('|',',',$result['field']);
					$resultmap['id']=array('in',$result['field']);
					$a = M('fields')->where($resultmap)->select();					
				}
			}
			
		}while(0);
		return $a;	
	}
}
?>