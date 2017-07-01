<?php
namespace Admin\Model;
use Think\Model;

class RoleModel extends Model {
    public $dataTreeData=array();
    public function getRoleList(){
        $where=array();
        $where['status']=1;
        $data	=	$this->field('*')->where($where)->select();
        return $data;
    }

    public function getRoleTree(){
        $tree=$this->getChildRole(0,$this->getRoleList());
        return $tree;
    }

    //递归成树
    public  function getChildRole($id,$datalist,$pid=false){
        $arr=array();
        foreach($datalist as $val){
            if($id==$val['pid']){
                $temp=$val['id'];
                $val['child']=$this->getChildRole($temp,$datalist);
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