<?php
namespace Admin\Model;
use Think\Model;

class NodeModel extends Model {
    public $dataTreeData=array();
    public function getNodeList(){
        $where=array();
        $where['status']=1;
        $data	=	$this->field('id,name,title,pid,level,ismenu,sort,(pid*level*sort) as sortby')->where($where)->order('sortby desc')->select();
        return $data;
    }

    public function getNodeTree($uid=false){
        if($uid)
            $datalist	=	$this->getNodeListByUid($uid);
        else
            $datalist	=	$this->getNodeList();
        $tree=$this->getChildNode(0,$datalist);
        return $tree;
    }

    //用户所有用的权限
    public function getNodeListByUid($uid){
        $sql	=	"
		select node.id,node.name,node.title,node.pid,node.level,node.ismenu,node.sort,(node.pid*node.level*node.sort) as sortby from crm_node as node left join crm_access as access on access.node_id	= node.id left join crm_role_user as ru on ru.role_id = access.role_id left join crm_user as user	on 	
		 user.id = ru.user_id 	where user.id	=	$uid ORDER BY sort DESC
		";
        $rs	=	M('')->query($sql);
        return $rs;
    }

    //角色所拥有的权限
    public function getNodeListByRoleId($roleid){
        $sql	=	"
		select node.id,node.name,node.title,node.pid,node.level,node.ismenu,node.sort,(node.pid*node.level*node.sort) as sortby from crm_node as node left join crm_access as access on access.node_id	= node.id 	where access.role_id	=	{$roleid} ORDER BY sortby DESC
		";
        $rs	=	M('')->query($sql);
        return $rs;
    }

    //递归成树
    public  function getChildNode($id,$datalist,$pid=false){
        $arr=array();
        foreach($datalist as $val){
            if($id==$val['pid']){
                $temp=$val['id'];
                $val['child']=$this->getChildNode($temp,$datalist);
                if(empty($val['child'])){
                    unset($val['child']);
                }
                $arr[]=$val;
            }
        }
        return $arr;
    }
    //将树形展示出来
    public function  getTreeData($datalist,$fix){
        if(empty($datalist))
            return array();
        foreach ($datalist as &$val){
            $val['titleN']=$fix.$val['title'];
            $child	=$val['child'];
            $val['hvChild']=	empty($child)?0:1;
            unset($val['child']);
            array_push($this->dataTreeData, $val);
            if(!empty($child)){
                $this->getTreeData($child,$fix.'--');

            }

        }
    }
}