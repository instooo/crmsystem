<?php
/**
 * 客户管理控制器
 * Created by qinfan qf19910623@gmail.com.
 * Date: 2017/6/22
 */
namespace Admin\Controller;
use Think\Controller;

class SystemController extends CommonController {
    public $partnerConfig;

    public function _initialize() {
        parent::_initialize();
        $this->partnerConfig = include(CONF_PATH.'partner.config.php');
    }

    /**
     * 字段管理
     */
    public function fieldsManager() {
        $this->assign('fields_type_list', $this->partnerConfig['FIELDS_TYPE']);
        $this->display();
    }
}