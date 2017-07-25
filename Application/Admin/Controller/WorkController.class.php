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
        $fieldlist = $this->getFieldList('partner');
        $klist = array_keys($fieldlist);

        $count = M('partner p')
            ->field('p.*,u.id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('partner p')
            ->field('p.*,u.id as user_id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->order('p.id desc')
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

        $count = M('contact p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name')
            ->join('left join crm_user u on u.id=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('contact p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name')
            ->join('left join crm_user u on u.id=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
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

        $count = M('agreement p')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');
		
		
		$nowuid = $this->get_numuid();
		$map['a.c_create_uid'] = $nowuid;	
		
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
        $partnerlist = M('partner')->select();
        $this->assign('partnerlist', $partnerlist);
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
        $partnerlist = M('partner')->select();
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
				->join('crm_work_case_log c on c.c_id=a.c_id and b.step=c.step')
				->join('left join crm_user u on u.user_number=p.owner')
				->join('left join crm_partner t on t.id=p.partner_id')
				->where($mapnew)			
				->order('p.id desc')
				->limit("{$page->firstRow},{$page->listRows}")
				->select();			
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
        $partnerlist = M('partner')->select();
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


}
