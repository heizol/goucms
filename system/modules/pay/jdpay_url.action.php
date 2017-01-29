<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");
include_once G_SYSTEM.'modules/pay/lib/jdpay/common/DesUtils.php';
include_once G_SYSTEM.'modules/pay/lib/jdpay/common/ConfigUtil.php';
class jdpay_url extends SystemAction {
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	
	/*返回*/
	
	public function qiantai($out_trade_no){	
		sleep(2);
		$v_oid = isset($_GET['tradeNum']) ? $_GET['tradeNum'] : $out_trade_no;
		$this->db->Autocommit_start();
		$dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$v_oid' for update");
		$this->db->Autocommit_rollback();		
		if(!$dingdaninfo || $dingdaninfo['status'] == '未付款'){
			_message("支付失败");		
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
	
	
	public function houtai(){
		$pay_type=$this->db->GetOne("select * from `@#_pay` where `pay_class`='jdpay' and `pay_start`='1'");
		
 		$resp = $_REQUEST['resp'];
 		if (null == $resp) {
			return;
		}

		$desKey = ConfigUtil::get_val_by_key ( "desKey" );
		$md5Key = ConfigUtil::get_val_by_key ( "md5Key" );
		
		$params = $this->xml_to_array ( base64_decode ( $resp ) );

		$ownSign = $this->generateSign ( $params, $md5Key );
		$params_json = json_encode ( $params );
		//file_put_contents('houtai.txt','params--'.var_export($params,1).'ownSign--'.$ownSign."\n",FILE_APPEND);
		if ($params ['SIGN'] [0] == $ownSign) {
			
			// 验签正确
			//echo "签名验证正确!" . "\n";
			$des = new DesUtils (); // （秘钥向量，混淆向量）
			$decryptArr = $des->decrypt ( $params ['DATA'] [0], $desKey ); // 加密字符串
			$params ['data'] = $decryptArr;
			$respone = $this->xml_to_array($decryptArr);			
			//file_put_contents('houtai.txt','respone--'.var_export($respone,1)."\n",FILE_APPEND);
			if($respone['RETURN']['code'] == '0000'|| $respone['RETURN']['DESC'] == '成功'){			//数据库操作
				
				$v_oid = $respone['TRADE']['ID'];
				
				$this->db->Autocommit_start();
				$dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$v_oid' and `status` = '未付款' for update");
				if(!$dingdaninfo){	return;}	//没有该订单,失败
				$c_money = intval($dingdaninfo['money']);			
				$uid = $dingdaninfo['uid'];
				$time = time();			
				$up_q1 = $this->db->Query("UPDATE `@#_member_addmoney_record` SET `pay_type` = '京东支付', `status` = '已付款' where `id` = '$dingdaninfo[id]' and `code` = '$dingdaninfo[code]'");
				$up_q2 = $this->db->Query("UPDATE `@#_member` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
				$up_q3 = $this->db->Query("INSERT INTO `@#_member_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '京东支付充值', '$c_money', '$time')");
					
				if($up_q1 && $up_q2 && $up_q3){
					$this->db->Autocommit_commit();			
				}else{
					$this->db->Autocommit_rollback();
					return;
				}			
				if(empty($dingdaninfo['scookies'])){						//充值完成	
						return;		
				}			
				$scookies = unserialize($dingdaninfo['scookies']);			
				$pay = System::load_app_class('pay','pay');			
				$pay->scookie = $scookies;	

				$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
				if($ok != 'ok'){
					_setcookie('Cartlist',NULL);	//商品购买失败	
					return;		
				}			
				$check = $pay->go_pay(1);
				if($check){
					$this->db->Query("UPDATE `@#_member_addmoney_record` SET `scookies` = '1' where `code` = '$v_oid' and `status` = '已付款'");
					_setcookie('Cartlist',NULL);
					return;	
				}else{
					return;
				}

				}else{
					return;
			}
			
		} else {
			//echo "签名验证错误!" . "\n";
			return;
		}
		}	


		

	public function xml_to_array($xml) {
		$array = ( array ) (simplexml_load_string ( $xml ));
		foreach ( $array as $key => $item ) {
			$array [$key] = $this->struct_to_array ( ( array ) $item );
		}
		return $array;
	}
	public function struct_to_array($item) {
		if (! is_string ( $item )) {
			$item = ( array ) $item;
			foreach ( $item as $key => $val ) {
				$item [$key] = $this->struct_to_array ( $val );
			}
		}
		return $item;
	}
	
	/**
	 * 签名
	 */
	public function generateSign($data, $md5Key) {
		$sb = $data ['VERSION'] [0] . $data ['MERCHANT'] [0] . $data ['TERMINAL'] [0] . $data ['DATA'] [0] . $md5Key;
		
		return md5 ( $sb );
	}
	
	
}//

?>