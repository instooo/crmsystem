<?php
/**
 * 控制器
 * Created by dengxiaolong
 * Date: 2017/6/22
 */
namespace Admin\Controller;

use Think\Controller;

class RoleController extends CommonController {

    public function index(){
		//前端传过来的对应审核人ID
		$wid=$_GET['wid'];
		$uid=str_replace('user','',$_GET['uid']);
		$title_html =$_GET['title_html']; 
		$uidarr = explode('|',$uid);
		$act=$_GET['act'];
		$extend_tit=$_GET['extend_tit'];
		$data_field=$_GET['data_field'];		
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
		foreach($member as $key =>$val){			
			//$array_data[$key]['id']="user".$val['id'];
			$array_data[$key]['id']="user".$val['user_number'];				
			$array_data[$key]['name']=$val['nickname'];	
			//$array_data[$key]['nickname']=$val['nickname'];	
			$array_data[$key]['pId']=$val['role_id'];	
			$array_data[$key]['type']='user';
			if(in_array($val['user_number'],$uidarr)){
				$array_data[$key]['checked']=true;
			}
		}	
		$array_data = array_merge($array_data,$role);
		$json_data = json_encode($array_data);		
		//查找对应的action
		$actresult = M('workflow_action')->select();
		$this->assign('actresult',$actresult);
		//查询出所有可选字段,并查询出已被选着的字段
		$fields = M('fields')->where('`field_type`="agreement"')->select();		
		$h_field = explode('|',$data_field);
		foreach($fields as $key=>$val){
			if(in_array($val['id'],$h_field)){
				$fields[$key]['is_select']=1;
			}else{
				$fields[$key]['is_select']=0;
			}
		}
		$this->assign('fields',$fields);
		$this->assign('extend_tit',$extend_tit);		
		$this->assign('data_field',$data_field);
		$this->assign('title_html',$title_html);
		$this->assign('wid',$wid);
		$this->assign('json_data',$json_data);
		$this->display();
	}
	
	//无线节点管理
	public function tree_pid($array,$pid=0){
		$result = array();
		foreach($array as $key=>$val){
			if($val['pid']==$pid){
				$val['children'] = $this->tree_pid($array,$val['id']); //递归获取子记录  
				if($val['children'] == null){  
					unset($val['children']);             //如果子元素为空则unset()进行删除，说明已经到该分支的最后一个元素了（可选）  
				}  
				$tree[] = $val;   
			}
		}
		return $tree;
	}
	
	//流程节点人员选择
	public function get_wkrole(){
		$cid=$_GET['cid'];
		$map['c_id'] = $cid;
		$caseresult = M('work_case')->where($map)->find();
		if($caseresult['c_state']!=2){
			unset($map);
			$map['w_id'] = $caseresult['w_id'];
			$map['step_id'] = $caseresult['step']+2;
			$result = M('workflow_extend')->where($map)->find();
			$uidarr = explode('|',$result['uid']);
			$map['user_number'] = array('in',$uidarr);
			$user = M('user')->where($map)->select();		 
		}		
		$this->assign('user',$user);
		$this->display();
	}
	
	//流程节点人员选择
	public function get_wkallrole(){
		$nowuid = $this->get_numuid();	
		$cid=$_GET['cid'];
		$act=$_GET['act'];
		$map['c_id'] = $cid;
		$caseresult = M('work_case')->where($map)->find();		
		$uidarrtmp[]=-1;
		if($caseresult['c_state']!=2 && $act!='nopass'  ){
			unset($map);
			$map['w_id'] = $caseresult['w_id'];	
			$map['step_id'] = array('lt',$caseresult['step']+3);		
			$result = M('workflow_extend')->where($map)->select();			
			foreach($result as $key=>$val){
				$uidarr = explode('|',$val['uid']);
				foreach($uidarr as $v){
					if($nowuid!=$v){
						$uidarrtmp[]=$v;
					}					
				}
			}	
			$uidarrtmp[]=$caseresult['c_create_uid'];
			$uidstr = array_unique($uidarrtmp);			
			$map['user_number'] = array('in',$uidstr);
			$user = M('user')->where($map)->select();		 
		}else if($caseresult['c_state']!=2 && $act=='nopass' ){
			unset($map);
			$map['w_id'] = $caseresult['w_id'];	
			$map['step_id'] = array('lt',$caseresult['step']+2);		
			$result = M('workflow_extend')->where($map)->select();			
			foreach($result as $key=>$val){
				$uidarr = explode('|',$val['uid']);
				foreach($uidarr as $v){
					if($nowuid!=$v){
						$uidarrtmp[]=$v;
					}					
				}
			}	
			$uidarrtmp[]=$caseresult['c_create_uid'];
			$uidstr = array_unique($uidarrtmp);			
			$map['user_number'] = array('in',$uidstr);
			$user = M('user')->where($map)->select();	
			
		}			
		$this->assign('user',$user);
		$this->display();
	}
}