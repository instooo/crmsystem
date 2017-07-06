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
        $fieldlist['nickname']['field_name'] = '创建人';
        $fieldlist['addtime']['field_name'] = '创建时间';
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
            $tmp['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
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
        $fieldlist['nickname']['field_name'] = '创建人';
        $fieldlist['addtime']['field_name'] = '创建时间';
        $klist = array_keys($fieldlist);

        $count = M('contact p')
            ->field('p.*,u.id as user_id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('contact p')
            ->field('p.*,u.id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();

        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
            $list[] = $tmp;
        }

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

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
     * 合同管理
     */
    public function agreement() {
        $fieldlist = $this->getFieldList('agreement');
        $fieldlist['nickname']['field_name'] = '创建人';
        $fieldlist['addtime']['field_name'] = '创建时间';
        $klist = array_keys($fieldlist);

        $count = M('agreement p')
            ->field('p.*,u.id as user_id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('agreement p')
            ->field('p.*,u.id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();

        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
            $list[] = $tmp;
        }

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 添加合同
     */
    public function addAgreement() {
        $this->addData();
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
