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
    public function test() {
        dump($_SESSION);die;
        $max_id = M('user')->max('id');
        $num = $max_id?$max_id+1:1;
        dump(strlen($num));die;
        dump(array_fill(0,5,0));
    }

    public function adduser() {
        echo 111;die;
        $data = array();
        $data['username'] = 'admin';
        $data['password'] = md5('123456');
        $data['nickname'] = '超级管理员';
        $data['realname'] = '超级管理员';
        $data['last_login_time'] = time();
        $data['last_login_ip'] = get_client_ip();
        $data['create_time'] = time();
        $data['update_time'] = time();
        $rs = M('user')->add($data);
        dump($rs);
    }

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
            unset($_SESSION['_ACCESS_LIST']);
            $return_data['code'] = 1;
            $return_data['msg'] = '退出成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }
}