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
		$data['uid'] = $this->get_numuid();	
		$data['c_id'] = $_POST['work_case'];
		$data['act'] = $_POST['act'];
		$data['comment'] = $_POST['comment'];		
		$workcase = new workflow();	
		$result = $workcase->doStep($data);
		exit(json_encode($result));
	}
	
	public function do_act(){
		$cid = $_GET['cid'];
		$act = $_GET['act'];
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
		if($act=="nopass"){
			unset($map);
			$nowuid = $this->get_numuid();				
			$map['user_number'] = $nowuid;
			$nowuser = M('user')->where($map)->find();
			
			$redata['nowuser'] = $nowuser['nickname'] ;
			$redata['step'] = $result['step']+1 ;				
			$redata['flag'] = 'nopass';
		}else{		
			unset($map);
			if($result['step']==-1){//提交审核
				$map['w_id']=$result['w_id'];
				$next = M('workflow_extend')->where($map)->order('e_id asc')->find();
				//查找下一步的处理人员
				unset($map);
				$uidarr = explode('|',$next['uid']);
				$map['user_number'] = array('in',$uidarr);
				$user = M('user')->where($map)->select();
				
				$redata['nowuser'] = $result['nickname'] ;			
				$redata['step'] = $result['step'] ;
				$redata['stepdes'] = "草稿" ;
				$redata['des'] = $next['des'];
				$redata['nextuser'] = $user;
				$redata['flag'] = 'first';				
			}else{
				unset($map);
				$nowuid = $this->get_numuid();				
				$map['user_number'] = $nowuid;
				$nowuser = M('user')->where($map)->find();
				//查找最大步骤
				unset($map);
				$map['w_id']=$result['w_id'];
				$max = M('workflow_extend')->where($map)->order('step_id desc')->find();
				if(($result['step']+1) >=$max['step_id'] )
				{
					$redata['nowuser'] = $nowuser['nickname'] ;
					$redata['step'] = $result['step']+1 ;				
					$redata['flag'] = 'last';	
				}else{
					//查找下一步处理人
					unset($map);
					$map['step_id']=$result['step']+2;
					$map['w_id']=$result['w_id'];
					$next = M('workflow_extend')->where($map)->find();
					unset($map);
					$uidarr = explode('|',$next['uid']);
					$map['user_number'] = array('in',$uidarr);
					$user = M('user')->where($map)->select();
					//查找当前人				
					$redata['nextuser'] = $user;
					$redata['des'] = $next['des'];
					$redata['step'] = $result['step']+1 ;
					$redata['nowuser'] = $nowuser['nickname'] ;	
					$redata['flag'] = 'middle';	
				}
			
			}	
		}
		
		$this->assign('redata',$redata);
		//查找下一步处理人
		$this->assign('cid',$cid);
		$this->assign('act',$act);
		$this->display();
	}
}
