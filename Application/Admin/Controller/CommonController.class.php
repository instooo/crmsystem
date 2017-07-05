<?php
/**
 * Created by PhpStorm.
 * User: qf19910623
 * Date: 2017/6/16 0016
 * Time: 0:04
 */
namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;

class CommonController extends Controller {

    public function _initialize() {
        //rbac 自带的游客验证
        Rbac::checkLogin();
        //如果是Index模块另外判断
        if(strtoupper(CONTROLLER_NAME)=="INDEX"){
            if(!isset($_SESSION[C('USER_AUTH_KEY')])){
                redirect(PHP_FILE.C('USER_AUTH_GATEWAY'));
            }
        }
        //验证权限
        if(!Rbac::AccessDecision()){
            if(IS_AJAX){
                $this->ajaxReturn(array("code"=>403,"msg"=>'没有权限',"data"=>""));
            }else
                $this->error("没有权限");
        }

        if($_SESSION[C('ADMIN_AUTH_KEY')])
            $datalist = D('Node')->getNodeList();
        else
            $datalist = D('Node')->getNodeListByUid($_SESSION[C('USER_AUTH_KEY')]);
        $tree = D('Node')->getChildNode(0,$datalist);
        $this->assign("tree",$tree);

        $this->assign('url_tag', strtoupper(CONTROLLER_NAME.'/'.ACTION_NAME));

    }

    /**
     * 获取字段列表
     * @param $field_type
     * @return array
     */
    public function getFieldList($field_type) {
        $fmap = array();
        $fmap['field_type'] = $field_type;
        $fmap['status'] = 1;
        $fieldlist = M('fields')->where($fmap)->order('sort asc')->select();
        $fieldarr = array();
        foreach ($fieldlist as $val) {
            $fieldarr['field_'.$val['id']] = $val;
        }
        return $fieldarr;
    }

    /**
     * 字段数据格式化
     * @param $value
     * @param $fieldlist
     * @return array
     */
    public function dataPaser($value, $fieldlist) {
        $klist = array_keys($fieldlist);
        $tmp = array();
        foreach ($klist as $v) {
            if ($fieldlist[$v]['data_type'] == 'date_time') {
                $value[$v] = date('Y-m-d H:i:s', $value[$v]);
            }elseif ($fieldlist[$v]['data_type'] == 'date') {
                $value[$v] = date('Y-m-d', $value[$v]);
            }elseif ($fieldlist[$v]['data_type'] == 'time') {
                $value[$v] = date('H:i:s', $value[$v]);
            }
            $tmp[$v] = $value[$v];
        }
        return $tmp;
    }

    /**
     * 生成字段表单
     * @param $fieldlist
     * @return string
     */
    public function createForm($fieldlist) {
        $formStr = '';
        $script = '';
        foreach ($fieldlist as $k=>$v) {
            if (in_array($v['data_type'], array('varchar','int','double'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><input name="field'.$v['id'].'" type="text" class="dfinput input_field'.$v['id'].'" style="width: 300px" /></li>';
            }elseif (in_array($v['data_type'], array('text'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><textarea name="field'.$v['id'].'" class="dfinput input_field'.$v['id'].'" style="width: 300px;height: 50px"></textarea></li>';
            }elseif (in_array($v['data_type'], array('date'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><input id="input_field'.$v['id'].'" name="field'.$v['id'].'" type="text" class="dfinput input_field'.$v['id'].'" style="width: 300px" readonly /></li>';
                $script .= '<script>laydate({elem: \'#input_field'.$v['id'].'\',format: \'YYYY/MM/DD\'});</script>';
            }elseif (in_array($v['data_type'], array('time'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><input id="input_field'.$v['id'].'" name="field'.$v['id'].'" type="text" class="dfinput input_field'.$v['id'].'" style="width: 300px" readonly /></li>';
                $script .= '<script>laydate({elem: \'#input_field'.$v['id'].'\',format: \'hh:mm:ss\'});</script>';
            }elseif (in_array($v['data_type'], array('date_time'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><input id="input_field'.$v['id'].'" name="field'.$v['id'].'" type="text" class="dfinput input_field'.$v['id'].'" style="width: 300px" readonly /></li>';
                $script .= '<script>laydate({elem: \'#input_field'.$v['id'].'\',format: \'YYYY/MM/DD hh:mm:ss\'});</script>';
            }elseif (in_array($v['data_type'], array('single_option'))) {
                $optlist = json_decode($v['field_option'], true);
                $optstr = '';
                foreach ($optlist as $val) {
                    $optstr .= '<option value="'.$val.'">'.$val.'</option>';
                }
                $formStr .= '<li><label>'.$v['field_name'].'</label><select name="field'.$v['id'].'" class="dfinput input_field'.$v['id'].'" style="width: 300px">'.$optstr.'</select></li>';
            }elseif (in_array($v['data_type'], array('multi_option'))) {
                $optlist = json_decode($v['field_option'], true);
                $optstr = '';
                foreach ($optlist as $val) {
                    $optstr .= '<option value="'.$val.'">'.$val.'</option>';
                }
                $formStr .= '<li><label>'.$v['field_name'].'</label><select name="field'.$v['id'].'" class="dfinput input_field'.$v['id'].'" style="width: 300px" multiple="multiple">'.$optstr.'</select></li>';
            }elseif (in_array($v['data_type'], array('file'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><input name="field'.$v['id'].'" type="file" class="dfinput input_field'.$v['id'].'" style="width: 300px" /></li>';
            }
            $formStr .= "\r\n";
        }
        return array('html'=>$formStr,'script'=>$script);
    }

    /**
     * 获取表单
     */
    public function getFieldForm() {
        $this->ajaxReturn(array('form'=>$this->createForm($this->getFieldList($_REQUEST['fieldtype']))), 'JSON');
    }

}