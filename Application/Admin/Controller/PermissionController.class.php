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
        $list = D('Node')->getNodeList();
        $menulist = array();
        foreach ($list as $k=>$v) {
            $v['tag_str'] = '｜';
            for ($i=2;$i<=$v['level'];$i++) {
                $v['tag_str'] .= '—';
            }
            $menulist[] = $v;
        }
        $tree = D('Node')->getChildNode(0,$menulist);
        $this->assign('tree', $tree);
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
            $list = D('Node')->getNodeList();
            $menulist = array();
            foreach ($list as $k=>$v) {
                $v['tag_str'] = '｜';
                for ($i=2;$i<=$v['level'];$i++) {
                    $v['tag_str'] .= '—';
                }
                $menulist[] = $v;
            }
            $tree = D('Node')->getChildNode(0,$menulist);
            $this->assign('tree', $tree);
            $this->display();
        }
    }

    /**
     * 编辑节点
     */
    public function editNode() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $data = array();
            $node_id = trim($_REQUEST['node_id']);
            $data['pid'] = trim($_REQUEST['pid']);
            $data['title'] = trim($_REQUEST['title']);
            $data['name'] = trim($_REQUEST['name']);
            $data['ismenu'] = trim($_REQUEST['ismenu']);
            $data['sort'] = trim($_REQUEST['sort']);
            $data['level'] = trim($_REQUEST['level']);
            if (!is_numeric($node_id)
                || !is_numeric($data['pid'])
                || !$data['title']
                || !$data['name']
                || !is_numeric($data['ismenu'])
                || !is_numeric($data['sort'])
                || !is_numeric($data['level'])) {
                $return_data['code'] = -2;
                $return_data['msg'] = '参数不全';
                break;
            }
            $nodeinfo = M('node')->where(array('id'=>$node_id))->find();
            if (!$nodeinfo) {
                $return_data['code'] = -3;
                $return_data['msg'] = '更新的节点不存在';
                break;
            }

            $rs = M('node')->where(array('id'=>$node_id))->save($data);
            if (false === $rs) {
                $return_data['code'] = -3;
                $return_data['msg'] = '保存失败';
                break;
            }
            $return_data['code'] = 1;
            $return_data['msg'] = '保存成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data, 'JSON');
    }

    /**
     * 删除节点
     */
    public function deleNode() {
        $return_data = array('code'=>-1,'msg'=>'');
        do{
            $idstr = trim($_REQUEST['id']);
            if (!$idstr) {
                $return_data['code'] = -2;
                $return_data['msg'] = '参数缺失';
                break;
            }
            $docinfo = M('node')->where(array('id'=>$idstr))->find();
            if (!$docinfo) {
                $return_data['code'] = -3;
                $return_data['msg'] = '并没有找到你想删除的内容';
                break;
            }

            $rs = M('node')->where(array('id'=>$idstr))->delete();
            $rs1 = M('node')->where(array('pid'=>$idstr))->delete();
            if (false === $rs) {
                $return_data['code'] = -3;
                $return_data['msg'] = '删除失败';
                break;
            }
            $return_data['code'] = 1;
            $return_data['msg'] = '删除成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data, 'JSON');
    }

    /**
     * 角色列表
     */
    public function roleList() {
        $list = D('role')->getRoleList();
        $menulist = array();
        foreach ($list as $k=>$v) {
            $v['tag_str'] = '｜';
            for ($i=2;$i<=$v['level'];$i++) {
                $v['tag_str'] .= '—';
            }
            $menulist[] = $v;
        }
        $tree = D('role')->getChildRole(0,$menulist);
        $this->assign('rolelist', $tree);
        $this->display();
    }

    /**
     * 添加橘色
     */
    public function addRole() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $rolename = I('post.rolename', '', 'trim,htmlspecialchars');
            $pid = I('post.pid', '', 'trim,htmlspecialchars');
            $level = I('post.level', '', 'trim,htmlspecialchars');
            if (!$rolename || !is_numeric($pid) || !is_numeric($level)) {
                $return_data['code'] = -2;
                $return_data['msg'] = '请输入正确的角色名';
                break;
            }

            $data = array();
            $data['name'] = $rolename;
            $data['pid'] = $pid;
            $data['level'] = $level;
            $data['status'] = 1;
            $data['create_time'] = time();
            $data['update_time'] = time();
            $rs = M('role')->add($data);
            if (!$rs) {
                $return_data['code'] = -3;
                $return_data['msg'] = '保存失败';
                break;
            }

            $return_data['code'] = 1;
            $return_data['msg'] = '添加成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 编辑角色
     */
    public function editRole() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $id = I('post.id', '', 'trim,htmlspecialchars');
            $rolename = I('post.rolename', '', 'trim,htmlspecialchars');
            $pid = I('post.pid', '', 'trim,htmlspecialchars');
            $level = I('post.level', '', 'trim,htmlspecialchars');
            if (!is_numeric($id) || !$rolename || !is_numeric($pid) || !is_numeric($level)) {
                $return_data['code'] = -2;
                $return_data['msg'] = '请输入正确的信息';
                break;
            }

            $roleinfo = M('role')->where(array('id'=>$id))->find();
            if (!$roleinfo) {
                $return_data['code'] = -3;
                $return_data['msg'] = '该部门不存在';
                break;
            }

            $data = array();
            $data['name'] = $rolename;
            $data['pid'] = $pid;
            $data['level'] = $level;
            $data['update_time'] = time();
            $rs = M('role')->where(array('id'=>$id))->save($data);
            if (false === $rs) {
                $return_data['code'] = -4;
                $return_data['msg'] = '保存失败';
                break;
            }

            $return_data['code'] = 1;
            $return_data['msg'] = '保存成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 删除角色
     */
    public function deleRole() {
        $return_data = array('code'=>-1,'msg'=>'');
        do{
            $idstr = trim($_REQUEST['id']);
            if (!$idstr) {
                $return_data['code'] = -2;
                $return_data['msg'] = '参数缺失';
                break;
            }
            $docinfo = M('role')->where(array('id'=>$idstr))->find();
            if (!$docinfo) {
                $return_data['code'] = -3;
                $return_data['msg'] = '并没有找到你想删除的内容';
                break;
            }

            $rs = M('role')->where(array('id'=>$idstr))->delete();
            $rs1 = M('role')->where(array('pid'=>$idstr))->delete();
            if (false === $rs) {
                $return_data['code'] = -3;
                $return_data['msg'] = '删除失败';
                break;
            }
            $return_data['code'] = 1;
            $return_data['msg'] = '删除成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data, 'JSON');
    }

    /**
     * 账户列表
     */
    public function userList() {
        $list = M('user u')
            ->field('u.*,r.name as rolename,r.id as role_id')
            ->join('left join crm_role_user ru on ru.user_id=u.id')
            ->join('left join crm_role r on r.id=ru.role_id')
            ->order('u.id desc')->select();
        $this->assign('list' ,$list);

        $rolelist = M('role')->select();
        $this->assign('rolelist' ,$rolelist);

        //客户列表
        $partnerlist = M('partner')->select();
        $this->assign('partnerlist' ,$partnerlist);

        $this->display();
    }

    /**
     * 编辑账户
     */
    public function editUser() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $id = I('post.id', '', 'trim,intval');
            $password = I('post.password', '', 'trim,htmlspecialchars');
            $nickname = I('post.nickname', '', 'trim,htmlspecialchars');
            $role_id = I('post.role_id', '', 'trim,htmlspecialchars');
            if (!$id) {
                $return_data['code'] = -2;
                $return_data['msg'] = '参数错误';
                break;
            }
            $info = M('user')->where(array('id'=>$id))->find();
            if (!$info) {
                $return_data['code'] = -3;
                $return_data['msg'] = '您修改的账户不存在';
                break;
            }

            $user_data = array();
            if ($password) $user_data['password'] = md5($password);
            if ($nickname) $user_data['nickname'] = $nickname;
            if ($user_data) $user_data['create_time'] = time();
            if ($user_data) {
                $rs = M('user')->where(array('id'=>$id))->save($user_data);
                if (false === $rs) {
                    $return_data['code'] = -4;
                    $return_data['msg'] = '账户信息保存失败';
                    break;
                }
            }


            if ($role_id) {
                $roleinfo = M('role')->where(array('id'=>$role_id))->find();
                if (!$roleinfo) {
                    $return_data['code'] = -5;
                    $return_data['msg'] = '角色不存在';
                    break;
                }
                $role_data = array();
                $role_data['role_id'] = $role_id;
                $rs1 = M('role_user')->where(array('user_id'=>$id))->find();
                if ($rs1) {
                    $rs2 = M('role_user')->where(array('user_id'=>$id))->save($role_data);
                    if (false === $rs2) {
                        $return_data['code'] = -6;
                        $return_data['msg'] = '角色信息更新失败';
                        break;
                    }
                }else {
                    $role_data['user_id'] = $id;
                    $rs2 = M('role_user')->add($role_data);
                    if (!$rs2) {
                        $return_data['code'] = -7;
                        $return_data['msg'] = '角色信息更新失败';
                        break;
                    }
                }

            }

            $return_data['code'] = 1;
            $return_data['msg'] = '保存成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 添加账户
     */
    public function addUser() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $username = I('post.username', '', 'trim,htmlspecialchars');
            $password = I('post.password', '', 'trim,htmlspecialchars');
            $nickname = I('post.nickname', '', 'trim,htmlspecialchars');
            $role_id = I('post.role_id', '', 'trim,htmlspecialchars');
            if (!$username || !$password || !$nickname || !$role_id) {
                $return_data['code'] = -2;
                $return_data['msg'] = '参数不全';
                break;
            }
            $info = M('user')->where(array('username'=>$username))->find();
            if ($info) {
                $return_data['code'] = -3;
                $return_data['msg'] = '您添加的用户名已存在';
                break;
            }
            $max_id = M('user')->max('id');
            $num = $max_id?$max_id+1:1;
            $user_num = 's'.implode('', array_fill(0, 5-strlen($num), 0)).$num.'n';


            $data = array();
            $data['user_number'] = $user_num;
            $data['username'] = $username;
            $data['password'] = md5($password);
            $data['nickname'] = $nickname;
            $data['realname'] = $nickname;
            $data['last_login_time'] = time();
            $data['last_login_ip'] = get_client_ip();
            $data['create_time'] = time();
            $data['update_time'] = time();
            $rs = M('user')->add($data);
            if (!$rs) {
                $return_data['code'] = -4;
                $return_data['msg'] = '账户信息保存失败';
                break;
            }

            $role_data = array();
            $role_data['user_id'] = $rs;
            $role_data['role_id'] = $role_id;
            $rs1 = M('role_user')->add($role_data);
            if (!$rs1) {
                $return_data['code'] = -5;
                $return_data['msg'] = '角色信息保存失败';
                break;
            }

            $return_data['code'] = 1;
            $return_data['msg'] = '保存成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 分配权限
     */
    public function addAccess() {
        if (IS_AJAX) {
            $return_data = array('code'=>-1,'msg'=>'未知错误');
            do{
                $role_id = I('post.role_id', '', 'intval');
                $node_ids = I('post.node_ids', '', 'trim');
                if (!$role_id || !$node_ids) {
                    $return_data['code'] = -2;
                    $return_data['msg'] = '参数错误';
                    break;
                }

                $nodeArr = explode(',', $node_ids);
                if (!$nodeArr) {
                    $return_data['code'] = -3;
                    $return_data['msg'] = '参数不合法';
                    break;
                }
                $rs1 = M('access')->where(array('role_id'=>$role_id))->delete();
                if (false === $rs1) {
                    $return_data['code'] = -4;
                    $return_data['msg'] = '数据更新失败';
                    break;
                }
                $dataAll = array();
                foreach ($nodeArr as $val) {
                    $temp = array();
                    $temp['role_id'] = $role_id;
                    $temp['node_id'] = $val;
                    $dataAll[] = $temp;
                }
                $rs2 = M('access')->addAll($dataAll);
                if (!$rs2) {
                    $return_data['code'] = -4;
                    $return_data['msg'] = '数据保存失败';
                    break;
                }

                $return_data['code'] = 1;
                $return_data['msg'] = '保存成功';
                break;
            }while(0);
            $this->ajaxReturn($return_data,'JSON');
        }else {
            $role_id = I('get.id', '', 'intval');
            if (!$role_id) {
                $this->error('参数错误');
            }
            $roleinfo = M('role')->where(array('id'=>$role_id))->find();
            if (!$roleinfo) {
                $this->error('角色不存在');
            }
            $this->assign('roleinfo', $roleinfo);

            $accessList = M('access')->where(array('role_id'=>$role_id))->select();
            $access = array_column($accessList, 'node_id');

            $menulist = D('Node')->getNodeList();
            foreach ($menulist as $key=>$val) {
                if (in_array($val['id'], $access)) {
                    $menulist[$key]['has_access'] = 1;
                }
            }
            $tree = D('Node')->getChildNode(0,$menulist);
            $this->assign('tree', $tree);
            $this->display();
        }
    }

    /**
     * 编辑用户可见客户
     */
    public function editUserPartner() {
        if (IS_POST) {
            $user_id = intval($_POST['user_id']);
            $partner_id = $_POST['partner_id'];
            if (!$user_id) {
                $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
            }
            $data = array();
            if (is_array($partner_id) && count($partner_id) > 0) {
                $data['partners'] = implode(',', $partner_id);
            }else {
                $data['partners'] = '';
            }
            if (false === M('user_partner')->where(array('userid'=>$user_id))->save($data)) {
                $this->ajaxReturn(array('code'=>-2,'msg'=>'保存失败'), 'JSON');
            }
            $this->ajaxReturn(array('code'=>1,'msg'=>'保存成功'), 'JSON');
        }else {
            $user_id = intval($_GET['user_id']);
            if (!$user_id) {
                $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
            }
            $user_partner = M('user_partner');
            $data = $user_partner->where(array('userid'=>$user_id))->find();
            if (!$data) {
                $data = array();
                $data['userid'] = $user_id;
                $data['partners'] = '';
                $user_partner->add($data);
            }
            if ($data['partners']) {
                $data['json'] = explode(',', $data['partners']);
            }
            $this->ajaxReturn(array('code'=>1,'msg'=>'获取成功','data'=>$data), 'JSON');
        }
    }
}