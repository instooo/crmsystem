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
use \Common\Vendor\Workflow\messagelog;

class MessageController extends CommonController {
	
	//我的消息列表
	public function message_list(){
		$messagelog = new messagelog();	
		$list = $messagelog->get_message_list();		
		$this->assign('list',$list);
		$this->display();
	}
	
	public function replay(){		
		if(IS_POST){
			//在日志表里查找这个信息是否存在
			$map['log_id'] =$_POST['logid'];			
			$uid = $this->get_numuid();			
			$result = M('work_case_log')->where($map)->find();
			
			$map['log_id'] =$_POST['newlogid'];			
			$uid = $this->get_numuid();			
			$result_new = M('work_case_log')->where($map)->find();

			if($result){
				$logdata['c_id']=$result['c_id'];
				$logdata['w_id']=$result['w_id'];			
				$logdata['step']=$result['step'];
				$logdata['pid']	=$result['log_id'];
				$logdata['uid']	=$uid;		
				$logdata['re_uid']	=$result_new['uid'];
				$logdata['act_id']=1;
				$logdata['create_time']=time();
				$logdata['des']='';
				$logdata['status']=1;
				$logdata['comment']=$_POST['comment'];
				
				$workcase = new caselog();	
				$workcase->addCaselog($logdata);	

				//发送消息
				$data['uid'] = $this->get_numuid();	
				$data['c_id'] = $result['c_id'];					
				$data['comment'] = $_POST['comment'];				
				$data['reuid'] = $result_new['uid'];
				
				$messagelog = new messagelog();	
				$result = $messagelog->addMessagelog($data);
				
								
				$redata['state']=1;
				$redata['msg']=1;
				exit(json_encode($redata));
			}else{
				
			}
		}else{
			$logid = $_GET['logid'];
			$newlogid =$_GET['newlogid'];						
			if($logid=='' || $newlogid ==''){
				echo "参数不全";die;
			}
			$this->assign('logid',$logid);	
			$this->assign('newlogid',$newlogid);	
			$this->display();
		}
		
	}

	//读取消息
	public function readmessage(){
		$id = $_POST['id'];		
		$messagelog = new messagelog();	
		$list = $messagelog->readmessage($id);	
	}
}
