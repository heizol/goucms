<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
System::load_app_class('base','member','no');
System::load_app_fun('my','go');
System::load_app_fun('user','go');
System::load_sys_fun('send');
System::load_sys_fun('user');
class home extends base {
	public function __construct(){ 
		parent::__construct();
		$this->db = System::load_sys_class('model');
		
	}
	public function init(){
	    $user_id = $this->getUserId();
	    $webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$title="我的夺宝中心";	
		//$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");		
		
	 //获取夺宝等级  夺宝新手  夺宝小将==
// 	  $memberdj=$this->db->GetList("select * from `@#_member_group`");
	   
// 	  $jingyan=$member['jingyan'];
// 	  if(!empty($memberdj)){
// 	     foreach($memberdj as $key=>$val){
// 		    if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
// 			   $member['yungoudj']=$val['name'];
// 			}
// 		 }
// 	  }
	  
		include templates("mobile/user","index");
	}
  
	function invite(){
	    $user_id = $this->getUserId();
        $webname=$this->_cfg['web_name'];
        $member=$this->userinfo;
        $title="我的夺宝中心";
        //$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");

        //获取夺宝等级  夺宝新手  夺宝小将==
        $memberdj=$this->db->GetList("select * from `@#_member_group`");

        $jingyan=$member['jingyan'];
        if(!empty($memberdj)){
            foreach($memberdj as $key=>$val){
                if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
                    $member['yungoudj']=$val['name'];
                }
            }
        }
        include templates("mobile/user","invite");
    }
	/*收货地址*/
	function address(){
	    $user_id = $this->getUserId();
        $webname=$this->_cfg['web_name'];
        $mysql_model=System::load_sys_class('model');
        $member=$this->userinfo;
        $uid = $member['uid'];   
        $title="收货地址";
        //$quanzi=$this->db->GetList("select * from `@#_quanzi_tiezi` order by id DESC LIMIT 5");
		$member_dizhi=$mysql_model->Getlist("select * from `@#_member_dizhi` where uid='$uid' limit 5");
			foreach($member_dizhi as $k=>$v){		
				$member_dizhi[$k] = _htmtocode($v);
			}
		$count=count($member_dizhi);
        include templates("mobile/user","address");
    }
	  
    public function deleteaddress(){
        $user_id = $this->getUserId();
    	 $mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
        $uid = $member['uid'];
        $data = !empty($_POST) || isset($_POST) ? $_POST : NULL;
        $arr = "";
	
		foreach ($data as $ts) {
			foreach ($ts as $key) {
				$arr.=$key.",";
			}
		}
		$Str_arr = explode(",", $arr);
		$status = array();
		if(is_array($Str_arr)){
			$mysql_model->Autocommit_start();
			$query = $mysql_model->Query("DELETE FROM `@#_member_dizhi` WHERE `id` = ".$Str_arr[0]." and `uid` = ".$Str_arr[1]);
			$sql = "DELETE FROM `@#_member_dizhi` WHERE `id` = ".$Str_arr[0]." and `uid` = ".$Str_arr[1];
			file_put_contents("post.txt", $sql);
			if($query){
				$mysql_model->Autocommit_commit();
				$status['status'] = 1;
			}else{
				$mysql_model->Autocommit_rollback();
				$status['status'] = 2;
			}
		}else{
			$status['status'] = 0;
		}
    	//file_put_contents("post.txt", var_export($Str_arr,true));
    	echo json_encode($status);
    }

    public function updateaddress(){
        $user_id = $this->getUserId();
    	 $mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
        $uid = $member['uid'];
		$data = !empty($_POST) || isset($_POST) ? $_POST : NULL;
		$shouhuoname = preg_replace("/(\s|&nbsp;|　|\xc2\xa0)/", "",htmlspecialchars($data['ren']));
		$mobile = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($data['mobile']));
		$userQQ = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($data['qq']));
		$youbian = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($data['youbian']));
		$jiedao = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($data['jiedao']));
		$status = array();
		$mysql_model->Autocommit_start();
		$time = time();
		$sql = "UPDATE `@#_member_dizhi` SET ";
		$sql.= "`uid` = '$uid',`sheng` = '$data[sheng]',`shi` = '$data[shi]',`xian` = '$data[xian]',`jiedao` = '$jiedao'";
		$sql.=	",`youbian` = '$youbian',`shouhuoren` = '$shouhuoname',`mobile` = '$mobile',`qq` = '$userQQ',`time` = '$time' ";
		$sql.= "WHERE `id` = '$data[aid]'";
		$status['arr'] = $sql;
		$query = $mysql_model->Query($sql);

		if($query){
			$mysql_model->Autocommit_commit();
			$status['status'] = 1;
		}else{
			$mysql_model->Autocommit_rollback();
			$status['status'] = 2;
		}
		echo json_encode($status);
    }

    public function setdefault(){
    	$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
        $uid = $member['uid'];
        $aid = intval($this->segment(4));
        if(!$aid) _message("参数不正确!");
       
        $member_dizhi=$mysql_model->Getlist("select * from `@#_member_dizhi` where uid='".$uid."'");

        foreach($member_dizhi as $dizhi){
			if($dizhi['default']=='Y'){
				$mysql_model->Query("UPDATE `@#_member_dizhi` SET `default`='N' where uid='".$uid."'");
			}
		}

 		$mysql_model->Autocommit_start();
        $query = $mysql_model->Query("UPDATE `@#_member_dizhi` SET `default` = 'Y' WHERE `id` = ".$aid."");
        if($query){
			$mysql_model->Autocommit_commit();
			header("location:".WEB_PATH."/mobile/home/address");
        }else{
        	$mysql_model->Autocommit_rollback();
        	_message("操作失败!");
        }
    }
	public function useraddress(){
		 $mysql_model=System::load_sys_class('model');
		// $arr = explode(",",$_POST);
		$member=$this->userinfo;
        $uid = $member['uid'];
		$data = !empty($_POST) || isset($_POST) ? $_POST : NULL;

		$arr = "";
	
		foreach ($data as $ts) {
			foreach ($ts as $key) {
				$arr.=$key.",";
			}
		}
		$Str_arr = explode(",", $arr);
		$status = array();
		if(is_array($Str_arr)){
			$time = time();
			$mysql_model->Autocommit_start();
			$shouhuoname = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($Str_arr[0]));
			$mobile = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($Str_arr[1]));
			$userQQ = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($Str_arr[2]));
			$youbian = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($Str_arr[3]));
			$jiedao = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "",htmlspecialchars($Str_arr[7]));
			$values = "VALUES('$uid','$Str_arr[4]','$Str_arr[5]','$Str_arr[6]','$jiedao','$youbian','$shouhuoname','$mobile','$userQQ','$time')";
			$query = $mysql_model->Query("INSERT INTO `@#_member_dizhi`(`uid`,`sheng`,`shi`,`xian`,`jiedao`,`youbian`,`shouhuoren`,`mobile`,`qq`,`time`)$values");
		
			if($query){
				$mysql_model->Autocommit_commit();
				$status['status'] = 1;
			}else{
				$mysql_model->Autocommit_rollback();
				$status['status'] = 2;
			}
		}else{
			$status['status'] = 0;
		}
		//file_put_contents("post.txt", var_export($Str_arr,true));
		echo json_encode($status);
	}

	public function results(){
		if($this->segment(4) == 'result'){
			$ids = $this->segment(5)==1 ? "添加成功!" : "添加失败!";
		}
		if($this->segment(4) == 'error'){
			$ids = "数据参数错误!";
		}
		if($this->segment(4) == 'delete'){
			$ids = "删除失败!";
		}
		_messagemobile("$ids");
	}
	  
	/* 
	public function password(){
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$title="密码修改";	
		include templates("member","password");
	}
	public function oldpassword(){
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		if($member['password']==md5($_POST['param'])){
			echo '{
					"info":"",
					"status":"y"
				}';
		}else{
			echo "原密码错误";
		}
	}
	public function userpassword(){
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		//$member=$mysql_model->GetOne("select * from `@#_member` where uid='".$member['uid']."'");	
		$password=isset($_POST['password']) ? $_POST['password'] : "";
		$userpassword=isset($_POST['userpassword']) ? $_POST['userpassword'] : "";
		$userpassword2=isset($_POST['userpassword2']) ? $_POST['userpassword2'] : "";
		if($password==null or $userpassword==null or $userpassword2==null){
				echo "密码不能为空;";
				exit;
			}
		if($_POST['password']<6 and $_POST['password']<20){
			echo "密码小于6位数";
			exit;
		}
		if($_POST['userpassword']!=$_POST['userpassword2']){
			echo "新密码不一致";
			exit;
		}		
		$password=md5($password);
		$userpassword=md5($userpassword);
		if($member['password']!=$password){
			echo _message("原密码错误",null,3);
		}else{
			$mysql_model->Query("UPDATE `@#_member` SET password='".$userpassword."' where uid='".$member['uid']."'");
			echo _message("密码修改成功",null,3);
		}
	} */
	
	//夺宝记录
	public function userbuylist(){
	   $webname=$this->_cfg['web_name'];
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$uid = $member['uid'];
		$title="夺宝记录";					
		//$record=$mysql_model->GetList("select * from `@#_member_go_record` where `uid`='$uid' ORDER BY `time` DESC");		
		$record=$mysql_model->GetList("select *,sum(gonumber) as gonumber from `@#_member_go_record` a left join `@#_shoplist` b on a.shopid=b.id where a.uid='$member[uid]' GROUP BY shopid ");
		$record = count($record);
		$announced=$mysql_model->GetList("select *,sum(gonumber) as gonumber from `@#_member_go_record` a left join `@#_shoplist` b on a.shopid=b.id where a.uid='$member[uid]' and b.q_end_time is not null GROUP BY shopid " );	
		$announced = count($announced);		
		include templates("mobile/user","userbuylist");
	}
	//夺宝记录详细
	public function userbuydetail(){
	    $webname=$this->_cfg['web_name'];
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$title="夺宝详情";
		$crodid=intval($this->segment(4));
		$record=$mysql_model->GetOne("select * from `@#_member_go_record` where `id`='$crodid' and `uid`='$member[uid]' LIMIT 1");		
		if($crodid>0){
			include templates("member","userbuydetail");
		}else{
			echo _message("页面错误",WEB_PATH."/member/home/userbuylist",3);
		}
	}
	//获得的商品
	public function orderlist(){
	    $webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$title="获得的商品";
		//$record=$this->db->GetList("select * from `@#_member_go_record` where `uid`='".$member['uid']."' and `huode`>'10000000' ORDER BY id DESC");				
		include templates("mobile/user","orderlist");
	}
	//账户管理
	public function userbalance(){
	    $webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$title="账户记录";
		$account=$this->db->GetList("select * from `@#_member_account` where `uid`='$member[uid]' and `pay` = '账户' ORDER BY time DESC");
         $czsum=0;
         $xfsum=0;
		if(!empty($account)){ 
			foreach($account as $key=>$val){
			  if($val['type']==1){
				$czsum+=$val['money'];		  
			  }else{
				$xfsum+=$val['money'];		  
			  }		
			} 		
		}
		
		include templates("mobile/user","userbalance");
	}
	
	 
	public function userrecharge(){
	    $webname=$this->_cfg['web_name'];
		$member=$this->userinfo;
		$title="账户充值";
		//$paylist = $this->db->GetList("SELECT * FROM `@#_pay` where `pay_start` = '1'");
 	
		include templates("mobile/user","recharge");
	}	
	/*
	public function pay(){
		if(isset($_POST['submit'])){
			$mid = TENPAY_MID; //商户编号 
			$key = TENPAY_KEY; //商户密钥
			$desc = '夺宝系统'; //商品名称   			  
			$oid = date("YmdHis").rand(100,999); //商户订单号   
			$pri = $_POST['money']*100; //总价(单位:分)   
			$url = WEB_PATH.'/member/home/tenpaysuccess'; //回调地址   			
			header("location:".makeUrl($key,$desc,$mid,$oid,$pri,$url)); 			
		}
	}
	public function tenpaysuccess(){
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$cmd_no         = $_GET['cmdno'];
		$pay_result     = $_GET['pay_result'];
		$pay_info       = $_GET['pay_info'];
		$bill_date      = $_GET['date'];
		$bargainor_id   = $_GET['bargainor_id'];
		$transaction_id = $_GET['transaction_id'];
		$sp_billno      = $_GET['sp_billno'];
		$total_fee      = $_GET['total_fee']/100+$member['money'];
		$fee_type       = $_GET['fee_type'];
		$attach         = $_GET['attach'];
		$sign           = $_GET['sign'];
		if($pay_result<1){
			$mysql_model->Query("UPDATE `@#_member` SET money='".$total_fee."' where uid='".$member['uid']."'");
			_message("支付成功",WEB_PATH.'/member/home/userbalance',3);
		}
	} 
	*/
	//晒单
	public function singlelist(){
		 $webname=$this->_cfg['web_name'];
		include templates("mobile/user","singlelist");
	}	
