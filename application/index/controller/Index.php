<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\user\User;

class Index extends Controller
{	
	
	private $userdb;

	public function _initialize()
	{
		$this->userdb = new User();
	}

    public function index()
    {	
    	$userlist = $this->userdb->userList();
    	return $this->fetch('index/index',['userlist'=>$userlist]);
        
    }

    public function one()
    {
    	$uid = input('id');
    	$user = $this->userdb->oneuser($uid);
    	return $this->fetch('index/one',['oneuser'=>$user]);
    }
}
