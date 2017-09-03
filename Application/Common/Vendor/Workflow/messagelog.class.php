<?php
/*
*name:工作流引擎
*author:dengxiaolong
*/
namespace Common\Vendor\Workflow;

class messagelog{
	
	//添加消息记录
	public function addMessagelog($data){
		$data['reuid']	=trim($data['reuid'],',');	
		$reuidarr = explode(',',$data['reuid']);		
		foreach($reuidarr as $key=>$val){
			$logdata['c_id']=$data['c_id'];
			$logdata['pid']	=0;
			$logdata['reuid']	=$val;
			$logdata['uid']	=$data['uid'];	
			$logdata['createtime']=time();
			$logdata['updatetime']=0;		
			$logdata['status']=0;
			$logdata['message']=$data['comment'];
			$logdata['type']=$data['type']?$data['type']:"agreement";					
			M('message_log')->add($logdata);	
		}	
	}
	
	//未读消息条数
	public function get_message_count(){		
		$nowuid = $this->get_num_uid();				
		$map['reuid'] = $nowuid;
		$map['status'] = 0;
		$count = M('message_log')->where($map)->count();		
		return $count;
	}

	public function get_message_list($type){
		if($type=='partner'){
			$userinfo = M('user')->select();
			$userinfoarr = array();
			foreach($userinfo as $val){
				$userinfoarr[$val['user_number']]=$val;
			}	
			$nowuid = $this->get_num_uid();				
			$map['reuid'] = $nowuid;
			$map['type'] = $type;
			$list = M('message_log')
					->field('*')				
					->where($map)
					->order('id desc')
					->select();		
			$tmp_arr = array();
			foreach ($list as $key=>$val){
				$tmp_arr[$key]=$val;
				$tmp_arr[$key]['c_id']=str_replace('p_','',$val['c_id']);
				$tmp_arr[$key]['send'] = $userinfoarr[$val['uid']]['nickname'];
			}		
		}else{
			$userinfo = M('user')->select();
			$userinfoarr = array();
			foreach($userinfo as $val){
				$userinfoarr[$val['user_number']]=$val;
			}	
			$nowuid = $this->get_num_uid();				
			$map['reuid'] = $nowuid;
		
			$list = M('message_log a')
					->field('a.*,b.*,b.id as aid,a.id')
					->join('crm_agreement b on a.c_id=b.e_id')
					->where($map)
					->order('a.id desc')
					->select();		
			$tmp_arr = array();
			foreach ($list as $key=>$val){
				$tmp_arr[$key]=$val;
				$tmp_arr[$key]['send'] = $userinfoarr[$val['uid']]['nickname'];
			}	
		}		
		return $tmp_arr;
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
	public function readmessage($id){
		$map['id'] = $id;		
		$list = M('message_log')->where($map)->find();
		if($list){
			$updata['status'] = 1;
			$list = M('message_log')->where($map)->save($updata);
		}
	}
}
?>