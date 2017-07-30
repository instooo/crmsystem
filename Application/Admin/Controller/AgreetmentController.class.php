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
				$result['html'].="<input type='button' class='do' act='".$val['action']."' value='".$val['des']."'/>";
			}
			//查找当前登录用户拥有的实例	
			$casedata['c_id'] =$cid;			
			$work_case = $workcase->onecase($casedata);

			
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
        $moneylog = M('money_log')->where(array('agree_id'=>$agree_id))->order('period asc,addtime asc')->select();
        $moneylogtree = array();
        $all_plan_sum = 0;
        $all_record_sum = 0;
        $all_invoice_sum = 0;
        foreach ($moneylog as $val) {
            if ($val['type'] == 0) {
                $temp = $val;
                $temp['plan_sum'] = 0;
                $temp['record_sum'] = 0;
                $temp['invoice_sum'] = 0;
                $temp['json'] = htmlspecialchars(json_encode($val));
                $moneylogtree[$val['period']] = $temp;
            }else {
                $temp = $val;
                if ($val['type'] == 1) {
                    $moneylogtree[$val['period']]['plan_sum'] += $val['money'];
                    $all_plan_sum += $val['money'];
                }elseif ($val['type'] == 2) {
                    $moneylogtree[$val['period']]['record_sum'] += $val['money'];
                    $all_record_sum += $val['money'];
                }elseif ($val['type'] == 3) {
                    $moneylogtree[$val['period']]['invoice_sum'] += $val['money'];
                    $all_invoice_sum += $val['money'];
                }
                $temp['json'] = htmlspecialchars(json_encode($temp));
                $period = $moneylogtree[$val['period']];
                unset($period['child']);
                $moneylogtree[$val['period']]['json'] = htmlspecialchars(json_encode($period));
                $moneylogtree[$val['period']]['child'][] = $temp;
            }
        }
        $all_no_sum = $all_plan_sum - $all_record_sum;
        $all_no_sum = $all_no_sum<0?0:$all_no_sum;
        return array(
            'moneylogtree'  =>  $moneylogtree,
            'all_plan_sum'  =>  $all_plan_sum,
            'all_record_sum'  =>  $all_record_sum,
            'all_invoice_sum'  =>  $all_invoice_sum,
            'all_no_sum'  =>  $all_no_sum
        );
    }

    /**
     * 添加回款记录
     */
    public function addMoneylog() {
        /*
        $data = M('money_log')->where(array('id'=>9))->find();
        $data['id'] = 9;
        $data['json'] = htmlspecialchars(json_encode($data));
        //返回最新回款数据
        $summap = array();
        $summap['period'] = array('in', array(1,2,3));
        $summap['agree_id'] = 1;
        $sum_data = M('money_log')->field('sum(money) as sum_money,period')->where($summap)->group('period')->order('period asc')->select();
        $sum_data = array_column($sum_data, 'sum_money', 'period');
        $info_data = $data;
        $this->ajaxReturn(array('code'=>1,'msg'=>'保存成功','sum_data'=>$sum_data,'info_data'=>$info_data), 'JSON');
        */
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
            'type'  =>  0
        );
        $money_log = M('money_log');
        $periodinfo = M('money_log')->where($lmap)->order('period desc')->select();
        $data = array();
        $data['user_id'] = $agreeinfo['uid'];
        $data['agree_id'] = $agreeinfo['id'];
        $data['addtime'] = time();
        $select_type = trim($_POST['select_type']);
        if ($select_type == 'summary') {
            //添加回款期次
            $data['money'] = 0;
            $data['period'] = $periodinfo[0]['period']?$periodinfo[0]['period']+1:1;
            $data['type'] = 0;
            $data['status'] = 0;
        }elseif ($select_type == 'plan') {
            //添加回款计划
            if (!$periodinfo) {
                $this->ajaxReturn(array('code'=>-3,'msg'=>'该回款期次不存在'), 'JSON');
            }
            $data['money'] = $_POST['money'];
            $data['period'] = $_POST['period'];
            $data['type'] = 1;
            $data['plan_time'] = strtotime($_POST['plan_time']);
            $data['status'] = $_POST['status'];
            $data['remarks'] = trim($_POST['remarks']);
            if (!is_numeric($data['money']) || !is_numeric($data['period']) || !$data['plan_time'] || !is_numeric($data['status'])) {
                $this->ajaxReturn(array('code'=>-5,'msg'=>'请填写完整的数据'), 'JSON');
            }

            $planlog = $money_log->where(array('agree_id'=>$agreeinfo['id'],'type'=>1,'period'=>$data['period']))->find();
            if ($planlog) {
                $this->ajaxReturn(array('code'=>-4,'msg'=>'已经添加过回款计划'), 'JSON');
            }

            //更新回款总额
            $save = array();
            $save['money'] = $data['money'];
            $money_log->where(array('agree_id'=>$agreeinfo['id'],'type'=>0))->save($save);
        }elseif ($select_type == 'record') {
            //添加回款记录
            if (!$periodinfo) {
                $this->ajaxReturn(array('code'=>-3,'msg'=>'该回款期次不存在'), 'JSON');
            }
            $data['money'] = $_POST['money'];
            $data['period'] = $_POST['period'];
            $data['type'] = 2;
            $data['finish_time'] = strtotime($_POST['pay_time']);
            $data['status'] = $_POST['status'];
            $data['pay_type'] = $_POST['pay_type'];
            $data['remarks'] = trim($_POST['remarks']);
            if (!is_numeric($data['money']) || !is_numeric($data['period']) || !$data['finish_time'] || !is_numeric($data['status']) || !$data['pay_type']) {
                $this->ajaxReturn(array('code'=>-5,'msg'=>'请填写完整的数据'), 'JSON');
            }
        }elseif ($select_type == 'invoice') {
            //添加开票记录
            if (!$periodinfo) {
                $this->ajaxReturn(array('code'=>-3,'msg'=>'该回款期次不存在'), 'JSON');
            }
            $data['money'] = $_POST['money'];
            $data['period'] = $_POST['period'];
            $data['type'] = 3;
            $data['finish_time'] = strtotime($_POST['invoice_time']);
            $data['status'] = $_POST['status'];
            $data['invoice_title'] = trim($_POST['invoice_title']);
            $data['invoice_type'] = trim($_POST['invoice_type']);
            $data['invoice_num'] = trim($_POST['invoice_num']);
            $data['remarks'] = trim($_POST['remarks']);
            if (!is_numeric($data['money'])
                || !is_numeric($data['period'])
                || !$data['finish_time']
                || !is_numeric($data['status'])
                || !$data['invoice_title']
                || !$data['invoice_type']
                || !$data['invoice_num']) {
                $this->ajaxReturn(array('code'=>-5,'msg'=>'请填写完整的数据'), 'JSON');
            }
        }else {
            $this->ajaxReturn(array('code'=>-6,'msg'=>'回款类型未知'), 'JSON');
        }
        $rs = $money_log->add($data);
        if (!$rs) {
            $this->ajaxReturn(array('code'=>-500,'msg'=>'数据保存失败'), 'JSON');
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
        if ($loginfo['type'] == 1) {
            //回款计划
            $data['money'] = $_POST['money'];
            $data['plan_time'] = strtotime($_POST['plan_time']);
            $data['status'] = $_POST['status'];
            $data['remarks'] = trim($_POST['remarks']);
            if (!is_numeric($data['money']) || !$data['plan_time'] || !is_numeric($data['status'])) {
                $this->ajaxReturn(array('code'=>-3,'msg'=>'请填写完整的数据'), 'JSON');
            }
        }elseif ($loginfo['type'] == 2) {
            $data['money'] = $_POST['money'];
            $data['finish_time'] = strtotime($_POST['pay_time']);
            $data['status'] = $_POST['status'];
            $data['pay_type'] = $_POST['pay_type'];
            $data['remarks'] = trim($_POST['remarks']);
            if (!is_numeric($data['money']) || !$data['finish_time'] || !is_numeric($data['status']) || !$data['pay_type']) {
                $this->ajaxReturn(array('code'=>-3,'msg'=>'请填写完整的数据'), 'JSON');
            }
        }elseif ($loginfo['type'] == 3) {
            $data['money'] = $_POST['money'];
            $data['finish_time'] = strtotime($_POST['invoice_time']);
            $data['status'] = $_POST['status'];
            $data['invoice_title'] = trim($_POST['invoice_title']);
            $data['invoice_type'] = trim($_POST['invoice_type']);
            $data['invoice_num'] = trim($_POST['invoice_num']);
            $data['remarks'] = trim($_POST['remarks']);
            if (!is_numeric($data['money'])
                || !$data['finish_time']
                || !is_numeric($data['status'])
                || !$data['invoice_title']
                || !$data['invoice_type']
                || !$data['invoice_num']) {
                $this->ajaxReturn(array('code'=>-3,'msg'=>'请填写完整的数据'), 'JSON');
            }
        }else {
            $this->ajaxReturn(array('code'=>-4,'msg'=>'回款类型未知'), 'JSON');
        }
        $rs = $money_log->where(array('id'=>$logid))->save($data);
        if (false === $rs) {
            $this->ajaxReturn(array('code'=>-500,'msg'=>'数据保存失败'), 'JSON');
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
        //返回最新回款数据
        $sum_data = $this->getMoneyLog($loginfo['agree_id']);
        $info_data = $loginfo;
        $return_data = array('sum_data'=>$sum_data,'info_data'=>$info_data);
        $this->ajaxReturn(array('code'=>1,'msg'=>'数据删除成功','return_data'=>$return_data), 'JSON');
    }
}
