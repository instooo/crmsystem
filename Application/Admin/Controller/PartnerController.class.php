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
            $this->assign('partnerinfo',$partnerinfo);
		}
		$this->display();
	}
	
	//客户合同详情
	public function ag_detail(){		
		$this->display();
	}
	
	//客户合同
	public function agreement(){
		$this->display();
	}

}
