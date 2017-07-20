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