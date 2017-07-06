<?php
/*
*name:工作流引擎
*author:dengxiaolong
*/
namespace Common\Vendor\Workflow;

class workflow{	
	
	//实例ID		
	public function doActive($data){
		$ret = array('code'=>-1,'msg'=>'');
        do{
			//require_once 'work_config.php';
			$data['uid'] = $data['uid'];//用户id
			$data['wid'] = 17;//添加合同时候，选择哪种流程
			$data['c_id']='';//为空的时候,则添加新的实例
			if( $data['uid']=='' || $data['wid']==''){
				$ret['code'] = '-40';
				$ret['msg'] = '参数不全';
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
	
}
?>