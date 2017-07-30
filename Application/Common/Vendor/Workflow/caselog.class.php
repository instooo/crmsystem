<?php
/*
*name:工作流引擎
*author:dengxiaolong
*/
namespace Common\Vendor\Workflow;

class caselog{	
	//添加审批流程记录
	public function addCaselog($data){
		$logdata['c_id']=$data['c_id'];
		$logdata['w_id']=$data['w_id'];			
		$logdata['step']=$data['step'];
		$logdata['uid']	=$data['uid'];
		$logdata['pid']	=$data['pid']?$data['pid']:0;
		$logdata['re_uid']	=$data['re_uid']?$data['re_uid']:0;
		$logdata['act_id']=$data['act_id'];
		$logdata['create_time']=time();
		$logdata['des']=$data['des'];
		$logdata['status']=$data['status'];
		$logdata['comment']=$data['comment'];			
		M('work_case_log')->add($logdata);
	}	
}
?>