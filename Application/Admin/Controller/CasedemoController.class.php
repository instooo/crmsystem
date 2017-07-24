<?php
/**
 * 控制器
 * Created by dengxiaolong
 * Date: 2017/6/22
 */
namespace Admin\Controller;
use Think\Controller;
use \Common\Vendor\Workflow\workflow;
class CasedemoController extends CommonController {	
	public function  _initialize(){			
		$nowuid = $this->get_numuid();			
		$usermap['user_number'] = $nowuid ;	
		$userinfo = M('user')->where($usermap)->find();
		$this->assign('userinfo',$userinfo);
	}
	//获取当前账号
	private function get_numuid(){
		if($_SESSION['tem_num']){
			//查找当前用户
			$nowuid = $_SESSION['tem_num'];			
		}else if($_SESSION['authId']){
			//查找当前用户
			$nowuid = $_SESSION['authId'];
		}else{
			echo "未登录";die;
		}
		return $nowuid;
	}
	
	//切换当前账号
	public function index(){	
		if(IS_POST){			
			$_SESSION['tem_uid'] = 's'.$_POST['user'].'n';
			$_SESSION['tem_num'] = $_POST['user'];
			//查找当前用户
			$usermap['user_number'] = $_POST['user'];
			$result = M('user')->where($usermap)->find();
			exit(json_encode($result));			
		}else{
			$nowuid = $this->get_numuid();			
			$usermap['user_number'] = $nowuid ;	
			$userinfo = M('user')->where($usermap)->find();
			$this->assign('userinfo',$userinfo);
			$user = M('user')->select();			
			$this->assign('user',$user);
			$this->display();
		}
	}
	//查看当前用户拥有的合同
	public function case_list(){		
		$nowuid = $this->get_numuid();	
		//查找所拥有的合同实例
		$type=$_GET['type'];
		$type=$type?$type:'mine';		
		$data['user'] =$nowuid;
		$data['type'] = $type;
		$workcase = new workflow();	
		$result = $workcase->caselist($data);
		$this->assign('list',$result['data']);
		$this->display();
	}
	
	//查看当前合同具体信息
	public function onecase(){
		$cid = $_GET['cid'];
		if($cid==''){
			echo "参数不全";die;
		}else{
			$nowuid = $this->get_numuid();	
			$workcase = new workflow();				
			$data['user'] =$nowuid;
			$data['work_case'] = $cid;			
			$result = $workcase->get_act($data);			
			$result['html']='';
			foreach($result['data'] as $key=>$val){
				$result['html'].="<input type='button' class='do' act='".$val['action']."' value='".$val['des']."'/>";
			}
			//查找当前登录用户拥有的实例	
			$casedata['c_id'] =$cid;			
			$work_case = $workcase->onecase($casedata);

			
			$this->assign('result',$result);	
			$this->assign('work_case',$work_case);	
		}
		$this->display();
	}
	//获取当前实例的操作
	public function act(){
		$nowuid = $this->get_nowuid();	
		$workcase = new workflow();	
		if(IS_POST){			
			$data['user'] =$nowuid;
			$data['work_case'] = $_POST['work_case'];			
			$result = $workcase->get_act($data);			
			$result['html']='';
			foreach($result['data'] as $key=>$val){
				$result['html'].="<input type='button' class='do' act='".$val['action']."' value='".$val['des']."'/>";
			}
			exit(json_encode($result));
			
		}
		//查找当前登录用户拥有的实例	
		$casedata['user'] =$nowuid;
		$work_case = $workcase->caselist($casedata);
		$this->assign('work_case',$work_case['data']);				
		$this->display();
	}
	
	
	//添加合同和实例
	public function addcase(){
		if(IS_POST){			
			$data['uid'] = $this->get_numuid();	
			$data['wid'] = $_POST['workflow'];
			$data['title'] = $_POST['htname'];				
			$workcase = new workflow();	
			$workcase->addCase($data);
		}
		$workflow = M('workflow')->select();			
		$this->assign('workflow',$workflow);
		$this->display();
	}

	//获取流程状态
    public function step_go()
    {
		$data['uid'] = $this->get_numuid();	
		$data['c_id'] = $_POST['work_case'];
		$data['act'] = $_POST['act'];		
		$workcase = new workflow();	
		$result = $workcase->doStep($data);
		exit(json_encode($result));
	}
}