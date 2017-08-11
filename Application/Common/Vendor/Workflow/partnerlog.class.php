<?php
/*
*name:工作流引擎
*author:dengxiaolong
*/
namespace Common\Vendor\Workflow;

class partner{	
	//添加审批流程记录
	public function addPartnerlog($data){
		$logdata['p_id']=$data['c_id'];		
		$logdata['uid']	=$data['uid'];
		$logdata['pid']	=$data['pid']?$data['pid']:0;
		$logdata['re_uid']	=$data['re_uid']?$data['re_uid']:0;		
		$logdata['create_time']=time();
		$logdata['des']=$data['des'];
		$logdata['status']=$data['status'];
		$logdata['comment']=$data['comment'];			
		M('work_partner_log')->add($logdata);
	}	
}
?>