/*	
	//添加晒单
	public function postsingle(){	
		$member=$this->userinfo;
		$uid=_getcookie('uid');
		$ushell=_getcookie('ushell');
		$title="添加晒单";		
		if(isset($_POST['submit'])){
	
			if($_POST['title']==null)_message("标题不能为空");	
			if($_POST['content']==null)_message("内容不能为空");	
			if(!isset($_POST['fileurl_tmp'])){
				_message("图片不能为空");
			}
			System::load_sys_class('upload','sys','no');
			$img=$_POST['fileurl_tmp'];
			$num=count($img);
			$pic="";
			for($i=0;$i<$num;$i++){
				$pic.=trim($img[$i]).";";
			}
			
			$src=trim($img[0]);
			$size=getimagesize(G_UPLOAD_PATH."/".$src);
			$width=220;
			$height=$size[1]*($width/$size[0]);
			
			$thumbs=tubimg($src,$width,$height);				
			$uid=$this->userinfo;
			$sd_userid=$uid['uid'];
			$sd_shopid=$_POST['shopid'];
			$sd_title=$_POST['title'];
			$sd_thumbs="shaidan/".$thumbs;
			$sd_content=$_POST['content'];
			$sd_photolist=$pic;
			$sd_time=time();			
			$this->db->Query("INSERT INTO `@#_shaidan`(`sd_userid`,`sd_shopid`,`sd_title`,`sd_thumbs`,`sd_content`,`sd_photolist`,`sd_time`)VALUES
			('$sd_userid','$sd_shopid','$sd_title','$sd_thumbs','$sd_content','$sd_photolist','$sd_time')");
			_message("晒单分享成功",WEB_PATH."/member/home/singlelist");
		}
		$recordid=intval($this->segment(4));
		if($recordid>0){
			$shaidan=$this->db->GetOne("select * from `@#_member_go_record` where `id`='$recordid'");		
			$shopid=$shaidan['shopid'];
			include templates("member","singleinsert");
		}else{
			_message("页面错误");
		}
	}
	//编辑
	public function PostSingleEdit(){
		_message("不可编辑!");
		if(isset($_POST['submit'])){
			System::load_sys_class('upload','sys','no');
			if($_POST['title']==null)_message("标题不能为空");	
			if($_POST['content']==null)_message("内容不能为空");				
			$sd_id=$_POST['sd_id'];
			$shaidan=$this->db->GetOne("select * from `@#_shaidan` where `sd_id`='$sd_id'");			
			$pic=null;$thumbs=null;
			if(isset($_POST['fileurl_tmp'])){
				if($shaidan['sd_photolist']==null){				
					$img=$_POST['fileurl_tmp'];
					$num=count($img);
					for($i=0;$i<$num;$i++){
						$pic.=trim($img[$i]).";";
					}
					$src=trim($img[0]);
					$size=getimagesize(G_UPLOAD_PATH."/".$src);
					$width=220;
					$height=$size[1]*($width/$size[0]);			
					$thumbs=tubimg($src,$width,$height);
				}else{
					$img=$_POST['fileurl_tmp'];
					$num=count($img);
					for($i=0;$i<$num;$i++){
						$pic.=$img[$i].";";
					}
				}
			}
			if($thumbs!=null){
				$sd_thumbs=$thumbs;
			}else{
				$sd_thumbs=$shaidan['sd_thumbs'];
			}
			$uid=$this->userinfo;
			$sd_userid=$uid['uid'];
			$sd_shopid=$shaidan['sd_shopid'];
			$sd_title=$_POST['title'];
			$sd_content=$_POST['content'];
			$sd_photolist=$pic.$shaidan['sd_photolist'];
			$sd_time=time();			
			$this->db->Query("UPDATE `@#_shaidan` SET
			`sd_userid`='$sd_userid',
			`sd_shopid`='$sd_shopid',
			`sd_title`='$sd_title',
			`sd_thumbs`='$sd_thumbs',
			`sd_content`='$sd_content',
			`sd_photolist`='$sd_photolist',
			`sd_time`='$sd_time' where sd_id='$sd_id'");
			_message("晒单修改成功",WEB_PATH."/member/home/singlelist");
		}
		$member=$this->userinfo;
		$title="修改晒单";	
		$uid=_getcookie('uid');
		$ushell=_getcookie('ushell');
		$sd_id=intval($this->segment(4));
		if($sd_id>0){
			$shaidan=$this->db->GetOne("select * from `@#_shaidan` where `sd_id`='$sd_id'");
			include templates("member","singleupdate");
		}else{
			_message("页面错误");
		}	
	}
	public function singoldimg(){
		if($_POST['action']=='del'){
			$sd_id=$_POST['sd_id'];
			$oldimg=$_POST['oldimg'];
			$shaidan=$this->db->GetOne("select * from `@#_shaidan` where `sd_id`='$sd_id'");
			$sd_photolist=str_replace($oldimg.";","",$shaidan['sd_photolist']);
			$this->db->Query("UPDATE `@#_shaidan` SET sd_photolist='".$sd_photolist."' where sd_id='".$sd_id."'");
		}
	}
	public function singphotoup(){
		 $mysql_model=System::load_sys_class('model');		
		if(!empty($_FILES)){			
			$uid=isset($_POST['uid']) ? $_POST['uid'] : NULL;		
			$ushell=isset($_POST['ushell']) ? $_POST['ushell'] : NULL;
			$login=$this->checkuser($uid,$ushell);
			if(!$login){_message("上传出错");}
			System::load_sys_class('upload','sys','no');
			upload::upload_config(array('png','jpg','jpeg','gif'),1000000,'shaidan');
			upload::go_upload($_FILES['Filedata']);
			if(!upload::$ok){
				echo _message(upload::$error,null,3);
			}else{
				$img=upload::$filedir."/".upload::$filename;					
				$size=getimagesize(G_UPLOAD_PATH."/shaidan/".$img);
				$max=700;$w=$size[0];$h=$size[1];
				if($w>700){
					$w2=$max;
					$h2=$h*($max/$w);
					upload::thumbs($w2,$h2,1);						
				}
					
				echo trim("shaidan/".$img);
			}					
		} 
	}	
	public function singdel(){
		$action=isset($_GET['action']) ? $_GET['action'] : null; 
		$filename=isset($_GET['filename']) ? $_GET['filename'] : null;
		if($action=='del' && !empty($filename)){
			$filename=G_UPLOAD_PATH.'shaidan/'.$filename;			
			$size=getimagesize($filename);			
			$filetype=explode('/',$size['mime']);			
			if($filetype[0]!='image'){
				return false;
				exit;
			}
			unlink($filename);
			exit;
		}
	}
	//晒单删除
	public function shaidandel(){
		_message("不可以删除!");
		$member=$this->userinfo;
		//$id=isset($_GET['id']) ? $_GET['id'] : "";
		$id=$this->segment(4);
		$id=intval($id);
		$shaidan=$this->db->Getone("select * from `@#_shaidan` where `sd_userid`='$member[uid]' and `sd_id`='$id'");
		if($shaidan){
			$this->db->Query("DELETE FROM `@#_shaidan` WHERE `sd_userid`='$member[uid]' and `sd_id`='$id'");
			_message("删除成功",WEB_PATH."/member/home/singlelist");
		}else{
			_message("删除失败",WEB_PATH."/member/home/singlelist");
		}
	}
	
 
	
		 
	*/
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
	
}

?>