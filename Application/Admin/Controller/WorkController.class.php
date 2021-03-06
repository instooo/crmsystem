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
use \Common\Vendor\Workflow\workflow;

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
		//查找当前账号的角色
		$role = M('role_user')->where("user_id=".$_SESSION['authId'])->find();
		if(in_array($role['role_id'],array(1,20))){
			$this->assign('special',1);
			if ($_REQUEST['owner']) {
				$map['p.owner'] = trim($_REQUEST['owner']);
				$this->assign('owner', $_REQUEST['owner']);
			}
			$userlist = M('user')->select();
			$this->assign('userlist', $userlist);
			$this->assign('member_list_json',json_encode($userlist));
		}else{
			//查找自己拥有客户
			$nowuid = $this->get_numuid();		
			$partners = $this->getUserPartner($_SESSION['authId']);
			$map['p.id'] = array('in', $partners);
		}
		
		if ($_REQUEST['partner_name']) {
				$map['p.partner_name'] = array('like', "%".trim($_REQUEST['partner_name'])."%");
				$this->assign('partner_name', $_REQUEST['partner_name']);
			}
        if ($_REQUEST['khtype']) {
            $map['p.khtype'] = $_REQUEST['khtype'];
            $this->assign('khtype', $_REQUEST['khtype']);
        }
		if ($_REQUEST['status']==2) {			
            $map['p.status'] = 0;           
        }else if($_REQUEST['status']==1){
			 $map['p.status'] = 1;    
		}	
        $count = M('partner p')
            ->field('p.*,u.id,u.nickname')
			->where($map)
            ->join('left join crm_user u on u.user_number=p.owner')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $list = M('partner p')
            ->field('p.*,u.id as user_id,u.nickname')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->order('p.id desc')
			->where($map)
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();
      
        $this->assign('pagebar', $page->show());
        $this->assign('count', $count);
		$this->assign('list', $list);
        $this->display();
    }


    /**
     * 添加客户
     */
    public function addPartner() {
        if(IS_POST){
			$return_data = array('code'=>-1,'msg'=>'未知错误');
			do{			
				//添加实例				
				$adddata =$_POST;
				$adddata['owner'] = $this->get_numuid();
				$adddata['addtime'] = time();
				
				//添加合同
				if (!$_POST['partner_name']) {
					$return_data['code'] = -2;
					$return_data['msg'] = '请输入客户名称';
					break;
				}
				//添加合同
				if (!$_POST['contact_name']) {
					$return_data['code'] = -2;
					$return_data['msg'] = '联系人信息';
					break;
				}
				
				//添加合同
				if (!$_POST['tel']) {
					$return_data['code'] = -2;
					$return_data['msg'] = '请输入电话号码';
					break;
				}
				if(!preg_match("/^1[34578]{1}\d{9}$/",$_POST['tel'])){  
					$return_data['code'] = -2;
					$return_data['msg'] = '电话号码不正确';
					break;
				}
				if(!preg_match("/^\d*$/",$_POST['qq'])){  
					$return_data['code'] = -2;
					$return_data['msg'] = 'qq号码不正确';
					break;
				}
				$rs = M('partner')->add($adddata);
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
		}else{
			//查询流程		
			$this->display();
		}
    }

    /**
     * 编辑客户
     */
    public function editPartner() {
        if(IS_POST){
            $return_data = array('code'=>-1,'msg'=>'未知错误');
            do{
                $id = $_REQUEST['id'];
                if (!$id) {
                    $return_data['code'] = -1;
                    $return_data['msg'] = '参数缺失';
                    break;
                }

                $info = M('partner')->where(array('id'=>$id))->find();
                if ($info['owner'] != $this->get_numuid()) {
                    $return_data['code'] = -1;
                    $return_data['msg'] = '您不是该客户的所有者，没有权限修改';
                    break;
                }

                //添加实例
                $adddata =$_POST;
                $adddata['owner'] = $this->get_numuid();
                $adddata['addtime'] = time();

                //添加合同
                if (!$_POST['partner_name']) {
                    $return_data['code'] = -2;
                    $return_data['msg'] = '请输入客户名称';
                    break;
                }
                //添加合同
                if (!$_POST['contact_name']) {
                    $return_data['code'] = -2;
                    $return_data['msg'] = '联系人信息';
                    break;
                }

                //添加合同
                if (!$_POST['tel']) {
                    $return_data['code'] = -2;
                    $return_data['msg'] = '请输入电话号码';
                    break;
                }
                if(!preg_match("/^1[34578]{1}\d{9}$/",$_POST['tel'])){
                    $return_data['code'] = -2;
                    $return_data['msg'] = '电话号码不正确';
                    break;
                }
                if(!preg_match("/^\d*$/",$_POST['qq'])){
                    $return_data['code'] = -2;
                    $return_data['msg'] = 'qq号码不正确';
                    break;
                }
                $rs = M('partner')->where(array('id'=>$id))->save($adddata);
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
        }else{
            $id = $_REQUEST['id'];
            $info = M('partner')->where(array('id'=>$id))->find();
            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 删除客户
     */
    public function delePartner() {
        $this->deleData();
    }

	//批量转移客户
	public function partner_plzy(){
		if ($_POST) {
			$return = array("state"=>-1,"msg"=>'',"data"=>"");
            do{ 				
				$uid = trim ( $_REQUEST ['zy_uid'] );	
				$mid_str = trim(I('post.mid_str'),"|");
				$mid_arr = explode('|',$mid_str);
				//添加上
				foreach($mid_arr as $key=>$val){
					$map['id']=$val;
					$data['owner'] = $uid;	
					M('partner')->where($map)->save($data);						
				}
				$ret['code'] = 1;
				$ret['msg'] = '转移成功';
				break;
				
			}while(0);
			exit(json_encode($ret));
		}	
	}

    /**
     * 联系人管理
     */
    public function contact() {
        $fieldlist = $this->getFieldList('contact');
        $klist = array_keys($fieldlist);

        //查询客户
        $partners = $this->getUserPartner($_SESSION['authId']);
        $pmap['id'] = array('in', $partners);
        $partnerlist = M('partner')->where($pmap)->select();
        $this->assign('partnerlist', $partnerlist);

        if ($_REQUEST['s_partner_id']) {
            $map['p.partner_id'] = $_REQUEST['s_partner_id'];
            $this->assign('s_partner_id', $_REQUEST['s_partner_id']);
        }else {
            $map['p.partner_id'] = array('in', $partners);
        }

        if ($_REQUEST['s_partner_id']) {
            $map['p.partner_id'] = $_REQUEST['s_partner_id'];
            $this->assign('s_partner_id', $_REQUEST['s_partner_id']);
        }else {
            $map['p.partner_id'] = array('in', $partners);
        }

        $count = M('contact p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->where($map)
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

        $datalist = M('contact p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->where($map)
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();

        $list = array();
        foreach ($datalist as $val) {
            $tmp = $this->dataPaser($val, $fieldlist);
            $tmp['id'] = $val['id'];
            $tmp['user_id'] = $val['user_id'];
            $tmp['partner_id'] = $val['partner_id'];
            $tmp['partner_name'] = $val['partner_name'];
            $tmp['addtime'] = $val['addtime'];
            $tmp['nickname'] = $val['nickname'];
            $list[] = $tmp;
        }

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $list);

        $partner_list = M('partner')->select();
        $this->assign('partner_list', $partner_list);

        $this->display();
    }

    /**
     * 添加联系人
     */
    public function addContact() {
        $this->addData();
    }

    /**
     * 编辑联系人
     */
    public function editContact() {
        $this->editData();
    }

    /**
     * 删除联系人
     */
    public function deleContact() {
        $this->deleData();
    }

    /**
     * 我的合同管理
     */
    public function agreement() {
        $partners = $this->getUserPartner($_SESSION['authId']);
        $pmap['id'] = array('in', $partners);
        $partnerlist = M('partner')->where($pmap)->select();
        $this->assign('partnerlist', $partnerlist);

        $nowuid = $this->get_numuid();
		
		//查找当前账号的角色
		$role = M('role_user')->where("user_id=".$_SESSION['authId'])->find();
		if(!in_array($role['role_id'],array(1,20,25))){
			$map['a.c_create_uid'] = $nowuid;
		}
		
        if ($_REQUEST['orderid']) {
            $map['p.orderid'] = array('like', "%".trim($_REQUEST['orderid'])."%");
            $this->assign('orderid', $_REQUEST['orderid']);
        }
        if ($_REQUEST['s_partner_id']) {
            $map['p.partner_id'] = $_REQUEST['s_partner_id'];
            $this->assign('s_partner_id', $_REQUEST['s_partner_id']);
        }else {
			if(!in_array($role['role_id'],array(1,20,25))){
				$map['p.partner_id'] = array('in', $partners);
			}
        }
		if ($_REQUEST['c_state']) {
            $map['a.c_state'] = $_REQUEST['c_state'];
            $this->assign('c_state', $_REQUEST['c_state']);
        }	
        $count = M('agreement p')
            ->join('crm_work_case a on a.c_id = p.e_id')
            ->join('crm_work_case_log b on b.c_id=a.c_id and a.step=b.step')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->where($map)
            ->count();

        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');

		
        $datalist = M('agreement p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name,a.*,b.*')
			->join('crm_work_case a on a.c_id = p.e_id')
			->join('crm_work_case_log b on b.c_id=a.c_id and a.step=b.step')			
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
			->where($map)			
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();	
		$datalist = second_array_unique_bykey($datalist,'c_id'); 		
		//查找用户
		$userinfo =  M('user')->select();
		$userinfolist= array();
		foreach($userinfo as $key=>$val){
			$userinfolist[$val['user_number']]=$val;
		}		
		//重新组合数据
		$datalistnew = array();
		foreach($datalist as $key=>$val){
			$datalistnew[$key]=$val;
			$stepmapnew['c_id']=$val['c_id'];
			$stepinfo = M('work_case_step')->where($stepmapnew)->order('st_id desc')->find();			
			$newuidarr = explode('|',$stepinfo['uid']);
			/*
			$str = '';
			foreach($newuidarr as $k=>$v){
				$str.=$userinfolist[$v]['nickname'].",";
			}
			*/
            $str = $userinfolist[$newuidarr[0]]['nickname'];
			$datalistnew[$key]['desnew']=$str;
		}
		

        $this->assign('fieldlist', $fieldlist);
        $this->assign('pagebar', $page->show());
        $this->assign('list', $datalistnew);
		
		//查询流程
        $workflow = M('workflow')->select();			
		$this->assign('workflow',$workflow);
        $this->display();
    }

    /**
     * 待审合同管理
     */
	public function dsagreement(){	
        $count = M('agreement p')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');
		
		
		$nowuid = $this->get_numuid();		
		$map['a.uid'] = array('like','%'.$nowuid.'%');
		$map['a.st_status'] = array('in',array(0));			
        $datalist = M('agreement p')
            ->field('p.*,u.id as user_id,u.nickname,t.partner_name,a.*,b.*,c.*')
			->join('crm_work_case_step a on a.c_id=p.e_id')
			->join('crm_work_case b on a.c_id=b.c_id')
			->join('crm_work_case_log c on c.c_id=a.c_id and b.step=c.step')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
			->where($map)			
            ->order('p.id desc')
            ->limit("{$page->firstRow},{$page->listRows}")
            ->select();
		$datalist = second_array_unique_bykey($datalist,'c_id');	
		//查找用户
		$userinfo =  M('user')->select();
		$userinfolist= array();
		foreach($userinfo as $key=>$val){
			$userinfolist[$val['user_number']]=$val;
		}		
		//重新组合数据
		$datalistnew = array();
		foreach($datalist as $key=>$val){
			$datalistnew[$key]=$val;
			$stepmapnew['c_id']=$val['c_id'];
			$stepinfo = M('work_case_step')->where($stepmapnew)->order('st_id desc')->find();			
			$newuidarr = explode('|',$stepinfo['uid']);
			$str = '';
			foreach($newuidarr as $k=>$v){
				$str.=$userinfolist[$v]['nickname'].",";
			}
			$datalistnew[$key]['desnew']=$str;
		}


        
        $this->assign('pagebar', $page->show());
        $this->assign('list', $datalistnew);

        //查询客户
		$nowuid = $this->get_numuid();
		$pmap['owner'] = $nowuid;	
        $partnerlist = M('partner')->where($pmap)->select();	
        $this->assign('partnerlist', $partnerlist);
		//查询流程
        $workflow = M('workflow')->select();			
		$this->assign('workflow',$workflow);
        $this->display();
	}

    /**
     * 已审核合同
     */
	public function ysagreement(){
		$fieldlist = $this->getFieldList('agreement');
        $klist = array_keys($fieldlist);		
        $count = M('agreement p')
            ->join('left join crm_user u on u.user_number=p.owner')
            ->join('left join crm_partner t on t.id=p.partner_id')
            ->count();
        $page = new \Think\Page($count, 20);
        $page->setConfig('prev', '&nbsp;');
        $page->setConfig('next', '&nbsp;');
		
		
		//查找审核过的合同
		$nowuid = $this->get_numuid();		
		$map['a.uid'] = $nowuid;
		$map['a.step'] = array('not in',array(-1,0));		
		$result_tmp = M('work_case_log a')
				  ->field('a.c_id')									  
				  ->where($map)	
				  ->group('a.c_id')
				  ->select();		
		if($result_tmp){
			//查找合同的状态		  
			$c_idarr = array_column($result_tmp,'c_id');				
			$mapnew['a.c_id'] = array('in',$c_idarr);	
			$datalist = M('agreement p')
				->field('p.*,u.id as user_id,u.nickname,t.partner_name,a.*,b.*')
				->join('crm_work_case a on a.c_id=p.e_id')
				->join('crm_work_case_log b on a.c_id=b.c_id and a.step=b.step')			
				->join('left join crm_user u on u.user_number=p.owner')
				->join('left join crm_partner t on t.id=p.partner_id')				
				->where($mapnew)			
				->order('p.id desc')
				->limit("{$page->firstRow},{$page->listRows}")
				->select();				
			$datalist = second_array_unique_bykey($datalist,'c_id');
        }	

		//查找用户
		$userinfo =  M('user')->select();
		$userinfolist= array();
		foreach($userinfo as $key=>$val){
			$userinfolist[$val['user_number']]=$val;
		}		
		//重新组合数据
		$datalistnew = array();
		foreach($datalist as $key=>$val){
			$datalistnew[$key]=$val;
			$stepmapnew['c_id']=$val['c_id'];
			$stepinfo = M('work_case_step')->where($stepmapnew)->order('st_id desc')->find();			
			$newuidarr = explode('|',$stepinfo['uid']);
			$str = '';
			foreach($newuidarr as $k=>$v){
				$str.=$userinfolist[$v]['nickname'].",";
			}
			$datalistnew[$key]['desnew']=$str;
		}
		
		$this->assign('pagebar', $page->show());
		$this->assign('list', $datalistnew);
		//查询客户
		$nowuid = $this->get_numuid();
		$pmap['owner'] = $nowuid;	
        $partnerlist = M('partner')->where($pmap)->select();	
        $this->assign('partnerlist', $partnerlist);
		//查询流程
        $workflow = M('workflow')->select();			
		$this->assign('workflow',$workflow);
        $this->display();
		
	}

	/**
     * 添加短期合同
     */
    public function addAgreement() {
		if(IS_POST){
			$return_data = array('code'=>-1,'msg'=>'未知错误');
			do{			
				//添加实例
				$data['uid'] = $this->get_numuid();	
				$data['wid'] = $_POST['w_id'];
				$data['title'] = $_POST['agree_name'];				
				$workcase = new workflow();	
				$recase = $workcase->addCase($data);
				$adddata =$_POST;
				$adddata['e_id'] = $recase['data']['c_id'];		
				//添加合同
				if (!is_numeric($_POST['total_money'])) {
					$return_data['code'] = -2;
					$return_data['msg'] = '您输入的数据不合法';
					break;
				}
				
				//添加合同
				if (!$_POST['w_id'] || !$_POST['agree_name']|| !$_POST['bltype']||!$_POST['partner_id'] || !$_POST['qydate']) {					
					$return_data['code'] = -2;
					$return_data['msg'] = '请输入完整数据';
					break;
				}
				
				$adddata['owner'] = $this->get_numuid();
				$adddata['addtime'] = time();			
				$rs = M('agreement')->add($adddata);
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
		}else{
			$banshitype = M('type')->where('typeid=1')->select();
			$this->assign('banshitype',$banshitype);
			
			$zltype = M('type')->where('typeid=2')->select();
			$this->assign('zltype',$zltype);
			
			$hqtype = M('type')->where('typeid=3')->select();
			$this->assign('hqtype',$zltype);
			
			$partners = $this->getUserPartner($_SESSION['authId']);
			$pmap['id'] = array('in', $partners);
			$partnerlist = M('partner')->where($pmap)->select();
			$this->assign('partnerlist', $partnerlist);
			
			$model = M('');
			$sql = "SELECT max(id) as max from `crm_agreement`";
			$orderid = $model->query($sql);			
			//合同号
			$orderid = "S".$orderid[0]['max'];
			$orderid = date('Ymd',time()).$orderid;
			$this->assign('orderid',$orderid);			
			//查询流程
			$workflow = M('workflow')->select();			
			$this->assign('workflow',$workflow);
			$this->display();
		}
		
    }

	
	/**
     * 添加合同
     */
    public function addlongAgreement() {
		if(IS_POST){
			$return_data = array('code'=>-1,'msg'=>'未知错误');
			do{			
				//添加实例
				$data['uid'] = $this->get_numuid();	
				$data['wid'] = $_POST['w_id'];
				$data['title'] = $_POST['agree_name'];				
				$workcase = new workflow();	
				$recase = $workcase->addCase($data);
				$adddata =$_POST;
				$adddata['e_id'] = $recase['data']['c_id'];		
				//添加合同
				if (!is_numeric($_POST['total_money'])) {
					$return_data['code'] = -2;
					$return_data['msg'] = '您输入的数据不合法';
					break;
				}
				
				//添加合同
				//if (!$_POST['w_id'] || !$_POST['agree_name']|| !$_POST['bltype']||!$_POST['partner_id'] || !$_POST['qydate']) {					
				//	$return_data['code'] = -2;
				//	$return_data['msg'] = '请输入完整数据';
				//	break;
				//}
				
				$adddata['owner'] = $this->get_numuid();
				$adddata['addtime'] = time();
				$adddata['type'] = 1;	
				$rs = M('agreement')->add($adddata);
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
		}else{
			$banshitype = M('type')->where('typeid=1')->select();
			$this->assign('banshitype',$banshitype);
			
			$zltype = M('type')->where('typeid=2')->select();
			$this->assign('zltype',$zltype);
			
			$hqtype = M('type')->where('typeid=3')->select();
			$this->assign('hqtype',$zltype);
			
			$fuwucontent = M('type')->where('typeid=4')->select();
			$this->assign('fuwucontent',$fuwucontent);
			
			$partners = $this->getUserPartner($_SESSION['authId']);
			$pmap['id'] = array('in', $partners);
			$partnerlist = M('partner')->where($pmap)->select();
			$this->assign('partnerlist', $partnerlist);
			
			//合同号
			$model = M('');
			$sql = "SELECT max(id) as max from `crm_agreement`";
			$orderid = $model->query($sql);			
			//合同号
			$orderid = "S".$orderid[0]['max'];
			$orderid = date('Ymd',time()).$orderid;
			$this->assign('orderid',$orderid);			
			//查询流程
			$workflow = M('workflow')->select();			
			$this->assign('workflow',$workflow);
			$this->display();
		}
		
    }

    /**
     * 编辑合同
     */
    public function editAgreement() {
        if(IS_POST){
			$return_data = array('code'=>-1,'msg'=>'未知错误');
			do{						
				//添加实例
				$upddata['id'] = $_POST['id'];
				$upddata['hqziliao'] = $_POST['hqziliao'];				
				$map['id'] =$_POST['id'];
				$rs = M('agreement')->where($map)->save($upddata);
				if ($rs===false) {
					$return_data['code'] = -4;
					$return_data['msg'] = '保存失败';
					break;
				}
				$return_data['code'] = 1;
				$return_data['msg'] = '保存成功';
				break;
			}while(0);
			$this->ajaxReturn($return_data,'JSON');
		}else{
			$hqtypetmp = M('type')->where('typeid=3')->select();
			
			$id = $_REQUEST['id'];
            $info = M('agreement')->where(array('id'=>$id))->find();
			$hqziliaoarr=explode(",",$info['hqziliao']);
			foreach($hqtypetmp as $key=>$val){
				$hqtype[$key]=$val;
				if(in_array($val['name'],$hqziliaoarr)){					
					$hqtype[$key]['is_select']=1;
				}else{
					$hqtype[$key]['is_select']=0;
				}
			}
			$this->assign('hqtype',$hqtype);			
            $this->assign('info', $info);
			$this->display();
		}
    }

    /**
     * 删除合同
     */
    public function deleAgreement() {
        $this->deleData();
    }

    /**
     * 添加回款记录
     */
    public function addMoneylog() {
        $data = array();
        $data['user_id'] = $this->get_numuid();
        $data['agree_id'] = intval($_POST['agree_id']);
        $data['money'] = $_POST['money'];
        $data['type'] = intval($_POST['type']);
        $data['status'] = intval($_POST['status']);
        $data['finish_time'] = strtotime($_POST['finish_time']);
        $data['addtime'] = time();
        if (!$data['agree_id'] || !$data['money'] || !$data['type'] || !is_numeric($data['status']) || !$data['finish_time']) {
            $this->ajaxReturn(array('code'=>-1,'msg'=>'参数不全'), 'JSON');
        }
        $money_log = M('money_log');
        if ($data['type'] == 1) {
            //添加回款计划
            $map = array(
                'agree_id'  =>  $data['agree_id'],
                'type'  =>  1
            );
            $log = $money_log->where($map)->order('period desc')->select();
            $data['period'] = $log['period']?$log['period']+1:1;
        }else {
            $data['period'] = intval($_POST['period']);
            if (!$data['period']) {
                $this->ajaxReturn(array('code'=>-1,'msg'=>'期次缺失'), 'JSON');
            }
        }
        $rs = $money_log->add($data);
        if (!$rs) {
            $this->ajaxReturn(array('code'=>-2,'msg'=>'数据保存失败'), 'JSON');
        }

        $this->ajaxReturn(array('code'=>1,'msg'=>'上传成功'), 'JSON');
    }


}
