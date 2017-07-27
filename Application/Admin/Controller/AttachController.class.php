<?php
/**
 * 附件管理
 * Created by PhpStorm.
 * User: qf19910623
 * Date: 2017/7/25 0025
 * Time: 22:00
 */
namespace Admin\Controller;

class AttachController extends CommonController {


    /**
     * 上传合同文档
     */
    public function uploadAgreeDoc() {
        $agree_id = intval($_POST['id']);
        if (!$agree_id) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
        }
        $dir = trim($_POST['subdir']);
        $info = $this->fileUpload($dir, false);
        if ($info['code'] != 1) {
            $this->ajaxReturn($info, 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'上传成功'), 'JSON');
    }

    /**
     * 创建文件夹
     */
    public function createDir() {
        $subdir = trim(trim($_POST['subdir']), "/");
        $dirname = trim(htmlspecialchars($_POST['dirname']));
        if (!$subdir || !$dirname) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'目录参数错误'), 'JSON');
        }
        $dirpath = ROOT_PATH.'Uploads/'.$subdir;
        if (!is_dir($dirpath)) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'上级目录不存在'), 'JSON');
        }
        if (!mkdir($dirpath.'/'.$dirname)) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'创建目录失败'), 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'创建成功'), 'JSON');
    }

    /**
     * 读取目录
     */
    public function readDir() {
        $subdir = trim(trim($_POST['subdir']), "/");
        $isup = intval($_POST['isup']);
        if (!$subdir) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'目录参数错误'), 'JSON');
        }
        $dirpath = ROOT_PATH.'Uploads/'.$subdir;
        if (!is_dir($dirpath)) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'目录不存在'), 'JSON');
        }
        $handle = opendir($dirpath);
        if (!$handle) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'目录读取失败'), 'JSON');
        }
        $dirlist = array();
        $filelist = array();
        while ( false  !== ( $file  =  readdir ( $handle ))) {
            if ($file == "." || $file == "..") continue;
            if (is_dir($dirpath.'/'.$file)) {
                $dirlist[] = $file;
            }else {
                $filelist[] = $file;
            }
        }
        asort($dirlist);
        asort($filelist);
        $list = array();
        foreach ($dirlist as $val) {
            $tmp = array();
            $tmp['isfile'] = 0;
            $tmp['subdir'] = $subdir;
            $tmp['name'] = $val;
            $list[] = $tmp;
        }
        foreach ($filelist as $val) {
            $tmp = array();
            $tmp['isfile'] = 1;
            $tmp['url'] = '/Uploads/'.$subdir.'/'.$val;
            $tmp['subdir'] = $subdir;
            $tmp['name'] = $val;
            $list[] = $tmp;
        }
        $this->ajaxReturn(array(
            'code'=>1,
            'msg'=>'读取成功',
            'list'=>$list
        ), 'JSON');
    }

}