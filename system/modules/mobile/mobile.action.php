<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
System::load_app_class('base','member','no');
System::load_app_fun('my','go');
System::load_app_fun('user','go');
System::load_sys_fun('user');

class mobile extends base {
	
	public function __construct() {
	    session_start();
		parent::__construct();
		$this->db=System::load_sys_class('model');
	}	

	//首页
	public function init(){
	    if (stristr($_SERVER['REQUEST_URI'], 'callwxback') && !empty($_GET['code'])) {
	        echo 'helloworld';
	        $this->callwxback($_GET['code']);
	    } else {
	        echo 'system wrong !!';
	        exit;
	    }
	    
// 	    header("Location: /?/mobile/mobile/glist");
// 			$webname=$this->_cfg['web_name'];
 
		
// 		//人气商品
// 		$shoplistrenqi=$this->db->GetList("select * from `@#_shoplist` where `renqi`='1' and `q_end_time` is null ORDER BY id DESC LIMIT 6");
	
// 		$max_renqi_qishu = 1;
// 		$max_renqi_qishu_id = 1;
		
// 		if(!empty($shoplistrenqi)){
// 			foreach ($shoplistrenqi as $renqikey =>$renqiinfo){
// 				if($renqiinfo['qishu'] >= $max_renqi_qishu){			
// 					$max_renqi_qishu = $renqiinfo['qishu'];
// 					$max_renqi_qishu_id = $renqikey;				
// 				}		
// 			}	
// 			$shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;	
// 		}				
// 		$this_time = time();
// 		if(count($shoplistrenqi) > 1){
// 					if($shoplistrenqi[0]['time'] > $this_time - 86400*3)
// 					$shoplistrenqi[0]['t_new_goods'] = 1;
// 		}
		
		
// 		$w_jinri_time = strtotime(date('Y-m-d'));
// 		$w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));
	

		
		
// 		//最新揭晓
// 		$currentTime = time();
// 		$shopqishu=$this->db->GetList("select * from `@#_shoplist` where `q_end_time` !='' and `q_end_time` < '$currentTime' ORDER BY `q_end_time` DESC LIMIT 4");
		
		
// 		$jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_jinri_time' and `xsjx_time` < '$w_minri_time' order by xsjx_time limit 0,3 ");
		
		
		
//          $key="首页";
// 		include templates("mobile/index","index");
	}
	
