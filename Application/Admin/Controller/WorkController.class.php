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
        $fieldlist['nickname']['field_name'] = '所有者';
        $klist = array_keys($fieldlist);

        $count = M('partner p')
            ->field('p.*,u.id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('partner p')
            ->field('p.*,u.id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();

        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            /*
            $tmp = array();
            foreach ($klist as $v) {
                if ($fieldlist[$v]['data_type'] == 'date_time') {
                    $val[$v] = date('Y-m-d H:i:s', $val[$v]);
                }elseif ($fieldlist[$v]['data_type'] == 'date') {
                    $val[$v] = date('Y-m-d', $val[$v]);
                }elseif ($fieldlist[$v]['data_type'] == 'time') {
                    $val[$v] = date('H:i:s', $val[$v]);
                }
                $tmp[$v] = $val[$v];
            }
            */
            $tmp['user_id'] = $val['user_id'];
            $list[] = $tmp;
        }

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        $this->display();
    }


}
