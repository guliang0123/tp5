<?php
/**
 * Index控制器
 *
 */
namespace app\index\controller;

use think\Controller;
use app\index\model\user\User;

class Index extends Controller
{	
	
	private $userdb;
    //初始化userdb对象
	public function _initialize()
	{
		$this->userdb = new User();
	}
    //所有列表内容
    public function index()
    {	
    	$userlist = $this->userdb->userList();
    	return $this->fetch('index/index',['userlist'=>$userlist]);
        
    }
    //单个用户
    public function one()
    {   
        trace(1234567890,'info');//记录日志
    	$uid = input('id');
    	$user = $this->userdb->oneuser($uid);
        // halt($user);
    	return $this->fetch('index/one',['oneuser'=>$user]);
    }
}
