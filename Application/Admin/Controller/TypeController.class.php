<?php
/**
 * 类型管理控制器
 * Created by qinfan qf19910623@gmail.com.
 * Date: 2017/9/16
 */
namespace Admin\Controller;

class TypeController extends CommonController {
    public $typeConfig;

    public function __construct() {
        parent::__construct();
        $this->typeConfig = array(
            1   =>  '办事类型',
            2   =>  '资料类型',
            3   =>  '后勤资料类型',
        );
    }

    /**
     * 类型列表
     */
    public function typelist() {
        $map = array();
        $typeid = intval($_REQUEST['typeid']);
        if ($typeid) {
            $map['typeid'] = $typeid;
            $this->assign('typeid', $typeid);
        }
        $count = M('type')->where($map)->count();
        $page = new \Think\Page($count, 10);
        $list = M('type')->where($map)->limit("{$page->firstRow},{$page->listRows}")->select();
        $this->assign('list', $list);
        $this->assign('pagebar', $page->show());
        $this->assign('typeConfig', $this->typeConfig);
        $this->display();
    }

    /**
     * 添加类型
     */
    public function addType() {
        $data = array();
        $data['typeid'] = intval($_REQUEST['typeid']);
        $data['name'] = $_REQUEST['name'];
        if (!$data['typeid'] || !$data['name']) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
        }
        if (!$this->typeConfig[$data['typeid']]) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'您选择的类型非法'), 'JSON');
        }
        if (!M('type')->add($data)) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'数据保存失败'), 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'数据保存成功'), 'JSON');
    }

    /**
     * 编辑类型
     */
    public function editType() {
        $id = $_REQUEST['id'];
        $data = array();
        $data['typeid'] = intval($_REQUEST['typeid']);
        $data['name'] = $_REQUEST['name'];
        if (!$id || !$data['typeid'] || !$data['name']) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
        }
        if (!$this->typeConfig[$data['typeid']]) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'您选择的类型非法'), 'JSON');
        }

        if (false === M('type')->where(array('id'=>$id))->save($data)) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'数据保存失败'), 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'数据保存成功'), 'JSON');
    }

    /**
     * 删除类型
     */
    public function deleType() {
        $id = $_REQUEST['id'];
        if (!$id) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
        }
        if (false === M('type')->where(array('id'=>$id))->delete()) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'删除失败'), 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'删除成功'), 'JSON');
    }
}