	/**
	 * @desc 限制微信登录
	 */
	public function callwxback($code) {
	    $appid = WX_APPID; //"wx312453bf54f34f20";
	    $secret = WX_APPSECRET;
	    $code = trim(htmlentities($code));
	    if (empty($code)) {
	        echo 'system wrong!';
	        exit;
	    }
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
	        print_r($_user_obj);exit;
	        $get_wx_info = array();
	        $get_wx_info['openid'] = $user_obj['openid'];
	        $get_wx_info['username'] = $user_obj['nickname'];
	        $get_wx_info['user_sex'] = $user_obj['sex'];
	        $get_wx_info['img'] = $user_obj['headimgurl'];
	        $get_wx_info['password'] = md5('111111');
	        if (empty($_user_obj)) {
	            $get_wx_info['add_time'] = date("Y-m-d H:i:s");
	            $get_wx_info['login_time'] = time();
	            $sql = "insert into `@#_member`(open_id, username, user_sex, img, password, login_time, add_time) values('{$get_wx_info['openid']}', '{$get_wx_info['username']}', '{$get_wx_info['user_sex']}', '{$get_wx_info['img']}', '{$get_wx_info['password']}', '{$get_wx_info['login_time']}', '{$get_wx_info['add_time']}')";
	            $this->db->Query($sql);
	            $id = $this->db->insert_id();
	            $user_obj['nickname'] = $get_wx_info['username'];
	        } else {
	            $id = $_user_obj[false]['id'];
	            $login_time = time();
	            $sql = "UPDATE `@#_member` SET login_time='{$login_time}' where `uid`='$id'";
	            $this->db->Query($sql);
	        }
	        $_SESSION['user_id'] = $id;
	        $_SESSION['username'] = $user_obj['nickname'];
	        print_r($_SESSION);
	       // header('Location: /?/mobile/mobile/glist');
	    } else {
	        echo '该平台只能在微信中登录';
	        exit;
	    }
	   exit;
	}
	//商品列表
	public function glist(){
	    echo 'haha';
	    print_r($_SESSION);
	    exit;
	    $this->getUserId();
        $webname=$this->_cfg['web_name'];	
		$title="商品列表_"._cfg("web_name");
		$key="所有商品";
		include templates("mobile/index","glist");
	}
	//ajax获取商品列表信息
	public function glistajax(){
	    $this->getUserId();
	    $webname=$this->_cfg['web_name'];
		$cate_band =htmlspecialchars($this->segment(4));
		$select =htmlspecialchars($this->segment(5));
	
		$p =htmlspecialchars($this->segment(6)) ? $this->segment(6) :1;		
		
		$sun_id_str = "'".$cate_band."'";
		$sun_cate = $this->db->GetList("SELECT cateid from `@#_category` where `parentid` = '$cate_band'");
			foreach($sun_cate as $v){
				$sun_id_str .= ","."'".$v['cateid']."'";
			}
		
		if(!$select){
			$select = '10';
		}		
		if($cate_band){
			$fen1 = intval($cate_band);	
			$cate_band = 'list';			
		}
		if(empty($fen1)){
			$brand=$this->db->GetList("select * from `@#_brand` where 1 order by `order` DESC");	
			$daohang = '所有分类';	
		}else{
			$brand=$this->db->GetList("select * from `@#_brand` where `cateid`='$fen1' order by `order` DESC");
			//2015-1-26 k
			//$brand=$this->db->GetList("select * from `@#_brand` where `cateid` like '%".$fen1."%' order by `order` DESC");
			
			
			//$brand=$this->db->GetList("select * from `@#_brand` where  `cateid` like '%".$fen1."%' UNION select * from `@#_brand` where  `cateid` like '%".$fen1.",%' UNION select * from `@#_brand` where  `cateid` like '%,".$fen1."%' order by `order` DESC");
			$daohang=$this->db->GetOne("select * from `@#_category` where `cateid` = '$fen1' order by `order` DESC");			
			$daohang = $daohang['name'];			
		}	
			
		$category=$this->db->GetList("select * from `@#_category` where `model` = '1'");
		
		//分页
		
		$end=10;
		$star=($p-1)*$end;
		
		$select_w = '';
		if($select == 10){
			$select_w = 'order by `zongrenshu`-`shenyurenshu` DESC';
		}
		if($select == 20){
			$select_w = " and `q_end_time` is  null and `q_showtime` = 'N' and `shenyurenshu`!='0'  and `sid`=`id` order by `id` DESC";
		}
		if($select == 30){
			$select_w = 'order by `shenyurenshu` ASC';
		}
		if($select == 40){
			$select_w = " and `renqi` = '1' order by `id` DESC";
		}
		if($select == 50){
			$select_w = 'order by `money` DESC';
		}
		if($select == 60){
			$select_w = 'order by `money` ASC';
		}
		
		if($fen1){		
			$count=$this->db->GetList("select * from `@#_shoplist` where `q_uid` is null and `cateid` in ($sun_id_str) $select_w");
		}else{			
			$count=$this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w");
		}
		if($fen1){
			$shoplist=$this->db->GetList("select * from `@#_shoplist` where `q_uid` is null and `cateid` in ($sun_id_str) $select_w limit $star,$end");
			
		}else{		
			$shoplist=$this->db->GetList("select * from `@#_shoplist` where `q_uid` is null $select_w limit $star,$end");	
		} 
		$max_renqi_qishu = 1;
		$max_renqi_qishu_id = 1;

		if(!empty($shoplistrenqi)){
			foreach ($shoplistrenqi as $renqikey =>$renqiinfo){
				if($renqiinfo['qishu'] >= $max_renqi_qishu){			
					$max_renqi_qishu = $renqiinfo['qishu'];
					$max_renqi_qishu_id = $renqikey;				
				}		
			}	
			$shoplistrenqi[$max_renqi_qishu_id]['t_max_qishu'] = 1;	
		}	
		
		
		$this_time = time();
		if(count($shoplist) > 1){
					if($shoplist[0]['time'] > $this_time - 86400*3)
					$shoplist[0]['t_new_goods'] = 1;
		}
		$pagex=ceil(count($count)/$end);
		if($p<=$pagex){
			$shoplist[0]['page']=$p+1;
		}
		if($pagex>0){
			$shoplist[0]['sum']=$pagex;
		}else if($pagex==0){
			$shoplist[0]['sum']=$pagex;
		}
		
		echo json_encode($shoplist);
	}
	
	//商品详细
	public function item(){
	    $this->getUserId();
	    $webname=$this->_cfg['web_name'];
		$key="商品详情";
		$mysql_model=System::load_sys_class('model');
		$itemid=safe_replace($this->segment(4));	
		
		$item=$mysql_model->GetOne("select * from `@#_shoplist` where `id`='".$itemid."' LIMIT 1");
		if(!$item)_messagemobile("商品不存在！");
		if($item['q_end_time']){
			header("location: ".WEB_PATH."/mobile/mobile/dataserver/".$item['id']);
			exit;			
		}
		$sid=$item['sid'];
		$sid_code=$mysql_model->GetOne("select * from `@#_shoplist` where `sid`='$sid' order by `id` DESC LIMIT 1,1");
		$sid_go_record=$mysql_model->GetOne("select * from `@#_member_go_record` where `shopid`='$sid_code[sid]' and `uid`='$sid_code[q_uid]' order by `id` DESC LIMIT 1");
		
		
		$category=$mysql_model->GetOne("select * from `@#_category` where `cateid` = '$item[cateid]' LIMIT 1");
		$brand=$mysql_model->GetOne("select * from `@#_brand` where `id`='$item[brandid]' LIMIT 1");
		
		$title=$item['title'];
		$syrs=$item['zongrenshu']-$item['canyurenshu'];
		$item['picarr'] = unserialize($item['picarr']) ;
		
		
		$us=$mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='".$itemid."' AND `shopqishu`='".$item['qishu']."'ORDER BY id DESC LIMIT 6");
		
		//$us2=$mysql_model->GetList("select * from `@#_member_go_record` where `shopid`='".$itemid."' AND `shopqishu`='".$item['qishu']."'ORDER BY id DESC");
		
		$itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' and `q_end_time` is not null order by `qishu` DESC");
		
		//期数显示
		$loopqishu='';
		$loopqishu.='<li class="cur"><a href="javascript:;">'."第".$item['qishu']."期</a><b></b></li>";
		 
		if(!empty($itemlist)){
    		foreach($itemlist as $qitem){			
    			$loopqishu.='<li><a href="'.WEB_PATH.'/mobile/mobile/item/'.$qitem['id'].'" class="">第'.$qitem['qishu'].'期</a></li>';		
    			
    		}
    		
    		foreach($itemlist as $qitem){
    			if($qitem['id'] == $itemid){
    				
    				$loopqishu.='<li class="cur"><a href="javascript:;">'."第".$itemlist[0]['qishu']."期</a><b></b></li>";
    			}else{				
    				$loopqishu.='<li><a href="'.WEB_PATH.'/mobile/mobile/dataserver/'.$qitem['id'].'" >第'.$qitem['qishu'].'期</a></li>';		
    			}
    		}
    		$gorecode=array();
    		//查询上期的获奖者信息
    		$gorecode=$this->db->GetOne("select * from `@#_member_go_record` where `shopid`='".$itemlist[0]['id']."' AND `shopqishu`='".$itemlist[0]['qishu']."' ORDER BY id DESC LIMIT 1");
		}
		
		//echo "<pre>";
		//print_r($itemlist);
		//echo microt($itemlist[0]['q_end_time']);exit;
		 $curtime=time();
         $shopitem='itemfun';
		 
		//晒单数
		$shopid=$this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
		$shoplist=$this->db->GetList("select * from `@#_shoplist` where `sid`='$shopid[sid]'");
		$shop='';
		foreach($shoplist as $list){
			$shop.=$list['id'].',';
		}
		$id=trim($shop,',');
		if($id){
			$shaidan=$this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id)");
			$sum=0;
			foreach($shaidan as $sd){
				$shaidan_hueifu=$this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
				$sum=$sum+count($shaidan_hueifu);
			}
		}else{
			$shaidan=0;
			$sum=0;
		}
		
		include templates("mobile/index","item");
	}
	
	//往期商品查看
	public function dataserver(){
	    $this->getUserId();
	    $webname=$this->_cfg['web_name'];
		$key="揭晓结果";
		$itemid=intval($this->segment(4));
		$item=$this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid' and `q_end_time` is not null LIMIT 1");
		if(!$item){
			_messagemobile("商品不存在！");
		}
		if(empty($item['q_user_code'])){
			_messagemobile("该商品正在进行中...");
		}
		
		$itemlist = $this->db->GetList("select * from `@#_shoplist` where `sid`='$item[sid]' order by `qishu` DESC");
		$category=$this->db->GetOne("select * from `@#_category` where `cateid` = '$item[cateid]' LIMIT 1");
		$brand=$this->db->GetOne("select * from `@#_brand` where `id` = '$item[brandid]' LIMIT 1");
		
		//云购中奖码
		$q_user = unserialize($item['q_user']);		
		$q_user_code_len = strlen($item['q_user_code']);
		$q_user_code_arr = array();
		for($q_i=0;$q_i < $q_user_code_len;$q_i++){	
			$q_user_code_arr[$q_i] = substr($item['q_user_code'],$q_i,1);
		}

		//期数显示
		$loopqishu='';
		if(empty($itemlist[0]['q_end_time'])){	
			$loopqishu.='<li><a href="'.WEB_PATH.'/mobile/mobile/item/'.$itemlist[0]['id'].'">'."第".$itemlist[0]['qishu']."期</a><b></b></li>";
			array_shift($itemlist);
		}
			
		foreach($itemlist as $qitem){
			if($qitem['id'] == $itemid){
				
				$loopqishu.='<li class="cur"><a href="javascript:;">'."第".$qitem['qishu']."期</a><b></b></li>";
			}else{				
				$loopqishu.='<li><a href="'.WEB_PATH.'/mobile/mobile/dataserver/'.$qitem['id'].'" >第'.$qitem['qishu'].'期</a></li>';		
			}
		}
		 
		//总云购次数
		$user_shop_number = 0;
		//用户云购时间
		$user_shop_time = 0;
		//得到云购码
		$user_shop_codes = '';
		 
		$user_shop_list = $this->db->GetList("select * from `@#_member_go_record` where `uid`= '$item[q_uid]' and `shopid` = '$itemid' and `shopqishu` = '$item[qishu]'");
		foreach($user_shop_list as $user_shop_n){
			$user_shop_number += $user_shop_n['gonumber'];
			if($user_shop_n['huode']){
				$user_shop_time = $user_shop_n['time'];
				$user_shop_codes = $user_shop_n['goucode'];
			}
		}
		
		$h=abs(date("H",$item['q_end_time']));
		$i=date("i",$item['q_end_time']);
		$s=date("s",$item['q_end_time']);		
		$w=substr($item['q_end_time'],11,3);	
		$user_shop_time_add = $h.$i.$s.$w;
		$user_shop_fmod = fmod($user_shop_time_add*100,$item['canyurenshu']);
		
		if($item['q_content']){
			$item['q_content'] = unserialize($item['q_content']);
		}
        $item['picarr'] = unserialize($item['picarr']) ;
		
		//记录	 
		$itemzx=$this->db->GetOne("select * from `@#_shoplist` where `sid`='$item[sid]' and `qishu`>'$item[qishu]' and `q_end_time` is null order by `qishu` DESC LIMIT 1");
		
	    $gorecode=$this->db->GetOne("select * from `@#_member_go_record` where `shopid`='".$itemid."' AND `shopqishu`='".$item['qishu']."' and `uid`= '$item[q_uid]'ORDER BY id DESC LIMIT 6");
        
		$shopitem='dataserverfun';
		$curtime=time();
		//晒单数
		$shopid=$this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
		$shoplist=$this->db->GetList("select * from `@#_shoplist` where `sid`='$shopid[sid]'");
		$shop='';
		foreach($shoplist as $list){
			$shop.=$list['id'].',';
		}
		$id=trim($shop,',');
		if($id){
			$shaidan=$this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id)");
			$sum=0;
			foreach($shaidan as $sd){
				$shaidan_hueifu=$this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
				$sum=$sum+count($shaidan_hueifu);
			}
		}else{
			$shaidan=0;
			$sum=0;
		}
		$itemxq=0;
		if(!empty($itemzx)){
		  $itemxq=1;
		}
		 
		include templates("mobile/index","item");
	}
	
	
	
	
	//************************************************//
	//************************************************//
	//************************************************//
	
	public function tenpaysuccess(){
	    $webname=$this->_cfg['web_name'];
		$code= _getcookie('CODE');
		if(!isset($_GET['attach'])){
			_messagemobile("页面错误!");
			exit;
		}
		if(!$code){
			_messagemobile("页面错误!");
			exit;
		}
		$mysql_model=System::load_sys_class('model');
		$member=$this->userinfo;
		$total_fee      = $_GET['total_fee']/100+$member['money'];
		$attach         = $_GET['attach'];
		$sign           = $_GET['sign'];
		if($pay_result<1){			
			$mysql_model->Query("UPDATE `@#_member` SET money='".$total_fee."' where uid='".$member['uid']."'");			
			$shop=explode("&",$attach);
			gopay($member,$shop[0],$shop[1],$shop[2]);
		}
	}	

	//最新揭晓
	public function lottery(){
	    $this->getUserId();
	     $webname=$this->_cfg['web_name'];
		//最新揭晓
		$shopqishu=$this->db->GetList("select * from `@#_shoplist` where `q_end_time` is not null ORDER BY `q_end_time` DESC LIMIT 0,4");	
		
		
		$shoplist=$this->db->GetList("select * from `@#_shoplist` where 1 ORDER BY `canyurenshu` DESC LIMIT 4");
		$member_record=$this->db->GetList("select * from `@#_member_go_record` order by id DESC limit 6");
		$key="最新揭晓";
		include templates("mobile/index","lottery");
	}
	
	//商品购买记录
	public function buyrecords(){	
	    $webname=$this->_cfg['web_name'];
		$key="所有云购记录";
		$itemid=intval($this->segment(4));
		$cords=$this->db->GetList("select * from `@#_member_go_record` where `shopid`='$itemid'");
		if(!$cords){
			_messagemobile('还没有购买记录!');
		}
		include templates("mobile/index","buyrecords");
	}
	//图文详细
	public function goodsdesc(){
	    $this->getUserId();
	    $webname=$this->_cfg['web_name'];
		$key="图文详情";
		$itemid=intval($this->segment(4));
		$desc=$this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid'");
		if(!$desc){
			_messagemobile('页面错误!');
		}
		include templates("mobile/index","goodsdesc");
	}
	//晒单评论
	public function goodspost(){
	    $this->getUserId();
	    $webname=$this->_cfg['web_name'];
		$key="晒单评论";
		$itemid=intval($this->segment(4));
		$shoplist=$this->db->GetList("select * from `@#_shoplist` where `sid`='$itemid'");
		if(!$shoplist){
			_messagemobile('页面错误!');
		}
		$shop='';
		foreach($shoplist as $list){
			$shop.=$list['id'].',';
		}
		$id=trim($shop,',');
		if($id){
			$shaidan=$this->db->GetList("select * from `@#_shaidan` where `sd_shopid` IN ($id) order by `sd_id` DESC");
			$sum=0;
			foreach($shaidan as $sd){
				$shaidan_hueifu=$this->db->GetList("select * from `@#_shaidan_hueifu` where `sdhf_id`='$sd[sd_id]'");
				$sum=$sum+count($shaidan_hueifu);
			}
		}else{
			$shaidan=0;
			$sum=0;
		}
		include templates("mobile/index","goodspost");
	}
	
	public function calResult(){
	  $itemid=intval($this->segment(4));
	  	$item=$this->db->GetOne("select * from `@#_shoplist` where `id`='$itemid' and `q_end_time` is not null LIMIT 1");
	  
	    $h=abs(date("H",$item['q_end_time']));
		$i=date("i",$item['q_end_time']);
		$s=date("s",$item['q_end_time']);		
		$w=substr($item['q_end_time'],11,3);	
		$user_shop_time_add = $h.$i.$s.$w;
		$user_shop_fmod = fmod($user_shop_time_add*100,$item['canyurenshu']);
		
		if($item['q_content']){
			$item['q_content'] = unserialize($item['q_content']);

			$user_shop_time_add = $item['q_counttime'];
			$user_shop_fmod = fmod($user_shop_time_add,$item['canyurenshu']);
		}
        
        $item['picarr'] = unserialize($item['picarr']) ;
	  
	  include templates("mobile/index","calResult");
	}
	//新手指南
	public function about(){
	 $webname=$this->_cfg['web_name'];
	 $category=$this->db->GetOne("select * from `@#_category` where `parentid`='1' and `name`='新手指南'");
	 
	 $article=$this->db->GetList("select * from `@#_article` where `cateid`='$category[cateid]'");
	 
	include templates("mobile/index","about");
	}	
	
	
	//用户服务协议
	public function terms(){
	  $webname=$this->_cfg['web_name'];
	 $category=$this->db->GetOne("select * from `@#_category` where `parentid`='1' and `name`='新手指南'");
	 
	 $article=$this->db->GetOne("select * from `@#_article` where `cateid`='$category[cateid]' and `title`='服务协议' ");
	//echo "<pre>";
	//print_r($article);
	  include templates("mobile/system","terms");
	}	
	
	//访问个人主页
	public function userindex(){
	  $this->getUserId();
	  $webname=$this->_cfg['web_name'];
	  $uid=safe_replace($this->segment(4));
	  //$uid=intval($this->segment(4))-1000000000;
	  //获取个人资料
	  $member=$this->db->GetOne("select * from `@#_member` where `uid`='$uid'");
	  
	  //获取云购等级  云购新手  云购小将==
	  $memberdj=$this->db->GetList("select * from `@#_member_group`");
	   
	  $jingyan=$member['jingyan'];
	  if(!empty($memberdj)){
	     foreach($memberdj as $key=>$val){
		    if($jingyan>=$val['jingyan_start'] && $jingyan<=$val['jingyan_end']){
			   $member['yungoudj']=$val['name'];
			}
		 }
	  }  
	  include templates("mobile/index","userindex");
	}
 
	/*
	//今日揭晓
	public function autolottery(){
	    $w_jinri_time = strtotime(date('Y-m-d'));
		$w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));
		
		$jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_jinri_time' and `xsjx_time` < '$w_minri_time' order by xsjx_time limit 0,3 ");
		include templates("mobile/index","buyrecords");
	
	}
    
	//明日揭晓
	public function nextautolottery(){		 
		$w_minri_time = strtotime(date('Y-m-d',strtotime("+1 day")));
		$w_houri_time = strtotime(date('Y-m-d',strtotime("+2 day")));
		
		$jinri_shoplist = $this->db->GetList("select * from `@#_shoplist` where `xsjx_time` > '$w_minri_time' and `xsjx_time` < '$w_houri_time' order by xsjx_time limit 0,3 ");
	}*/
	
}
?>