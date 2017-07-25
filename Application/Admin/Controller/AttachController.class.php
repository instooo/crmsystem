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
        $info = $this->fileUpload('agreement');
        if ($info['code'] != 1) {
            $this->ajaxReturn($info, 'JSON');
        }
        $files = $info['data'];
        $nowtime = time();
        $addData = array();
        foreach ($files as $val) {
            $tmp = array();
            $tmp['join_id'] = $agree_id;
            $tmp['type'] = 'agreement';
            $tmp['file'] = $val;
            $tmp['uploader'] = $this->get_numuid();
            $tmp['addtime'] = $nowtime;
            $addData[] = $tmp;
        }
        if (!M('cattachment')->addAll($addData)) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'数据保存失败'), 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'上传成功'), 'JSON');
    }

}