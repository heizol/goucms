<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
define("MEMBER",true);
System::load_sys_fun("user");
class base extends SystemAction {
	protected $userinfo=NULL;	
	public function __construct(){
		$this->db = System::load_sys_class("model");
		$uid = $uid= $_SESSION['user_id'];
// 		$uid=intval(_encrypt(_getcookie("uid"),'DECODE'));		
// 		$ushell=_encrypt(_getcookie("ushell"),'DECODE');
		
		if(!$uid) {
		    $this->userinfo=false;
		} else {
		    $this->userinfo=$this->db->GetOne("SELECT * from `@#_member` where `uid` = '$uid'");
		}
// 		$shell=md5($this->userinfo['uid'].$this->userinfo['password'].$this->userinfo['mobile'].$this->userinfo['email']);		
// 		if($ushell!=$shell)$this->userinfo=false;
		global $_cfg;		
		$_cfg['userinfo']=$this->userinfo;
	}
	
	protected function checkuser($uid,$ushell){
		$uid=$_SESSION['user_id'];
		$ushell=_encrypt($ushell,'DECODE');	
// 		if(!$uid)return false;
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
		_message("你还未登录，无权限访问该页！",WEB_PATH."/member/user/login");
	}
	
	
	public function getUserId() {
	    $user_id = $_SESSION['user_id'];
	    if (empty($user_id)) {
	        // 跳转微信
	        $app_id = WX_APPID; // Yii::$app->params['wx_appid'];
	        $redirect_uri = urlencode('http://duobao.joinear.com/mobile/mobile/callwxback');
	        $scope = 'snsapi_userinfo'; // SCOPE
	        $wx_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$app_id.'&redirect_uri='.$redirect_uri.'&response_type=code&scope='.$scope.'&state=STATE#wechat_redirect';
	        header("Location:" . $wx_url);
	    } else {
	        return $user_id;
	    }
	    exit;
	}
	
}
?>