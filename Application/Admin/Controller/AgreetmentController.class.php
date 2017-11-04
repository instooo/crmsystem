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
use \Common\Vendor\Workflow\messagelog;

class AgreetmentController extends CommonController {
    public $partnerConfig;
    public $moneylogType;

    public function _initialize() {
        parent::_initialize();
        $this->partnerConfig = include(CONF_PATH.'partner.config.php');
        $this->moneylogType = array(
            1   =>  array(0=>'未回款',1=>'已回款'),//回款计划
            2   =>  array(0=>'未开票',1=>'已开票'),//实际回款记录
            3   =>  array(0=>'未回款',1=>'已回款'),//开票记录
        );
        $this->assign('moneylogType', $this->moneylogType);
    }
	
	public function detail(){
		$cid = $_GET['cid'];
		$agree_id= $_GET['agree_id'];
		$detaidata = $this->get_form_info($agree_id,'agreement');
		
		if($cid=='' || $agree_id == ''){		
			echo "参数不全";die;
		}else{
			$nowuid = $this->get_numuid();	
			$workcase = new workflow();				
			$data['user'] =$nowuid;
			$data['work_case'] = $cid;			
			$result = $workcase->get_act($data);			
			$result['html']='';
			foreach($result['data'] as $key=>$val){
				$result['html'].="<input type='button' class='do btn btn-primary' act='".$val['action']."' value='".$val['des']."'/>";
			}
			//查找当前登录用户拥有的实例	
			$casedata['c_id'] =$cid;			
			$work_case = $workcase->onecase($casedata);
			
			//查找当前用户可修改字段
			$edit = $workcase->get_cedit($data);
			$editarr = array_column($edit,'field_name');	
			foreach($detaidata as $key=>$val){
				if(in_array($val['name'],$editarr)){
					$detaidata[$key]['edit']=1;					
				}else{
					$detaidata[$key]['edit']=0;
				}
			}
			//print_r($detaidata);die;
			
			
			$this->assign('edit',$edit);
			$this->assign('result',$result);	
			$this->assign('work_case',$work_case);

            //当前合同信息
            $agreeinfo = M('agreement a')
                ->field('a.*,u.id as uid,u.username,u.nickname')
                ->join('left join crm_user u on u.user_number=a.owner')
                ->where(array('a.id'=>$agree_id))->find();
            $this->assign('agreeinfo',$agreeinfo);
			
            //回款记录
            $moneylog = $this->getMoneyLog($agree_id);			
			$partdata = $this->get_form_info($agreeinfo['partner_id'],'partner');	
			
			//财务特殊账号
			$special_role = array(25);
			$usermap['user_id'] = $_SESSION['authId'];
			$userinfo = M('role_user')->where($usermap)->find();
			if(in_array($userinfo['role_id'],$special_role)){
				$is_caiwu = 1;
			}else{
				$is_caiwu = 0;
			}
			//后勤特殊账号
			$hq_role = array(22);
			if(in_array($userinfo['role_id'],$hq_role)){
				$is_hq = 1;
			}else{
				$is_hq = 0;
			}
			$this->assign('is_hq',$is_hq);
			$this->assign('is_caiwu',$is_caiwu);
			$this->assign('partdata',$partdata);
			$this->assign('detaidata',$detaidata);
            $this->assign('moneylogtree',$moneylog['moneylogtree']);
            $this->assign('all_plan_sum',$moneylog['all_plan_sum']);
            $this->assign('all_record_sum',$moneylog['all_record_sum']);
            $this->assign('all_invoice_sum',$moneylog['all_invoice_sum']);
            $this->assign('all_no_sum',$moneylog['all_no_sum']);
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
		if($result['code'] == 1){
			//发送消息
			$messagelog = new messagelog();	
			$result = $messagelog->addMessagelog($data);
		}
		exit(json_encode($result));
	}
	
	public function do_act(){		
		$data['cid'] = $_GET['cid'];
		$data['act'] = $_GET['act'];
		$workcase = new workflow();	
		$redata = $workcase->get_step_info($data);				
		
		$this->assign('redata',$redata);
		//查找下一步处理人
		$this->assign('cid',$data['cid']);
		$this->assign('act',$data['act']);
		$this->display();
	}

    /**
     * 查询回款记录
     * @param $agree_id
     * @return array
     */
    public function getMoneyLog($agree_id) {
        $moneylog = M('money_log a')
				->join('crm_user b on a.user_id=b.id')
				->field('a.*,b.*,a.id')
				->where(array('a.agree_id'=>$agree_id))
				->order('period asc,addtime asc')
				->select();  	
		$all_plan_sum=0;
		$moneylogtree = array();
        foreach ($moneylog as $val) {         
			$temp = $val;		
			$temp['pay_time']=date('Y-m-d',$val['finish_time']);			
			$temp['json'] = htmlspecialchars(json_encode($temp));
			$all_plan_sum += $val['money'];
			$moneylogtree[$val['period']] = $temp;
          
        }
        return array(
            'moneylogtree'  =>  $moneylogtree,
			 'all_plan_sum'  =>  $all_plan_sum
		);
    }

    /**
     * 添加回款记录
     */
    public function addMoneylog() {       
        $agree_id = intval(trim($_POST['agree_id']));
        if (!$agree_id) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'合同参数错误'), 'JSON');
        }
        $agreeinfo = M('agreement a')
            ->field('a.*,u.id as uid,u.username')
            ->join('left join crm_user u on u.user_number=a.owner')
            ->where(array('a.id'=>$agree_id))->find();
        if (!$agreeinfo) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'合同不存在'), 'JSON');
        }
        //查询所有回款期次
        $lmap = array(
            'agree_id'  =>  $agreeinfo['id'],           
        );
        $money_log = M('money_log');
        $periodinfo = M('money_log')->where($lmap)->order('period desc')->select();       
		$data = array();
        $data['user_id'] = $agreeinfo['uid'];
        $data['agree_id'] = $agreeinfo['id'];
        $data['addtime'] = time();
        $select_type = trim($_POST['select_type']);
        if ($select_type == 'record') {
            //添加回款记录          
            $data['money'] = $_POST['money'];
            $data['period'] = $periodinfo[0]['period']?$periodinfo[0]['period']+1:1;
            $data['type'] = 2;
			if($agreeinfo['type']==0){
			    $data['finish_time'] = strtotime($_POST['pay_time']); 
				$data['month'] = date('Ym',$data['finish_time']);			
			}else{
				 $data['finish_time'] = strtotime($_POST['pay_time']."01"); 
				 $data['month'] = date('Ym',$data['finish_time']);		
			}
          	
            $data['pay_type'] = $_POST['pay_type'];
            $data['remarks'] = trim($_POST['remarks']);
		
            if (!is_numeric($data['money']) || !is_numeric($data['period']) || !$data['finish_time'] || !$data['pay_type']) {
                $this->ajaxReturn(array('code'=>-5,'msg'=>'请填写完整的数据'), 'JSON');
            }
			if ($data['money']<=0) {
                $this->ajaxReturn(array('code'=>-5,'msg'=>'金额不能为负数'), 'JSON');
            }
			if (($agreeinfo['total_money']-$agreeinfo['return_money'])<$data['money'] && $agreeinfo['type']==0) {
                $this->ajaxReturn(array('code'=>-5,'msg'=>'超出了合同总金额'), 'JSON');
            }			
            $return_money = $data['money'];
        }else {
            $this->ajaxReturn(array('code'=>-6,'msg'=>'回款类型未知'), 'JSON');
        }
        // 长期结算批量回款
        $dataAll = array();
        if ($data['pay_type'] == '长期结算') {
            $pay_time_start = $_POST['pay_time_start'];
            if (!$pay_time_start) {
                $this->ajaxReturn(array('code'=>-7,'msg'=>'请输入开始月份'), 'JSON');
            }
            $start = strtotime($pay_time_start.'01');
            $t = $start;
            while ($t <= $data['finish_time']) {
                $temp = $data;
                $temp['money'] = 0;
                $temp['month'] = date('Ym', $t);
                if ($temp['month'] == $data['month']) break;
                $dataAll[] = $temp;
                $t = strtotime('+1 month', $t);
            }
        }
        $dataAll[] = $data;
        //$rs = $money_log->add($data);
        $rs = $money_log->addAll($dataAll);
        if (!$rs) {
            $this->ajaxReturn(array('code'=>-500,'msg'=>'数据保存失败'), 'JSON');
        }

        if ($return_money) {
            //更新回款金额
            M('agreement')->where(array('id'=>$agree_id))->setInc('return_money', $return_money);
        }
        $data['id'] = $rs;
        $data['json'] = htmlspecialchars(json_encode($data));
        //返回最新回款数据
        $sum_data = $this->getMoneyLog($data['agree_id']);
        $info_data = $data;
        $return_data = array('sum_data'=>$sum_data,'info_data'=>$info_data);
        $this->ajaxReturn(array('code'=>1,'msg'=>'保存成功','return_data'=>$return_data), 'JSON');
    }

    /**
     * 编辑回款记录
     */
    public function editMoneylog() {
        $logid = intval($_POST['logid']);
        if (!$logid) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数错误'), 'JSON');
        }
        $money_log = M('money_log');
        $loginfo = $money_log->where(array('id'=>$logid))->find();
        if (!$loginfo) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'要修改的记录不存在'), 'JSON');
        }
        $data = array();
        
		//实际回款记录
		$data['money'] = $_POST['money'];
		$data['finish_time'] = strtotime($_POST['pay_time']);	
		$data['pay_type'] = $_POST['pay_type'];
		$data['remarks'] = trim($_POST['remarks']);
		if (!is_numeric($data['money']) || !$data['finish_time']) {
			$this->ajaxReturn(array('code'=>-3,'msg'=>'请填写完整的数据'), 'JSON');
		}
		$return_money = $data['money'];
		$agreeinfo = M('agreement')->where(array('id'=>$loginfo['agree_id']))->find();
        if (($agreeinfo['total_money']-$agreeinfo['return_money'])<$return_money-$loginfo['money'] ) {
            $this->ajaxReturn(array('code'=>-5,'msg'=>'超出了合同总金额'), 'JSON');
        }			
			
			
        $rs = $money_log->where(array('id'=>$logid))->save($data);
        if (false === $rs) {
            $this->ajaxReturn(array('code'=>-500,'msg'=>'数据保存失败'), 'JSON');
        }		
        if ($return_money) {
            //更新回款金额
            M('agreement')->where(array('id'=>$loginfo['agree_id']))->setDec('return_money', $loginfo['money'] - $return_money);
        }
        $loginfo = array_merge($loginfo, $data);
        $loginfo['json'] = htmlspecialchars(json_encode($loginfo));
        //返回最新回款数据
        $sum_data = $this->getMoneyLog($loginfo['agree_id']);
        $info_data = $loginfo;
        $return_data = array('sum_data'=>$sum_data,'info_data'=>$info_data);
        $this->ajaxReturn(array('code'=>1,'msg'=>'保存成功','return_data'=>$return_data), 'JSON');
    }

    /**
     * 删除回款记录
     */
    public function deleMoneylog() {
        $logid = intval($_POST['logid']);
        if (!$logid) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数错误'), 'JSON');
        }
        $money_log = M('money_log');
        $loginfo = $money_log->where(array('id'=>$logid))->find();
        if (!$loginfo) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'要删除的记录不存在'), 'JSON');
        }
        $map = array();
        if ($loginfo['type'] == 0) {
            //删除总期次
            $map['period'] = $loginfo['period'];
        }else {
            $map['id'] = $logid;
        }
        $rs = $money_log->where($map)->delete();
        if (false === $rs) {
            $this->ajaxReturn(array('code'=>-500,'msg'=>'数据删除失败'), 'JSON');
        }
        //更新回款金额
        M('agreement')->where(array('id'=>$loginfo['agree_id']))->setDec('return_money', $loginfo['money']);
        //返回最新回款数据
        $sum_data = $this->getMoneyLog($loginfo['agree_id']);
        $info_data = $loginfo;
        $return_data = array('sum_data'=>$sum_data,'info_data'=>$info_data);
        $this->ajaxReturn(array('code'=>1,'msg'=>'数据删除成功','return_data'=>$return_data), 'JSON');
    }
	
}
