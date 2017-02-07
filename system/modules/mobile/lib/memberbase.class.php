<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
define("MEMBER",true);
System::load_sys_fun("user");
class memberbase extends SystemAction {
	protected $userinfo=NULL;	
	public function __construct(){
	    session_start();
		$this->db = System::load_sys_class("model");
		$uid=$_SESSION['user_id'];		
// 		$ushell=_encrypt(_getcookie("ushell"),'DECODE');
		if(!$uid)$this->userinfo=false;
		$this->userinfo=$this->db->GetOne("SELECT * from `@#_member` where `uid` = '$uid'");
		if(!$this->userinfo)$this->userinfo=false;	
	
// 		$shell=md5($this->userinfo['uid'].$this->userinfo['password'].$this->userinfo['mobile'].$this->userinfo['email']);		
// 		if($ushell!=$shell)$this->userinfo=false;
	}
	
	protected function checkuser($uid,$ushell){
		$uid=$_SESSION['user_id'];
// 		$ushell=_encrypt($ushell,'DECODE');	
		if(!$uid)return false;
// 		if($ushell===NULL)return false;
		$this->userinfo=$this->db->GetOne("SELECT * from `@#_member` where `uid` = '$uid'");
		if(!$this->userinfo){
			$this->userinfo=false;
			return false;
		}
// 		$shell=md5($this->userinfo['uid'].$this->userinfo['password'].$this->userinfo['mobile'].$this->userinfo['email']);
// 		if($ushell!=$shell){
// 			$this->userinfo=false;
// 			return false;
// 		}else{
			return true;
// 		}
		
	}
	public function get_user_info(){
		if($this->userinfo){
			return $this->userinfo;
		}else{
			return false;
		}
	}
	protected function HeaderLogin(){
		_message("你还未登录，无权限访问该页！",WEB_PATH."/mobile/user/login");
	}
	
}
?>