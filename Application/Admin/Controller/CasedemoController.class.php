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
		$nowuid = $this->get_nowuid();			
		$usermap['id'] = $nowuid ;	
		$userinfo = M('user')->where($usermap)->find();
		$this->assign('userinfo',$userinfo);
	}
	//获取当前账号
	private function get_nowuid(){
		if($_SESSION['tem_uid']){
			//查找当前用户
			$nowuid = $_SESSION['tem_uid'];			
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
			$_SESSION['tem_uid'] = $_POST['user'];
			//查找当前用户
			$usermap['id'] = $_POST['user'];
			$result = M('user')->where($usermap)->find();
			exit(json_encode($result));			
		}else{
			$nowuid = $this->get_nowuid();			
			$usermap['id'] = $nowuid ;	
			$userinfo = M('user')->where($usermap)->find();
			$this->assign('userinfo',$userinfo);
			$user = M('user')->select();			
			$this->assign('user',$user);
			$this->display();
		}
	}
	//查看当前用户拥有的合同
	public function case_list(){		
		$nowuid = $this->get_nowuid();	
		//查找所拥有的合同实例
		$type=$_GET['type'];
		$type=$type?$type:'done';		
		$data['user'] =$nowuid;
		$data['type'] = $type;
		$workcase = new workflow();	
		$result = $workcase->caselist_detail($data);
		$this->assign('list',$result['data']);
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
			$data['uid'] = $this->get_nowuid();	
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
		$data['uid'] = $this->get_nowuid();	
		$data['c_id'] = $_POST['work_case'];
		$data['act'] = $_POST['act'];				
		$workcase = new workflow();	
		$result = $workcase->doStep($data);
		exit(json_encode($result));
	}
}