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

class AgreetmentController extends CommonController {
    public $partnerConfig;

    public function _initialize() {
        parent::_initialize();
        $this->partnerConfig = include(CONF_PATH.'partner.config.php');
    }
	
	public function detail(){
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

	//获取流程状态
    public function step_go()
    {
		//print_r($_POST);DIE;
		$data['uid'] = $this->get_numuid();	
		$data['c_id'] = $_POST['work_case'];
		$data['act'] = $_POST['act'];
		$data['comment'] = $_POST['comment'];
		$data['nextuid'] = $_POST['nextuid'];	
		$data['reuid'] = $_POST['reuid'];	
		$workcase = new workflow();	
		$result = $workcase->doStep($data);
		exit(json_encode($result));
	}
	
	public function do_act(){
		$cid = $_GET['cid'];
		$act = $_GET['act'];
		$data['cid'] = $_GET['cid'];
		$data['act'] = $_GET['act'];
		$workcase = new workflow();	
		$redata = $workcase->get_step_info($data);
		
		
		if($cid=='' || $act=='' ){
			echo "参数不全";die;
		}
		//查找当前实例是否存在		
		$map['c_id']=$cid;
		$result = M('work_case a')
			->join('crm_user b on a.c_create_uid = b.user_number')			
			->where($map)
			->find();
		if(!$result){
			die;
		}		
		
		
		$this->assign('redata',$redata);
		//查找下一步处理人
		$this->assign('cid',$cid);
		$this->assign('act',$act);
		$this->display();
	}
}
