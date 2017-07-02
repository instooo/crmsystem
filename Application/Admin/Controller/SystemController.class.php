<?php
/**
 * 客户管理控制器
 * Created by qinfan qf19910623@gmail.com.
 * Date: 2017/6/22
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Model;

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
        if (IS_AJAX) {
            $return_data = array('code'=>-1,'msg'=>'未知错误');
            do{
                $map = array();
                if ($_REQUEST['field_type']) {
                    $map['field_type'] = $_REQUEST['field_type'];
                }
                $list = M('fields')->where($map)->select();
                foreach ($list as $k=>$v) {
                    $list[$k]['data_type_name'] = $this->partnerConfig['DATA_TYPE'][$v['data_type']];
                    $list[$k]['not_null_name'] = $v['not_null']==1?'必填':'非必填';
                    $list[$k]['is_unique_name'] = $v['is_unique']==1?'唯一':'非唯一';
                    $list[$k]['status_name'] = $v['status']==1?'启用':'禁用';
                }

                $return_data['code'] = 1;
                $return_data['msg'] = 'success';
                $return_data['data'] = $list;
                break;
            }while(0);
            $this->ajaxReturn($return_data,'JSON');
        }else {
            $this->assign('fields_type_list', $this->partnerConfig['FIELDS_TYPE']);
            $this->assign('data_type_list', $this->partnerConfig['DATA_TYPE']);
            $this->display();
        }
    }

    /**
     * 添加字段
     */
    public function addFields() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $field_type = trim(htmlspecialchars($_REQUEST['field_type']));
            $data_type = trim(htmlspecialchars($_REQUEST['data_type']));
            $field_name = trim(htmlspecialchars($_REQUEST['field_name']));
            $not_null = trim(htmlspecialchars($_REQUEST['not_null']));
            $is_unique = trim(htmlspecialchars($_REQUEST['is_unique']));
            $status = trim(htmlspecialchars($_REQUEST['status']));
            $field_option = $_REQUEST['field_option'];
            if (!$field_type || !$data_type) {
                $return_data['code'] = -2;
                $return_data['msg'] = '参数错误';
                break;
            }
            if (!$field_name) {
                $return_data['code'] = -3;
                $return_data['msg'] = '请输入正确的字段名';
                break;
            }
            $data = array();
            $data['field_name'] = $field_name;
            $data['data_type'] = $data_type;
            $data['field_type'] = $field_type;
            $data['addtime'] = time();
            if (is_numeric($not_null)) {
                $data['not_null'] = $not_null;
            }
            if (is_numeric($is_unique)) {
                $data['is_unique'] = $is_unique;
            }
            if (is_numeric($status)) {
                $data['status'] = $status;
            }
            if ($field_option) {
                $data['field_option'] = json_encode($field_option);
            }
            $rs = M('fields')->add($data);
            if (!$rs) {
                $return_data['code'] = -4;
                $return_data['msg'] = '数据保存失败';
                break;
            }

            //更新或创建表
            $data['id'] = $rs;
            $this->alterFields($data);

            $return_data['code'] = 1;
            $return_data['msg'] = '保存成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    public function test() {
        $data = M('fields')->where(array('id'=>2))->find();
        $this->alterFields($data);
    }

    private function alterFields($data) {
        if (!$data['field_type'] || !$data['id']) return false;
        $createRes = $this->createTable($data['field_type']);
        switch ($data['data_type']) {
            case 'varchar':
                $typeStr = 'varchar(128)';
                break;
            case 'text':
                $typeStr = 'text';
                break;
            case 'int':
                $typeStr = 'int(10)';
                break;
            case 'double':
                $typeStr = 'double(10,2)';
                break;
            case 'date':
                $typeStr = 'int(15)';
                break;
            case 'time':
                $typeStr = 'int(15)';
                break;
            case 'date_time':
                $typeStr = 'int(15)';
                break;
            case 'single_option':
                $typeStr = 'text';
                break;
            case 'multi_option':
                $typeStr = 'text';
                break;
            default:
                $typeStr = '';
                break;
        }
        if (!$typeStr) return false;
        try{
            $field_name = 'field_'.$data['id'];
            $not_null = $data['not_null']?' not null':'';
            $is_unique = $data['is_unique']?' unique':'';
            $alterSql = "ALTER TABLE `{$createRes}` ADD `{$field_name}` {$typeStr}{$not_null}{$is_unique}";
            M('')->execute($alterSql);
            return true;
        }catch (\Exception $e){
            return false;
        }

    }

    private function createTable($tablename) {
        $tablename = C('DB_PREFIX').$tablename;
        $rs1 = M('')->query("SHOW TABLES LIKE '{$tablename}'");
        if ($rs1) {
            return $tablename;
        }
        if ($tablename == C('DB_PREFIX').'partner') {
            //客户表
            $createSql = "CREATE TABLE `{$tablename}` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`owner` int(10) NOT NULL COMMENT '所有者',`addtime` int(15) NOT NULL COMMENT '创建时间',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        }elseif ($tablename == C('DB_PREFIX').'contact') {
            //联系人表
            $createSql = "CREATE TABLE `{$tablename}` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`owner` int(10) NOT NULL COMMENT '所有者',`addtime` int(15) NOT NULL COMMENT '创建时间',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        }else {
            //默认合同表
            $createSql = "CREATE TABLE `{$tablename}` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`owner` int(10) NOT NULL COMMENT '所有者',`status` varchar(16) NOT NULL COMMENT '审批状态',`addtime` int(15) NOT NULL COMMENT '创建时间',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        }
        M('')->execute($createSql);
        return $tablename;
    }
}