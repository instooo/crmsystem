<?php
/**
 * 控制器
 * Created by qinfan qf19910623@gmail.com.
 * Date: 2017/6/16
 */
namespace Admin\Controller;

use Think\Controller;

class PermissionController extends CommonController {

    /**
     * 节点列表
     */
    public function nodeList() {
        $this->display();
    }

    /**
     * 添加节点
     */
    public function addNode() {
        if (IS_AJAX) {
            $return_data = array('code'=>-1,'msg'=>'未知错误');
            do{
                $data = array();
                $data['pid'] = trim($_REQUEST['pid']);
                $data['title'] = trim($_REQUEST['title']);
                $data['name'] = trim($_REQUEST['name']);
                $data['ismenu'] = trim($_REQUEST['ismenu']);
                $data['sort'] = trim($_REQUEST['sort']);
                $data['level'] = trim($_REQUEST['level']);
                if (!is_numeric($data['pid'])
                    || !$data['title']
                    || !$data['name']
                    || !is_numeric($data['ismenu'])
                    || !is_numeric($data['sort'])
                    || !is_numeric($data['level'])) {
                    $return_data['code'] = -2;
                    $return_data['msg'] = '参数不全';
                    break;
                }
                $data['status'] = 1;
                $data['remark'] = '';
                $rs = M('node')->add($data);
                if (!$rs) {
                    $return_data['code'] = -3;
                    $return_data['msg'] = '保存失败';
                    break;
                }
                $return_data['code'] = 1;
                $return_data['msg'] = '保存成功';
                break;
            }while(0);
            $this->ajaxReturn($return_data, 'JSON');
        }else {
            $this->display();
        }
    }
}