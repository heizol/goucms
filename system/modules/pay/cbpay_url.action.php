<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");
class cbpay_url extends SystemAction {
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	
	
	public function qiantai($out_trade_no){	
		//sleep(2);
		$dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$out_trade_no'");
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
	
		//$key='test';
		$pay_type =$this->db->GetOne("SELECT * from `@#_pay` where `pay_name` = '网银在线'");	
		$pay_type['pay_key'] = unserialize($pay_type['pay_key']);
		$key = $pay_type['pay_key']['key']['val'];		//支付KEY
		//登陆后在上面的导航栏里可能找到“资料管理”，在资料管理的二级导航栏里有“MD5密钥设置” 
		//建议您设置一个16位以上的密钥或更高，密钥最多64位，但设置16位已经足够了
		//****************************************
	
		$v_oid     =trim($_POST['v_oid']);       // 商户发送的v_oid定单编号   
		$v_pmode   =trim($_POST['v_pmode']);    // 支付方式（字符串）   
		$v_pstatus =trim($_POST['v_pstatus']);   //  支付状态 ：20（支付成功）；30（支付失败）
		$v_pstring =trim($_POST['v_pstring']);   // 支付结果信息 ： 支付完成（当v_pstatus=20时）；失败原因（当v_pstatus=30时,字符串）； 
		$v_amount  =trim($_POST['v_amount']);     // 订单实际支付金额
		$v_moneytype =trim($_POST['v_moneytype']); //订单实际支付币种    
		$remark1   =trim($_POST['remark1' ]);      //备注字段1
		$remark2   =trim($_POST['remark2' ]);     //备注字段2
		$v_md5str  =trim($_POST['v_md5str' ]);   //拼凑后的MD5校验值  

		/**
		 * 重新计算md5的值
		 */
	                           
		$md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key));

		/**
		 * 判断返回信息，如果支付成功，并且支付结果可信，则做进一步的处理
		 */


		if ($v_md5str==$md5string)
		{
			if($v_pstatus=="20")
			{
				//支付成功，可进行逻辑处理！
				//商户系统的逻辑处理（例如判断金额，判断支付状态，更新订单状态等等）......

				//$this->db=System::load_sys_fun('global');
				//_g_triggerRequest(WEB_PATH."/pay/cbpay_url/qiantai?out_trade_no=".$out_trade_no);

				$this->db->Autocommit_start();
				$dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$v_oid' and `status` = '未付款' for update");
				if(!$dingdaninfo){	$this->qiantai($v_oid);}	//没有该订单,失败
				$c_money = intval($dingdaninfo['money']);			
				$uid = $dingdaninfo['uid'];
				$time = time();			
				$up_q1 = $this->db->Query("UPDATE `@#_member_addmoney_record` SET `pay_type` = '网银在线', `status` = '已付款' where `id` = '$dingdaninfo[id]' and `code` = '$dingdaninfo[code]'");
				$up_q2 = $this->db->Query("UPDATE `@#_member` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
				$up_q3 = $this->db->Query("INSERT INTO `@#_member_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '充值', '$c_money', '$time')");
					
				if($up_q1 && $up_q2 && $up_q3){
					$this->db->Autocommit_commit();			
				}else{
					$this->db->Autocommit_rollback();
					$this->qiantai($v_oid);
				}			
				if(empty($dingdaninfo['scookies'])){					
						$this->qiantai($v_oid);	//充值完成			
				}			
				$scookies = unserialize($dingdaninfo['scookies']);			
				$pay = System::load_app_class('pay','pay');			
				$pay->scookie = $scookies;	

				$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
				if($ok != 'ok'){
					_setcookie('Cartlist',NULL);
					$this->qiantai($v_oid);	//商品购买失败			
				}			
				$check = $pay->go_pay(1);
				if($check){
					$this->db->Query("UPDATE `@#_member_addmoney_record` SET `scookies` = '1' where `code` = '$v_oid' and `status` = '已付款'");
					_setcookie('Cartlist',NULL);
					$this->qiantai($v_oid);			
				}else{
					$this->qiantai($v_oid);
				}

				}else{
					$this->qiantai($v_oid);
			}
		}
		
			
	}//function end
	
}//

?>