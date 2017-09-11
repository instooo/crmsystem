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

class RechargeController extends CommonController {
	//长期合同收费
	public function shoufei(){
		//获取当前月份
		if($_REQUEST['orderid']){
			$smap['p.orderid'] = $_REQUEST['orderid'];
			$this->assign('orderid',$_REQUEST['orderid']);
		}
		if($_REQUEST['partner_name']){
			$smap['t.partner_name'] = array('like',"%".$_REQUEST['partner_name']."%");
			$this->assign('partner_name',$_REQUEST['partner_name']);
		}	
		$smap['p.type']=1;
		$yearm = date('Ym',time());
		$agreement = M('agreement p')		
		->where($smap)		
        ->join('left join crm_partner t on t.id=p.partner_id')		
		->select();			
		foreach($agreement as $key=>$val){
			//查找收费记录
			$agreement[$key]['shoufeilog'] = M('money_log')->where("agree_id=".$val['id'])->select();
		}
		foreach($agreement as $key=>$val){
			$agreement[$key] = $val;
			foreach($val['shoufeilog'] as $k=>$v){
				$agreement[$key]['shounew'][$v['month']]=$v;				
				$agreement[$key]['monthar'].=$v['month'].",";
			}
		}
		$month_arr = array();
		for($i=12;$i>0;$i--){
			$month_arr[$i]['ym'] = date("Ym", strtotime("-".($i-1). "months", strtotime($yearm)));
			$month_arr[$i]['m'] = date("m", strtotime("-".($i-1). "months", strtotime($yearm)));
		}
		$this->assign('month_arr',$month_arr);	
		$this->assign('agreement',$agreement);
		$this->display();
	}
	
}
