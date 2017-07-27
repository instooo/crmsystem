<?php
/**
 * 控制器
 * Created by qinfan qf19910623@gmail.com.
 * Date: 2017/6/30
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Model;
use Think\Think;
use \Common\Vendor\Workflow\workflow;
use \Common\Vendor\Workflow\caselog;

class MessageController extends CommonController {
	
	public function replay(){		
		if(IS_POST){
			//在日志表里查找这个信息是否存在
			$map['log_id'] =$_POST['logid'];			
			$uid = $this->get_numuid();			
			$result = M('work_case_log')->where($map)->find();
			if($result){
				$logdata['c_id']=$result['c_id'];
				$logdata['w_id']=$result['w_id'];			
				$logdata['step']=$result['step'];
				$logdata['pid']	=$result['log_id'];
				$logdata['uid']	=$result['uid'];
				$logdata['re_uid']	=$uid;		
				$logdata['act_id']=1;
				$logdata['create_time']=time();
				$logdata['des']='';
				$logdata['status']=1;
				$logdata['comment']=$_POST['comment'];	
				$workcase = new caselog();	
				$result = $workcase->addCaselog($logdata);
				if($result){
					$redata['state']=1;
					$redata['msg']=1;
				}
				exit(json_encode($redata));
			}else{
				
			}
		}else{
			$logid = $_GET['logid'];			
			if($logid==''){
				echo "参数不全";die;
			}
			$this->assign('logid',$logid);			
			$this->display();
		}
		
	}

}
