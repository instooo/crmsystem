<?php
/*
*name:工作流引擎
*author:dengxiaolong
*/
namespace Common\Vendor\Workflow;

class partnerlog{	
	//添加审批流程记录
	public function addPartnerlog($data){
		$logdata['p_id']=$data['p_id'];		
		$logdata['uid']	=$data['uid'];
		$logdata['pid']	=$data['pid']?$data['pid']:0;
		$logdata['re_uid']	=$data['reuid']?$data['reuid']:0;		
		$logdata['create_time']=time();	
		$logdata['comment']=$data['comment'];			
		M('work_partner_log')->add($logdata);
	}	
}
?>