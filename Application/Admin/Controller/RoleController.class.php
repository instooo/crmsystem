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
			$array_data[$key]['name']=$val['username'];		
			$array_data[$key]['pId']=$val['role_id'];	
			$array_data[$key]['type']='user';
		}
		$array_data = array_merge($array_data,$role);
		$json_data = json_encode($array_data);		
		//查找对应的action
		$actresult = M('workflow_action')->select();
		$this->assign('actresult',$actresult);
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
}