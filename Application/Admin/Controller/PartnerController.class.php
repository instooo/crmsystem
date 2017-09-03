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
use \Common\Vendor\Workflow\partner;
use \Common\Vendor\Workflow\messagelog;

class PartnerController extends CommonController {
    public $partnerConfig;
    public $moneylogType;

    public function _initialize() {
        parent::_initialize();    
    }
	
	//客户详情
	public function detail(){
		$id = $_GET['id'];
		if($id==''){		
			echo "参数不全";die;
		}else{
			$nowuid = $this->get_numuid();
			$map['id']=$id;			
			$partnerinfo = M('partner')->where($map)->find();
			//查看当前实例的具体情况
		//查找所有的用户
		$userinfo = M('user')->select();
		$userinfoarr = array();
		foreach($userinfo as $val){
			$userinfoarr[$val['user_number']]=$val;
		}	
		unset($map);
		$map['p_id']=$id;		
		$log = M('work_partner_log')			
			->where($map)
			->order('log_id asc')
			->select();			
		$tmp_arr = array();
		foreach($log as $key=>$val){
			if($val['pid']==0){
				$tmp_arr[$val['log_id']]['log_id']=$val['log_id'];	
				$tmp_arr[$val['log_id']]['pid']=$val['pid'];
				$tmp_arr[$val['log_id']]['c_id']=$val['c_id'];
				$tmp_arr[$val['log_id']]['w_id']=$val['w_id'];
				$tmp_arr[$val['log_id']]['step']=$val['step'];
				$tmp_arr[$val['log_id']]['uid']=$val['uid'];
				$tmp_arr[$val['log_id']]['user']=$userinfoarr[$val['uid']]['nickname'];
				$tmp_arr[$val['log_id']]['re_uid']=$val['re_uid'];
				$tmp_arr[$val['log_id']]['re_user']=$userinfoarr[$val['re_uid']]['nickname'];
				$tmp_arr[$val['log_id']]['act_id']=$val['act_id'];
				$tmp_arr[$val['log_id']]['create_time']=$val['create_time'];
				$tmp_arr[$val['log_id']]['des']=$val['des'];
				$tmp_arr[$val['log_id']]['comment']=$val['comment'];
				$tmp_arr[$val['log_id']]['status']=$val['status'];
			}else{		
				$pid = $val['pid'];
				$tmp_arr[$pid]['sub'][$key]=$val;
				$tmp_arr[$pid]['sub'][$key]['user']=$userinfoarr[$val['uid']]['nickname'];
				$tmp_arr[$pid]['sub'][$key]['re_user']=$userinfoarr[$val['re_uid']]['nickname'];
			}
		}			
		$partnerinfo['history'] = $tmp_arr;
            $this->assign('partnerinfo',$partnerinfo);
		}
		$this->display();
	}
	
	public function add_plog(){
		
	}
	//客户合同
	public function agreement(){
		$this->display();
	}

}
