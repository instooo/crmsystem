<?php
/**
 * 控制器
 * Created by qinfan qf19910623@gmail.com.
 * Date: 2017/6/30
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Model;
use Think\Think;

class WorkController extends CommonController {
    public $partnerConfig;

    public function _initialize() {
        parent::_initialize();
        $this->partnerConfig = include(CONF_PATH.'partner.config.php');
    }

    /**
     * 客户管理
     */
    public function partner() {
        $fieldlist = $this->getFieldList('partner');
        $fieldlist['nickname']['field_name'] = '创建人';
        $fieldlist['addtime']['field_name'] = '创建时间';
        $klist = array_keys($fieldlist);

        $count = M('partner p')
            ->field('p.*,u.id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('partner p')
            ->field('p.*,u.id as user_id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();

        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            /*
            $tmp = array();
            foreach ($klist as $v) {
                if ($fieldlist[$v]['data_type'] == 'date_time') {
                    $val[$v] = date('Y-m-d H:i:s', $val[$v]);
                }elseif ($fieldlist[$v]['data_type'] == 'date') {
                    $val[$v] = date('Y-m-d', $val[$v]);
                }elseif ($fieldlist[$v]['data_type'] == 'time') {
                    $val[$v] = date('H:i:s', $val[$v]);
                }
                $tmp[$v] = $val[$v];
            }
            */
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
            $list[] = $tmp;
        }

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        $this->display();
    }

    public function test() {
        echo RUNTIME_PATH.'Data/_fields/';die;
        dump($_SESSION);
    }

    /**
     * 添加数据
     */
    public function addData() {
        $return_data = array('code'=>-1,'msg'=>'未知错误');
        do{
            $post = $_POST;
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

            $data['owner'] = $_SESSION[C('USER_AUTH_KEY')];
            $data['addtime'] = time();
            $rs = M($post['fieldtype'])->add($data);
            if (!$rs) {
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

    //修改客户
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

            $return_data['code'] = 1;
            $return_data['msg'] = '保存成功';
            break;
        }while(0);
        $this->ajaxReturn($return_data,'JSON');
    }

    /**
     * 联系人管理
     */
    public function contact() {
        $fieldlist = $this->getFieldList('contact');
        $fieldlist['nickname']['field_name'] = '创建人';
        $fieldlist['addtime']['field_name'] = '创建时间';
        $klist = array_keys($fieldlist);

        $count = M('contact p')
            ->field('p.*,u.id as user_id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('contact p')
            ->field('p.*,u.id,u.nickname')
            ->join('left join crm_user u on u.id=p.owner')
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();

        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['addtime'] = date('Y-m-d H:i:s', $val['addtime']);
            $list[] = $tmp;
        }

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 合同管理
     */
    public function agreement() {}


}
