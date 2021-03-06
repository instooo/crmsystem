<?php
/**
 * Created by PhpStorm.
 * User: qf19910623
 * Date: 2017/6/18 0018
 * Time: 20:56
 */
namespace Admin\Controller;
use Think\Controller;
use Org\Util\Rbac;

class PublicController extends Controller {   

    /**
     * 登录页
     */
    public function login() {
        $this->display();
    }

    /**
     * 登陆方法
     */
    public function checkLogin() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $username = I('post.username', '', 'trim,htmlspecialchars');
            $password = I('post.password', '', 'trim,htmlspecialchars');
            if (!$username || !$password) {
                $return_data['code'] = -2;
                $return_data['msg'] = '用户名或密码不能为空';
                break;
            }
            $userinfo = M('user')->where(array('username'=>$username))->find();
            if (!$userinfo) {
                $return_data['code'] = -3;
                $return_data['msg'] = '用户名不存在';
                break;
            }
            if ($userinfo['password'] != md5($password)) {
                $return_data['code'] = -4;
                $return_data['msg'] = '密码错误';
                break;
            }
            $_SESSION[C('USER_AUTH_KEY')] = $userinfo['id'];
            $_SESSION['username'] = $userinfo['username'];
            $_SESSION['user_number'] = $userinfo['user_number'];
            if (in_array($username, array("admin"))) {
                $_SESSION[C('ADMIN_AUTH_KEY')] = true;
            }
            Rbac::saveAccessList($userinfo['id']);

            $return_data['code'] = 1;
            $return_data['msg'] = '登陆成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 退出登录
     */
    public function loginout() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            unset($_SESSION[C('ADMIN_AUTH_KEY')]);
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION['username']);
			unset($_SESSION['user_number']);
			unset($_SESSION['tem_uid']);
			unset($_SESSION['tem_num']);
            unset($_SESSION['_ACCESS_LIST']);
            $return_data['code'] = 1;
            $return_data['msg'] = '退出成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 获取用户信息
     */
    public function getUserlist() {
        $uidstr = trim(htmlspecialchars($_REQUEST['uidstr']));
        if (!$uidstr) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数错误'), 'JSON');
        }
        $uidlist = explode('|', $uidstr);
        if (!$uidlist) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'参数非法'), 'JSON');
        }
        $map = array(
            'id'    =>  array('in', $uidlist)
        );
        $userlist = M('user')->field('id,user_number,username,nickname')->where($map)->select();
        if (!$userlist) {
            $this->ajaxReturn(array('code'=>-3,'msg'=>'用户信息为空'), 'JSON');
        }
        $this->ajaxReturn(array('code'=>1,'msg'=>'success', 'data'=>$userlist), 'JSON');
    }
}