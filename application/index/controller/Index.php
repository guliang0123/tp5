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
        //$addr = getAddrByIp('101.69.252.186');
        //$addr = url('welcome');
        //halt(getAddrByIp(getip()));
    	$userlist = $this->userdb->userList();
    	return $this->fetch('index/index',['userlist'=>$userlist]);
        
    }
    public function index2()
    {
        return $this->fetch();
    }
    //vue ajax请求数据
    public function index3()
    {
        $userlist = $this->userdb->userList2();
        echo json_encode($userlist);
    }
    //单个用户
    public function one()
    {   
        //trace(1234567890,'info');//记录日志
    	$uid = input('id');
    	$user = $this->userdb->oneuser($uid);
        // halt($user);
    	return $this->fetch('index/one',['oneuser'=>$user]);
    }
}
