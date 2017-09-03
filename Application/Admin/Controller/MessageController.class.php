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
use \Common\Vendor\Workflow\partnerlog;

class MessageController extends CommonController {
	
	//我的消息列表
	public function message_list(){
		$type = $_GET['type'];
		$messagelog = new messagelog();	
		$list = $messagelog->get_message_list($type);	
		$this->assign('list',$list);
		$this->assign('type',$type);
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
	
	//给指定人发消息
	public function replay_uid(){
		if($_POST){		
			$data['uid'] = $this->get_numuid();	
			$data['c_id'] = $_POST['cid'];		
			$data['comment'] = $_POST['comment'];			
			$data['reuid'] = $_POST['reuid'];			
			
			//查找当前到了哪一步			
			$stepmap['c_id']=$data['c_id'];			
			//一个步骤当中，可能有不通过情况
			$stepresult  = M('work_case_step')->where($stepmap)->order('st_id desc')->find();	

			//添加日志			
			$logdata['c_id']=$data['c_id'];
			$logdata['w_id']=$stepresult['w_id'];	
			$logdata['step']	=$stepresult['step'];
			$logdata['uid']	=$data['uid'];//用户id		
			$logdata['act_id']=1;				
			$logdata['des']=$_POST['comment'];
			$logdata['status']=1;
			$logdata['comment']=$data['comment'];					
			$workcase = new caselog();	
			$workcase->addCaselog($logdata);
			
			//发送消息
			$messagelog = new messagelog();	
			$result = $messagelog->addMessagelog($data);			
			exit(json_encode($result));	
		}else{
			//查找下一步处理人
			$this->assign('comment',$_GET['comment']);
			$this->assign('cid',$_GET['cid']);		
			$this->display();		
		}
		
	}
	
	//给指定人发消息
	public function replay_partner_uid(){
		if(IS_POST){				
			$map['log_id'] =$_POST['newlogid'];				
			$result_new = M('work_partner_log')->where($map)->find();
			if($_POST['newlogid']){
				$data['uid'] = $this->get_numuid();	
				$data['pid'] =$result_new['log_id'];
				$data['p_id'] =$result_new['p_id'];
				$data['c_id'] ="p_".$result_new['p_id'];
				$data['comment'] = $_POST['comment'];			
				$data['reuid'] = $result_new['uid'];
				$data['type'] = 'partner';
				
				$partnerlog = new partnerlog();	
				$partnerlog->addPartnerlog($data);
				
				//发送消息
				$messagelog = new messagelog();	
				$result = $messagelog->addMessagelog($data);			
				exit(json_encode($result));		
			}else{
				//添加日志			
				$logdata['p_id']=$_POST['p_id'];		
				$logdata['uid']	=$this->get_numuid();	
				$logdata['pid']	=$_POST['pid']?$_POST['pid']:0;
				$logdata['reuid']	=$_POST['reuid']?$_POST['reuid']:$this->get_numuid();	
				$logdata['create_time']=time();			
				$logdata['comment']=$_POST['comment'];				
				$partnerlog = new partnerlog();	
				$partnerlog->addPartnerlog($logdata);
				
				$data['uid'] = $this->get_numuid();	
				$data['c_id'] ="p_".$_POST['p_id'];		
				$data['comment'] = $_POST['comment'];			
				$data['reuid'] = $_POST['reuid']?$_POST['reuid']:$this->get_numuid();
				$data['type'] = 'partner';
				
				//发送消息
				$messagelog = new messagelog();	
				$result = $messagelog->addMessagelog($data);			
				exit(json_encode($result));		
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

}
