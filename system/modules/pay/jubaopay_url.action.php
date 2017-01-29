<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");
class jubaopay_url extends SystemAction {
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	
	public function qiantai(){	
		sleep(2);
		$out_trade_no = $_GET['/pay/jubaopay_url/qiantai/?payid'];	//商户订单号
		// file_put_contents('jubaopay_qiantai.txt','_GET--'.var_export($_GET,1)."\n",FILE_APPEND);
		// file_put_contents('jubaopay_qiantai.txt','get--'.$_GET['/pay/jubaopay_url/qiantai/?payid']."\n",FILE_APPEND);
		$dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$out_trade_no'");
		if(_is_mobile){
				if(!$dingdaninfo || $dingdaninfo['status'] == '未付款'){
		header("location:".WEB_PATH);			
		}else{
			if(empty($dingdaninfo['scookies'])){
				header("location:".WEB_PATH."/mobile/home/userbalance");
			}else{
				if($dingdaninfo['scookies'] == '1'){
					
					header("location:".WEB_PATH."/mobile/cart/paysuccess");
					exit;
				}else{
					header("location:".WEB_PATH."/mobile/cart/cartlist");
				}
					
			}
			
		}
		
}else{
	if(!$dingdaninfo || $dingdaninfo['status'] == '未付款'){
			_message("支付失败",WEB_PATH);			
		}else{
			if(empty($dingdaninfo['scookies'])){
				_message("充值成功!",WEB_PATH."/member/home/userbalance");
			}else{
				if($dingdaninfo['scookies'] == '1'){
					_message("支付成功!",WEB_PATH."/member/cart/paysuccess");
				}else{
					_message("商品还未购买,请重新购买商品!",WEB_PATH."/member/cart/cartlist");
				}
					
			}
		}
		
	}
	}
	public function houtai(){
		//echo 123;exit;
		$message		=trim($_REQUEST["message"]);
		$signature		=trim($_REQUEST["signature"]);
		
		include dirname(__FILE__).'/lib/jubaopay/jubaopay_enc.php';
		$jubaopay_enc=new jubaopay_enc(dirname(__FILE__).'/lib/jubaopay/jubaopay.ini');
		$jubaopay_enc->decrypt($message);
		// 校验签名，然后进行业务处理
		$result=$jubaopay_enc->verify($signature);
						
		$out_trade_no = $jubaopay_enc->getEncrypt("payid");
		$this->out_trade_no = $out_trade_no;	
		if(!$out_trade_no){
			echo "返回参数错误";exit;	
		}
	//	file_put_contents('jubaopay_houtai.txt'.'result--'.$result."\n",FILE_APPEND);
		//校验码正确.
		if($result == 1){
			$state = $jubaopay_enc->getEncrypt("state");
			$orderNo = $jubaopay_enc->getEncrypt("orderNo");
			$amount = intval($jubaopay_enc->getEncrypt("amount"));
		//	file_put_contents('jubaopay_houtai.txt'.'state--'.$state."\n",FILE_APPEND);
			if($state=="2"){				

				//如果需要应答机制则必须回写流,以success开头,大小写不敏感.	
				$ret = $this->jubaopay_chuli(intval($amount));
			//	file_put_contents('jubaopay_houtai.txt'.'ret--'.var_export($ret,1)."\n",FILE_APPEND);
				if( $ret == '充值完成' || $ret == '商品购买成功'){
					echo 'success';exit;
				}
				if($ret == '充值失败' || $ret == '商品购买失败'){
					echo $ret;exit;
				}
			}
						
		}else{
			echo "交易信息被篡改";
		}
	
		

	}

	private function jubaopay_chuli($amount){
		$pay_type =$this->db->GetOne("SELECT * from `@#_pay` where `pay_class` = 'jubaopay' and `pay_start` = '1'");
		$out_trade_no = $this->out_trade_no;	
		$this->db->Autocommit_start();
		$dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$out_trade_no' and `status` = '未付款' for update");
		if(!$dingdaninfo){	echo "fail";exit;}	//没有该订单,失败
		$c_money = intval($dingdaninfo['money']);			
		$uid = $dingdaninfo['uid'];
		$time = time();	
	
		$up_q1 = $this->db->Query("UPDATE `@#_member_addmoney_record` SET `pay_type` = '聚宝支付', `status` = '已付款' where `id` = '$dingdaninfo[id]' and `code` = '$dingdaninfo[code]'");
		$up_q2 = $this->db->Query("UPDATE `@#_member` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
		$up_q3 = $this->db->Query("INSERT INTO `@#_member_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '充值', '$c_money', '$time')");
		//file_put_contents('jubaopay_chuli.txt','up_q1--'.$up_q1.'up_q2--'.$up_q2.'up_q3--'.$up_q3."\n",FILE_APPEND);
		if($up_q1 && $up_q2 && $up_q3){
			//file_put_contents('jubaopay_chuli.txt','yes--'."\n",FILE_APPEND);
			$this->db->Autocommit_commit();			
		}else{
			$this->db->Autocommit_rollback();
			echo "充值失败";exit;
		}			
		if(empty($dingdaninfo['scookies'])){					
				echo "充值完成";exit;	//充值完成			
		}			
		$scookies = unserialize($dingdaninfo['scookies']);			
		$pay = System::load_app_class('pay','pay');			
		$pay->scookie = $scookies;	

		$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
		//file_put_contents('jubaopay_chuli.txt','ok--'.$ok."\n",FILE_APPEND);
		if($ok != 'ok'){
			_setcookie('Cartlist',NULL);
			echo "商品购买失败";exit;	//商品购买失败			
		}			
		$check = $pay->go_pay(1);
		//file_put_contents('jubaopay_chuli.txt','check--'.$check."\n",FILE_APPEND);
		if($check){
		//	file_put_contents('jubaopay_chuli.txt','buyyes--'."\n",FILE_APPEND);
			$this->db->Query("UPDATE `@#_member_addmoney_record` SET `scookies` = '1' where `code` = '$out_trade_no' and `status` = '已付款'");
			_setcookie('Cartlist',NULL);
			echo "商品购买成功";exit;			
		}else{
			echo "商品购买失败";exit;
		}	
	
	
	
	
	}
	

	
}//

?>