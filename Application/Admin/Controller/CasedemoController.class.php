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
		if($type == 'done'){//完成的实例
			$map['c_create_uid'] = $nowuid;
			$map['c_state'] = 2;
			$result = M('work_case a')			
			->where($map)			
			->select();
		}else if($type == 'doing'){//正在进行的实例
			$map['a.c_create_uid'] = $nowuid;
			$map['a.c_state'] = 1;
			$result = M('work_case a')
			->field("a.c_id,distinct('b.c_id')")
			->join('crm_work_case_log b on b.c_id=a.c_id')
			->where($map)
			->order('b.step desc')
			->select();
			print_r( M('work_case a')->getLastSql());die;
		}
		
		$this->assign('list',$result);
		$this->display();
	}

	public function act(){
		if(IS_POST){
			$data['user'] = $_POST['user'];
			$data['work_case'] = $_POST['work_case'];
			$workcase = new workflow();	
			$result = $workcase->get_act($data);
			$result['html']='';
			foreach($result['data'] as $key=>$val){
				$result['html'].="<input type='button' class='do' act='".$val['action']."' value='".$val['des']."'/>";
			}
			exit(json_encode($result));
			
		}
		$user = M('user')->select();			
		$this->assign('user',$user);
		
		$workflow = M('workflow')->select();			
		$this->assign('workflow',$workflow);
		
		
		//查找当前登录用户
		$work_case = M('work_case')->select();
		$this->assign('work_case',$work_case);				
		$this->display();
	}
	
	//涉及走工作流的,添加实例
    public function add_case()
    {	
		$data['uid'] = $_POST['user'];
		$data['wid'] = $_POST['workflow'];
		$data['title'] = $_POST['htname'];		
		$workcase = new workflow();	
		$workcase->doActive($data);
    }

	//获取流程状态
    public function step_go()
    {
		$data['uid'] = $_POST['user'];
		$data['c_id'] = $_POST['work_case'];
		$data['act'] = $_POST['act'];		
		$workcase = new workflow();	
		$result = $workcase->doStep($data);
	}
}