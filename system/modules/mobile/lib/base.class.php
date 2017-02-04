<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
define("MEMBER",true);

class base extends SystemAction {
	protected $userinfo=NULL;
	public function __construct(){	
	    session_start();
	
// 		if(ROUTE_M=='member' && ROUTE_C=='user' && ROUTE_A=='login'){
// 			return;
// 		}
// 		if(ROUTE_M=='member' && ROUTE_C=='user' && ROUTE_A=='register'){
// 			return;
// 		}
// 		$uid= $_SESSION['user_id']; //intval(_encrypt(_getcookie("uid"),'DECODE'));		
// // 		$utype=_encrypt(_getcookie("utype"),'DECODE');
// // 		$ushell=_encrypt(_getcookie("ushell"),'DECODE');
	
// // 		if($utype===NULL)$this->HeaderLogin();
// 		if(!$uid)$this->HeaderLogin();
// 		$this->userinfo=$this->DB()->GetOne("SELECT * from `@#_member` where `uid` = '$uid'");
// 		if(!$this->userinfo) {
// 		    $this->HeaderLogin();		
// 		}
// 		$shell=md5($this->userinfo['uid'].$this->userinfo['password'].$this->userinfo[$utype]);		
// 		if($ushell!=$shell)	$this->HeaderLogin();
	}
	
	private function HeaderLogin(){
		_message("",WEB_PATH."/?/mobile/mobile/",3);
	}
	
	public function getUserId() {
	    $user_id = $_SESSION['user_id'];
	    if (empty($user_id)) {
	        // 跳转微信
	        $app_id = WX_APPID; // Yii::$app->params['wx_appid'];
	        $redirect_uri = 'http://duobao.joinear.com/mobile/mobile/callWxBack';
	        $scope = 'snsapi_userinfo'; // SCOPE
	        $wx_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$app_id.'&redirect_uri='.$redirect_uri.'&response_type=code&scope='.$scope.'&state=STATE#wechat_redirect';
	        header("Location:" . $wx_url);
	    } else {
	        return $user_id;
	    }
	}
	
	public function callWxBack() {
	    $appid = WX_APPID; //"wx312453bf54f34f20";
	    $secret = WX_APPSECRET;
	    $code = trim(htmlentities($_GET["code"]));
	     
	    $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
	    $ch = curl_init();
	    curl_setopt($ch,CURLOPT_URL,$get_token_url);
	    curl_setopt($ch,CURLOPT_HEADER,0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    $res = curl_exec($ch);
	    curl_close($ch);
	    $json_obj = json_decode($res,true);
	    $access_token = $json_obj['access_token'];
	    $openid = $json_obj['openid'];
	     
	    $get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $get_user_info_url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    $res = curl_exec($ch);
	    curl_close($ch);
	     
	    $user_obj = json_decode($res, true);
	    if (!empty($user_obj) && !empty($user_obj['openid'])) {
	        $_user_obj = $this->db->GetOne("SELECT * from `@#_member` where `open_id` = '{$user_obj['openid']}'");
	         
	        $get_wx_info = array();
	        $get_wx_info['openid'] = $user_obj['openid'];
	        $get_wx_info['username'] = $user_obj['nickname'];
	        $get_wx_info['user_sex'] = $user_obj['sex'];
	        $get_wx_info['img'] = $user_obj['headimgurl'];
	        $get_wx_info['password'] = md5('111111');
	        if (empty($_user_obj)) {
	            $get_wx_info['add_time'] = date("Y-m-d H:i:s");
	            $get_wx_info['login_time'] = time();
	            $sql = "insert into `@#_member`(openid, username, user_sex, img, password, login_time, add_time) values('{$get_wx_info['openid']}', '{$get_wx_info['username']}', '{$get_wx_info['user_sex']}', '{$get_wx_info['img']}', '{$get_wx_info['password']}', '{$get_wx_info['login_time']}', '{$get_wx_info['add_time']}')";
	            $this->db->Query($sql);
	        } else {
	            $id = $_user_obj[false]['id'];
	            $login_time = time();
	            $sql = "UPDATE `@#_member` SET login_time='{$login_time}' where `uid`='$id'";
	            $this->db->Query($sql);
	        }
	        $_SESSION['user_id'] = $id;
	        $_SESSION['username'] = $user_obj['nickname'];
	        return $this->redirect('/?/mobile/mobile/glist');
	    } else {
	        echo '该平台只能在微信中登录';
	        exit;
	    }
	
	}
	
}
?>