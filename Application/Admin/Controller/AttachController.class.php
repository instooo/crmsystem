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

    public function test() {
        $dirpath = ROOT_PATH.'Uploads/agreement/1';
        $handle = opendir($dirpath);
        if (!$handle) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'目录读取失败'), 'JSON');
        }
        while ( false  !== ( $file  =  readdir ( $handle ))) {
            if ($file == "." || $file == "..") continue;
            dump(mb_detect_encoding($file));
        }
        //dump(iconv_get_encoding('all'));
    }


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
            $subarr = explode("/", $subdir);
            $tempdir = ROOT_PATH.'Uploads';
            foreach ($subarr as $val) {
                $tempdir .= '/'.$val;
                if (DIRECTORY_SEPARATOR == "\\") { //windows os
                    $tempdir = iconv('utf-8', 'gbk', $tempdir);
                }
                if (is_dir($tempdir)) continue;
                if (!mkdir($tempdir)) {
                    $this->ajaxReturn(array('code'=>-2,'msg'=>'上级目录初始化失败'), 'JSON');
                }
            }
        }
        if (DIRECTORY_SEPARATOR == "\\") { //windows os
            $mkdirname = iconv('utf-8', 'gbk', $dirpath.'/'.$dirname);
        }
        if (!mkdir($mkdirname)) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'创建目录失败'), 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'创建成功'), 'JSON');
    }

    /**
     * 重命名目录
     */
    public function renameDir() {
        $old_dir = trim(trim($_POST['old_dir']), "/");
        $new_dir = trim(trim($_POST['new_dir']), "/");
        if (!$old_dir || !$new_dir) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
        }
        $oldpath = ROOT_PATH.'Uploads/'.$old_dir;
        $newpath = ROOT_PATH.'Uploads/'.$new_dir;
        if (DIRECTORY_SEPARATOR == "\\") { //windows os
            $oldpath = iconv('utf-8', 'gbk', $oldpath);
            $newpath = iconv('utf-8', 'gbk', $newpath);
        }
        if (!is_dir($oldpath)) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'修改的目录不存在'), 'JSON');
        }
        if (is_dir($newpath)) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'文件夹名称已存在'), 'JSON');
        }
        if (!rename($oldpath, $newpath)) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'文件夹名称修改失败'), 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'修改成功'), 'JSON');
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
        if (DIRECTORY_SEPARATOR == "\\") { //windows os
            $dirpath = iconv('utf-8', 'gbk', $dirpath);
        }
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
            if (DIRECTORY_SEPARATOR == "\\") { //windows os
                $val = iconv('gbk', 'utf-8', $val);
            }
            $tmp = array();
            $tmp['isfile'] = 0;
            $tmp['subdir'] = $subdir;
            $tmp['name'] = $val;
            $list[] = $tmp;
        }
        foreach ($filelist as $val) {
            if (DIRECTORY_SEPARATOR == "\\") { //windows os
                $val = iconv('gbk', 'utf-8', $val);
            }
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

    /**
     * 删除文件或目录
     */
    public function delFile() {
        $type = trim(htmlspecialchars($_POST['type']));
        $path = trim($_POST['path']);
        if (!$type || !$path) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数错误'), 'JSON');
        }
        $realpath = ROOT_PATH.'Uploads/'.$path;
        if (DIRECTORY_SEPARATOR == "\\") { //windows os
            $realpath = iconv('utf-8', 'gbk', $realpath);
        }
        if ($type == 'file') {
            //删除文件
            if (!file_exists($realpath)) {
                $this->ajaxReturn(array('code'=>-2,'msg'=>'删除的文件不存在'), 'JSON');
            }
            if (!unlink($realpath)) {
                $this->ajaxReturn(array('code'=>-3,'msg'=>'文件删除失败'), 'JSON');
            }
        }else {
            //删除目录
            if (!is_dir($realpath)) {
                $this->ajaxReturn(array('code'=>-2,'msg'=>'删除的目录不存在'), 'JSON');
            }
            if (!$this->deldir($realpath)) {
                $this->ajaxReturn(array('code'=>-3,'msg'=>'目录删除失败'), 'JSON');
            }
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'删除成功'), 'JSON');
    }

    private function deldir($dir) {
        //先删除目录下的文件：
        $dh=opendir($dir);
        while (false  !== ( $file  =  readdir ( $dh ))) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

}