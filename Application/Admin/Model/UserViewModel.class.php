<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class UserViewModel extends ViewModel {
	public $viewFields  = array(
		'user' => array('id','username'),
		'role_user'=>array('role_id','_on'=>'user.id=role_user.user_id'),		
	);	
}