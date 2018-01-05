<?php 
namespace app\index\model\user;

use think\Model;
use think\DB;

class User extends Model
{
    public function userList()
    {
        $list = DB::name('users')->where('status',1)->paginate(10);
        return $list;
    }
    //选择字段，选择条数
	public function userList2()
	{
		$list = DB::name('users')->field('uid,username')->where('status',1)->limit(20)->select();
		return $list;
	}

	public function oneuser($uid)
	{	
		if(empty($uid)){
			return FALSE;
		}
		$list = DB::name('users')->where('uid',$uid)->find();
		return $list;
	}
}




















