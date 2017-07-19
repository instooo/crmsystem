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
	public function index(){
		if(IS_POST){
			$data['user'] = $_POST['user'];
			$data['work_case'] = $_POST['work_case'];
			$workcase = new workflow();	
			$result = $workcase->get_act($data);
			print_r($result);die;
			
		}
		$user = M('user')->select();			
		$this->assign('user',$user);
		//查找当前登录用户
		$work_case = M('work_case')->select();
		$this->assign('work_case',$work_case);				
		$this->display();
	}
	
	//涉及走工作流的,添加实例
    public function add_case()
    {	
		$data['uid'] = $_SESSION['authId'];//用户id
		$data['wid'] = 17;//添加合同时候，选择哪种流程
		$data['c_id']='';//为空的时候,则添加新的实例
		$data['title']='测试合同名称';//为空的时候,则添加新的实例
		$workcase = new workflow();	
		$workcase->doActive($data);
    }

	//获取流程状态
    public function step_go()
    {
		$data['uid'] = $_SESSION['authId'];//用户id		
		$data['c_id']= 1;//为空的时候,则添加新的实例		
		$workcase = new workflow();	
		$workcase->doStep($data);
	}
}