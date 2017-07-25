<?php
/**
 * Created by PhpStorm.
 * User: qf19910623
 * Date: 2017/6/16 0016
 * Time: 0:04
 */
namespace Admin\Controller;

use Common\Vendor\Workflow\workflow;
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

	
	//获取当前账号
	public function get_numuid(){
		if($_SESSION['tem_num']){
			//查找当前用户
			$nowuid = $_SESSION['tem_num'];			
		}else if($_SESSION['authId']){
			//查找当前用户
			$nowuid = $_SESSION['authId'];
		}else{
			echo "未登录";die;
		}
		return $nowuid;
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
     * 数据过滤以及格式化
     * @param $data
     * @param string $fieldtype
     * @return array
     */
    public function dataFilter($data, $fieldtype = 'partner') {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $fieldlist = $this->getFieldList($fieldtype);
            foreach ($data as $key=>$val) {
                if (in_array($fieldlist[$key]['data_type'], array('date_time','date','time'))) {
                    $data[$key] = strtotime($val);
                }else {
                    $data[$key] = trim($val);
                }
            }

            $return_data['code'] = 1;
            $return_data['msg'] = 'success';
            $return_data['data'] = $data;
            break;
        }while(0);
        return $return_data;
    }

    /**
     * 数据检测
     * @param $data
     * @param string $fieldtype
     * @return array
     */
    public function dataChecker($data, $fieldtype = 'partner') {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            if (!$data) {
                $return_data['code'] = -2;
                $return_data['msg'] = '没有获取到数据';
                break;
            }
            $fieldlist = $this->getFieldList($fieldtype);
            $flag = '';
            foreach ($data as $key=>$val) {
                if ($fieldlist[$key]) {
                    if (!$val && $fieldlist[$key]['not_null'] == 0) {
                        $flag = $fieldlist[$key]['field_name'].'不能为空';
                        break;
                    }
                    if ($val && in_array($fieldlist[$key]['data_type'], array('int','double'))) {
                        if (!is_numeric($val)) {
                            $flag = $fieldlist[$key]['field_name'].'必须为数字';
                            break;
                        }
                    }elseif ($val && in_array($fieldlist[$key]['data_type'], array('date','time','date_time'))) {
                        if (!strtotime($val)) {
                            $flag = $fieldlist[$key]['field_name'].'格式非法';
                            break;
                        }
                    }
                }
            }
            if ($flag) {
                $return_data['code'] =-3;
                $return_data['msg'] = $flag;
                break;
            }

            $return_data['code'] = 1;
            $return_data['msg'] = '验证通过';
            $return_data['data'] = $data;
            break;
        }while(0);
        return $return_data;
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
            }elseif ($fieldlist[$v]['data_type'] == 'file') {
                $value[$v] = '<a class="tablelink" target="_blank" href="'.$value[$v].'">查看</a>';
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
    public function createForm($fieldlist, $data = array()) {
        $formStr = '';
        $script = '';
        foreach ($fieldlist as $k=>$v) {
            if (in_array($v['data_type'], array('varchar','int','double'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><input name="field_'.$v['id'].'" type="text" class="dfinput input_field'.$v['id'].'" style="width: 300px" value="'.$data['field_'.$v['id']].'" /></li>';
            }elseif (in_array($v['data_type'], array('text'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><textarea name="field_'.$v['id'].'" class="dfinput input_field'.$v['id'].'" style="width: 300px;height: 50px">'.$data['field_'.$v['id']].'</textarea></li>';
            }elseif (in_array($v['data_type'], array('date'))) {
                $vvv = $data['field_'.$v['id']]?date('Y-m-d',$data['field_'.$v['id']]):'';
                $formStr .= '<li><label>'.$v['field_name'].'</label><input id="input_field'.$v['id'].'" name="field_'.$v['id'].'" type="text" class="dfinput input_field'.$v['id'].'" style="width: 300px" value="'.$vvv.'" readonly /></li>';
                $script .= '<script>laydate({elem: \'#input_field'.$v['id'].'\',format: \'YYYY-MM-DD\',istime:false});</script>';
            }elseif (in_array($v['data_type'], array('time'))) {
                $vvv = $data['field_'.$v['id']]?date('H:i:s',$data['field_'.$v['id']]):'';
                $formStr .= '<li><label>'.$v['field_name'].'</label><input id="input_field'.$v['id'].'" name="field_'.$v['id'].'" type="text" class="dfinput input_field'.$v['id'].'" style="width: 300px" value="'.$vvv.'" readonly /></li>';
                $script .= '<script>laydate({elem: \'#input_field'.$v['id'].'\',format: \'hh:mm:ss\',istime:false});</script>';
            }elseif (in_array($v['data_type'], array('date_time'))) {
                $vvv = $data['field_'.$v['id']]?date('Y-m-d H:i:s',$data['field_'.$v['id']]):'';
                $formStr .= '<li><label>'.$v['field_name'].'</label><input id="input_field'.$v['id'].'" name="field_'.$v['id'].'" type="text" class="dfinput input_field'.$v['id'].'" style="width: 300px" value="'.$vvv.'" readonly /></li>';
                $script .= '<script>laydate({elem: \'#input_field'.$v['id'].'\',format: \'YYYY-MM-DD hh:mm:ss\',istime:true});</script>';
            }elseif (in_array($v['data_type'], array('single_option'))) {
                $optlist = json_decode($v['field_option'], true);
                $optstr = '';
                foreach ($optlist as $val) {
                    $selected = $data['field_'.$v['id']] == $val?'selected':'';
                    $optstr .= '<option value="'.$val.'" '.$selected.'>'.$val.'</option>';
                }
                $formStr .= '<li><label>'.$v['field_name'].'</label><select name="field_'.$v['id'].'" class="dfinput input_field'.$v['id'].'" style="width: 300px">'.$optstr.'</select></li>';
            }elseif (in_array($v['data_type'], array('file'))) {
                $formStr .= '<li><label>'.$v['field_name'].'</label><input name="field_'.$v['id'].'" type="file" class="dfinput input_field'.$v['id'].'" style="width: 300px" /></li>';
            }
            $formStr .= "\r\n";
        }
        return array('html'=>$formStr,'script'=>$script);
    }

    /**
     * 获取表单
     */
    public function getFieldForm() {
        $id = $_REQUEST['id'];
        $data = array();
        if ($id) {
            $data = M($_REQUEST['fieldtype'])->where(array('id'=>$id))->find();
        }
        $this->ajaxReturn(array(
            'form'=>$this->createForm($this->getFieldList($_REQUEST['fieldtype']), $data),
        ), 'JSON');
    }

    public function fileUpload() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $files = $_FILES;
            if (!$files) {
                $return_data['code'] = -2;
                $return_data['msg'] = '没有文件上传';
                break;
            }
            $flag = '';
            $result = array();
            $upload = new \Think\Upload();
            $upload->maxSize = 20*1024*1024;//上传文件不超过20M
            //$upload->exts = array('jpg', 'gif', 'png', 'jpeg', 'bmp');
            $upload->autoSub = false;
            $upload->rootPath = './';
            $upload->savePath = '/Uploads/'.date('Ymd').'/';
            foreach ($files as $key=>$value) {
                $upload->saveName = date('YmdHis').mt_rand(1000,9999);
                $info = $upload->uploadOne($value);
                if (!$info) {
                    $flag = $upload->getError();
                    break;
                }
                $result[$key] = $info['savepath'].$info['savename'];
            }
            if ($flag) {
                $return_data['code'] = -3;
                $return_data['msg'] = '文件上传失败：'.$flag;
                break;
            }

            $return_data['code'] = 1;
            $return_data['msg'] = 'success';
            $return_data['data'] = $result;
            break;
        }while(0);
        return $return_data;
    }

    public function _deleteDir($R){
        //打开一个目录句柄
        $handle = opendir($R);
        //读取目录,直到没有目录为止
        while(($item = readdir($handle)) !== false){
            //跳过. ..两个特殊目录
            if($item != '.' and $item != '..'){
                //如果遍历到的是目录
                if(is_dir($R.'/'.$item)){
                    //继续向目录里面遍历
                    $this->_deleteDir($R.'/'.$item);
                }else{
                    //如果不是目录，删除该文件
                    if(!unlink($R.'/'.$item))
                        die('error!');
                }
            }
        }
        //关闭目录
        closedir( $handle );
        //删除空的目录
        return rmdir($R);
    }


    /**
     * 添加数据
     */
    public function addData($extpar = array()) {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
			if($extpar){
				$post = $_POST;
				$post = array_merge($post,$extpar);
			}else{
				 $post = $_POST;
			}   			
            if (!$post['fieldtype']) {
                $return_data['code'] = -1;
                $return_data['msg'] = '参数不全';
                break;
            }
            $check = $this->dataChecker($post, $post['fieldtype']);
            if ($check['code'] != 1) {
                $return_data['code'] = -2;
                $return_data['msg'] = $check['msg'];
                break;
            }
            $filterRes = $this->dataFilter($post, $post['fieldtype']);
            $data = $filterRes['data'];

            if ($_FILES) {
                $uploadRes = $this->fileUpload();
                if ($uploadRes['code'] != 1) {
                    $return_data['code'] = -3;
                    $return_data['msg'] = $uploadRes['msg'];
                    break;
                }
                $data = array_merge($uploadRes['data'], $data);
            }

            $data['owner'] = $this->get_numuid();
            $data['addtime'] = time();
            $rs = M($post['fieldtype'])->add($data);
            if (!$rs) {
                $return_data['code'] = -4;
                $return_data['msg'] = '保存失败';
                break;
            }			
            $partnerConfig = include(CONF_PATH.'partner.config.php');
            \Common\Vendor\Eventlog::saveLog('添加了'.$partnerConfig['FIELDS_TYPE'][$post['fieldtype']], $rs, $post['fieldtype']);

            $return_data['code'] = 1;
            $return_data['msg'] = '保存成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 修改数据
     */
    public function editData() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $post = $_POST;
            if (!$post['id'] || !$post['fieldtype']) {
                $return_data['code'] = -1;
                $return_data['msg'] = '参数不全';
                break;
            }
            $check = $this->dataChecker($post, $post['fieldtype']);
            if ($check['code'] != 1) {
                $return_data['code'] = -2;
                $return_data['msg'] = $check['msg'];
                break;
            }
            $filterRes = $this->dataFilter($post, $post['fieldtype']);
            $data = $filterRes['data'];

            if ($_FILES) {
                $uploadRes = $this->fileUpload();
                if ($uploadRes['code'] != 1) {
                    $return_data['code'] = -3;
                    $return_data['msg'] = $uploadRes['msg'];
                    break;
                }
                $data = array_merge($uploadRes['data'], $data);
            }

            $rs = M($post['fieldtype'])->where(array('id'=>$post['id']))->save($data);
            if (false === $rs) {
                $return_data['code'] = -4;
                $return_data['msg'] = '保存失败';
                break;
            }
            $partnerConfig = include(CONF_PATH.'partner.config.php');
            \Common\Vendor\Eventlog::saveLog('修改了'.$partnerConfig['FIELDS_TYPE'][$post['fieldtype']], $post['id'], $post['fieldtype']);

            $return_data['code'] = 1;
            $return_data['msg'] = '保存成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 删除数据
     */
    public function deleData() {
        $return_data = array('code'=>-1,'msg'=>'');
        do{
            $idstr = trim($_REQUEST['id']);
            $fieldtype = trim($_REQUEST['fieldtype']);
            if (!$idstr || !$fieldtype) {
                $return_data['code'] = -2;
                $return_data['msg'] = '参数缺失';
                break;
            }
            $docinfo = M($fieldtype)->where(array('id'=>$idstr))->find();
            if (!$docinfo) {
                $return_data['code'] = -3;
                $return_data['msg'] = '并没有找到你想删除的内容';
                break;
            }

            $rs = M($fieldtype)->where(array('id'=>$idstr))->delete();
            if (false === $rs) {
                $return_data['code'] = -3;
                $return_data['msg'] = '删除失败';
                break;
            }
            $partnerConfig = include(CONF_PATH.'partner.config.php');
            \Common\Vendor\Eventlog::saveLog('删除了'.$partnerConfig['FIELDS_TYPE'][$_REQUEST['fieldtype']]);

            $return_data['code'] = 1;
            $return_data['msg'] = '删除成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data, 'JSON');
    }


}