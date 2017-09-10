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
			->order('log_id desc')
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

	//共享客户
	public function gxpartner(){
		if (IS_POST) {			
            $uid = $_POST['uid'];
            $par_id = $_POST['par_id'];
			
            if (!$par_id||!$uid) {
                $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
            }
			$data = explode('|', $uid);
			foreach($data as $key=>$val){
				$map['userid']=$val;
				$map['partners']=$par_id;
				$tmpresult = M('user_partner')->where($map)->find();
				if(!$tmpresult){
					$updata[$key]['userid']=$val;
					$updata[$key]['partners']=$par_id;
				}
			}  
			if($updata){
				 if (false === M('user_partner')->addAll($updata)) {
					$this->ajaxReturn(array('code'=>-2,'msg'=>'保存失败'), 'JSON');
				}
			} 
			$this->ajaxReturn(array('code'=>1,'msg'=>'保存成功'), 'JSON');
           
           
        }else {
			//查询出对应的职位和对应的用户，数量比较少，忽略性能
			$role = M('role')->field('id,name,pid')->select();
			//查询出所有的用户，数量比较少，忽略性能
			$member = D('UserView')->select();	
			$array_data = array();
			foreach($role as $key =>$val){
				$val['type']='jiaose';
				$val['pId']=$val['pid'];
				unset($val['pid']);
				$role[$key]=$val;			
			}
			//查询uid
			$pmap['partners'] = $_GET['pid'];
			$uidarr = M('user_partner')->where($pmap)->select();
			$uidarr = array_column($uidarr,'userid');
			
			foreach($member as $key =>$val){			
				//$array_data[$key]['id']="user".$val['id'];
				$array_data[$key]['id']=$val['id'];				
				$array_data[$key]['name']=$val['nickname'];	
				//$array_data[$key]['nickname']=$val['nickname'];	
				$array_data[$key]['pId']=$val['role_id'];	
				$array_data[$key]['type']='user';
				if(in_array($val['id'],$uidarr)){
					$array_data[$key]['checked']=true;
				}
			}	
			$array_data = array_merge($array_data,$role);
			$json_data = json_encode($array_data);
			$this->assign('par_id',$_GET['pid']);	
			$this->assign('json_data',$json_data);
			$this->display();
		}
	}
}
