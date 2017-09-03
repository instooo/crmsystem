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

class WorkController extends CommonController {
    public $partnerConfig;

    public function _initialize() {
        parent::_initialize();
        $this->partnerConfig = include(CONF_PATH.'partner.config.php');
    }

    /**
     * 客户管理
     */
    public function partner() {
		//查找自己拥有客户
		$nowuid = $this->get_numuid();
        $partners = $this->getUserPartner($_SESSION['authId']);
		$map['p.id'] = array('in', $partners);
		
        $fieldlist = $this->getFieldList('partner');
        $klist = array_keys($fieldlist);

        $count = M('partner p')
            ->field('p.*,u.id,u.nickname')
			->where($map)
            ->join('left join crm_user u on u.user_number=p.owner')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('partner p')
            ->field('p.*,u.id as user_id,u.nickname')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->order('p.id desc')
			->where($map)
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();

        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['partner_name'] = $val['partner_name'];
            $tmp['nickname'] = $val['nickname'];
            $tmp['addtime'] = $val['addtime'];
            $list[] = $tmp;
        }

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        $this->display();
    }


    /**
     * 添加客户
     */
    public function addPartner() {
        $this->addData();
    }

    /**
     * 编辑客户
     */
    public function editPartner() {
        $this->editData();
    }

    /**
     * 删除客户
     */
    public function delePartner() {
        $this->deleData();
    }



    /**
     * 联系人管理
     */
    public function contact() {
        $fieldlist = $this->getFieldList('contact');
        $klist = array_keys($fieldlist);

        //查询客户
        $partners = $this->getUserPartner($_SESSION['authId']);
        $pmap['id'] = array('in', $partners);
        $partnerlist = M('partner')->where($pmap)->select();
        $this->assign('partnerlist', $partnerlist);

        if ($_REQUEST['s_partner_id']) {
            $map['p.partner_id'] = $_REQUEST['s_partner_id'];
            $this->assign('s_partner_id', $_REQUEST['s_partner_id']);
        }else {
            $map['p.partner_id'] = array('in', $partners);
        }

        if ($_REQUEST['s_partner_id']) {
            $map['p.partner_id'] = $_REQUEST['s_partner_id'];
            $this->assign('s_partner_id', $_REQUEST['s_partner_id']);
        }else {
            $map['p.partner_id'] = array('in', $partners);
        }

        $count = M('contact p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->where($map)
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('contact p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->where($map)
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();

        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['partner_id'] = $val['partner_id'];
            $tmp['partner_name'] = $val['partner_name'];
            $tmp['addtime'] = $val['addtime'];
            $tmp['nickname'] = $val['nickname'];
            $list[] = $tmp;
        }

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        $partner_list = M('partner')->select();
        $this->assign('partner_list', $partner_list);

        $this->display();
    }

    /**
     * 添加联系人
     */
    public function addContact() {
        $this->addData();
    }

    /**
     * 编辑联系人
     */
    public function editContact() {
        $this->editData();
    }

    /**
     * 删除联系人
     */
    public function deleContact() {
        $this->deleData();
    }

    /**
     * 我的合同管理
     */
    public function agreement() {
        $fieldlist = $this->getFieldList('agreement');
        $klist = array_keys($fieldlist);

        $partners = $this->getUserPartner($_SESSION['authId']);
        $pmap['id'] = array('in', $partners);
        $partnerlist = M('partner')->where($pmap)->select();
        $this->assign('partnerlist', $partnerlist);

        $nowuid = $this->get_numuid();
        $map['a.c_create_uid'] = $nowuid;

        if ($_REQUEST['s_name']) {
            $map['p.agree_name'] = array('like', "%".trim($_REQUEST['s_name'])."%");
            $this->assign('s_name', $_REQUEST['s_name']);
        }
        if ($_REQUEST['s_partner_id']) {
            $map['p.partner_id'] = $_REQUEST['s_partner_id'];
            $this->assign('s_partner_id', $_REQUEST['s_partner_id']);
        }else {
            $map['p.partner_id'] = array('in', $partners);
        }

        $count = M('agreement p')
            ->join('crm_work_case a on a.c_id = p.e_id')
            ->join('crm_work_case_log b on b.c_id=a.c_id and a.step=b.step')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->where($map)
            ->count();

        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

		
        $datalist = M('agreement p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name,a.*,b.*')
			->join('crm_work_case a on a.c_id = p.e_id')
			->join('crm_work_case_log b on b.c_id=a.c_id and a.step=b.step')			
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
			->where($map)			
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();	
		$datalist = second_array_unique_bykey($datalist,'c_id');		
        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['nickname'] = $val['nickname'];
            $tmp['addtime'] = $val['addtime'];
            $tmp['agree_name'] = $val['agree_name'];
            $tmp['partner_id'] = $val['partner_id'];
            $tmp['partner_name'] = $val['partner_name'];
            $tmp['total_money'] = $val['total_money'];
			$tmp['c_state'] = $val['c_state'];
			$tmp['des'] = $val['des'];
			$tmp['uid'] = $val['uid'];
			$tmp['c_id'] = $val['c_id'];
			$tmp['step'] = $val['step'];
            $list[] = $tmp;
        }
		
        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        //查询客户
        /*
		$nowuid = $this->get_numuid();
		$pmap['owner'] = $nowuid;	
        $partnerlist = M('partner')->where($pmap)->select();	
        $this->assign('partnerlist', $partnerlist);
        */
		//查询流程
        $workflow = M('workflow')->select();			
		$this->assign('workflow',$workflow);
        $this->display();
    }

    /**
     * 待审合同管理
     */
	public function dsagreement(){
		$fieldlist = $this->getFieldList('agreement');
        $klist = array_keys($fieldlist);

        $count = M('agreement p')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');
		
		
		$nowuid = $this->get_numuid();		
		$map['a.uid'] = array('like','%'.$nowuid.'%');
		$map['a.st_status'] = array('in',array(0));			
        $datalist = M('agreement p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name,a.*,b.*,c.*')
			->join('crm_work_case_step a on a.c_id=p.e_id')
			->join('crm_work_case b on a.c_id=b.c_id')
			->join('crm_work_case_log c on c.c_id=a.c_id and b.step=c.step')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
			->where($map)			
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();	
		$datalist = second_array_unique_bykey($datalist,'c_id');		
        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['nickname'] = $val['nickname'];
            $tmp['addtime'] = $val['addtime'];
            $tmp['agree_name'] = $val['agree_name'];
            $tmp['partner_id'] = $val['partner_id'];
            $tmp['partner_name'] = $val['partner_name'];
            $tmp['total_money'] = $val['total_money'];
			$tmp['c_state'] = $val['c_state'];
			$tmp['des'] = $val['des'];
			$tmp['uid'] = $val['uid'];
			$tmp['c_id'] = $val['c_id'];
            $list[] = $tmp;
        }
		
        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        //查询客户
		$nowuid = $this->get_numuid();
		$pmap['owner'] = $nowuid;	
        $partnerlist = M('partner')->where($pmap)->select();	
        $this->assign('partnerlist', $partnerlist);
		//查询流程
        $workflow = M('workflow')->select();			
		$this->assign('workflow',$workflow);
        $this->display();
	}

    /**
     * 已审核合同
     */
	public function ysagreement(){
		$fieldlist = $this->getFieldList('agreement');
        $klist = array_keys($fieldlist);		
        $count = M('agreement p')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');
		
		
		//查找审核过的合同
		$nowuid = $this->get_numuid();		
		$map['a.uid'] = $nowuid;
		$map['a.step'] = array('not in',array(-1,0));		
		$result_tmp = M('work_case_log a')
				  ->field('a.c_id')									  
				  ->where($map)	
				  ->group('a.c_id')
				  ->select();		
		if($result_tmp){
			//查找合同的状态		  
			$c_idarr = array_column($result_tmp,'c_id');				
			$mapnew['a.c_id'] = array('in',$c_idarr);	
			$datalist = M('agreement p')
				->field('p.*,u.id as user_id,u.nickname,t.partner_name,a.*,b.*')
				->join('crm_work_case a on a.c_id=p.e_id')
				->join('crm_work_case_log b on a.c_id=b.c_id and a.step=b.step')			
				->join('left join crm_user u on u.user_number=p.owner')
				->join('left join crm_partner t on t.id=p.partner_id')				
				->where($mapnew)			
				->order('p.id desc')
				->limit("{$page->firstRow},{$page->listRows}")
				->select();	
			$datalist = second_array_unique_bykey($datalist,'c_id');
			$list = array();
			foreach ($datalist as $val) {
				$tmp = $this->dataPaser($val, $fieldlist);
				$tmp['id'] = $val['id'];
				$tmp['user_id'] = $val['user_id'];
				$tmp['nickname'] = $val['nickname'];
				$tmp['addtime'] = $val['addtime'];
				$tmp['agree_name'] = $val['agree_name'];
				$tmp['partner_id'] = $val['partner_id'];
				$tmp['partner_name'] = $val['partner_name'];
				$tmp['total_money'] = $val['total_money'];
				$tmp['c_state'] = $val['c_state'];
				$tmp['des'] = $val['des'];
				$tmp['uid'] = $val['uid'];
				$tmp['c_id'] = $val['c_id'];
				$list[] = $tmp;
			}			
			
        }
		$this->assign('fieldlist', $fieldlist);
		$this->assign('pagebar', $page->show());
		$this->assign('list', $list);
		//查询客户
		$nowuid = $this->get_numuid();
		$pmap['owner'] = $nowuid;	
        $partnerlist = M('partner')->where($pmap)->select();	
        $this->assign('partnerlist', $partnerlist);
		//查询流程
        $workflow = M('workflow')->select();			
		$this->assign('workflow',$workflow);
        $this->display();
		
	}

	/**
     * 添加合同
     */
    public function addAgreement() {
		//添加实例
		$data['uid'] = $this->get_numuid();	
		$data['wid'] = $_POST['e_id'];
		$data['title'] = $_POST['agree_name'];				
		$workcase = new workflow();	
		$recase = $workcase->addCase($data);
		$adddata['e_id'] = $recase['data']['c_id'];		
		//添加合同
        if (!is_numeric($_POST['total_money'])) {
            $return_data['code'] = -2;
            $return_data['msg'] = '您输入的数据不合法';
            $this->ajaxReturn($return_data,'JSON');
        }
        $this->addData($adddata);	
    }

    /**
     * 编辑合同
     */
    public function editAgreement() {
        $this->editData();
    }

    /**
     * 删除合同
     */
    public function deleAgreement() {
        $this->deleData();
    }

    /**
     * 添加回款记录
     */
    public function addMoneylog() {
        $data = array();
        $data['user_id'] = $this->get_numuid();
        $data['agree_id'] = intval($_POST['agree_id']);
        $data['money'] = $_POST['money'];
        $data['type'] = intval($_POST['type']);
        $data['status'] = intval($_POST['status']);
        $data['finish_time'] = strtotime($_POST['finish_time']);
        $data['addtime'] = time();
        if (!$data['agree_id'] || !$data['money'] || !$data['type'] || !is_numeric($data['status']) || !$data['finish_time']) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
        }
        $money_log = M('money_log');
        if ($data['type'] == 1) {
            //添加回款计划
            $map = array(
                'agree_id'  =>  $data['agree_id'],
                'type'  =>  1
            );
            $log = $money_log->where($map)->order('period desc')->select();
            $data['period'] = $log['period']?$log['period']+1:1;
        }else {
            $data['period'] = intval($_POST['period']);
            if (!$data['period']) {
                $this->ajaxReturn(array('code'=>-1,'msg'=>'期次缺失'), 'JSON');
            }
        }
        $rs = $money_log->add($data);
        if (!$rs) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'数据保存失败'), 'JSON');
        }

        $this->ajaxReturn(array('code'=>1,'msg'=>'上传成功'), 'JSON');
    }


}
