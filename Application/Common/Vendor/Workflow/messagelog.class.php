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
			M('message_log')->add($logdata);	
		}	
	}

}
?>