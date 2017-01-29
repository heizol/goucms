<?php
include dirname(__FILE__).DIRECTORY_SEPARATOR."cbpay".DIRECTORY_SEPARATOR."cbpay_submit.class.php";
System::load_sys_fun('global');
class cbpay{
	private $config;
	private $url;
	//主入口
	public function config($config=null){
		$this->config = $config;
		$this->config_jsdz();
	}

	//及时到账
	private function config_jsdz(){
		$this->db=System::load_sys_class('model');
		$cbpay_config = $this->config;
		$cbpay_config1 = array();
		$cbpay_config1['v_mid'] = trim($cbpay_config['id']);
		$cbpay_config2['key'] = trim($cbpay_config['key']);
		$cbpay_config1['v_oid'] = date('Ymd',time())."-".$cbpay_config1['v_mid']."-".date('His',time());
		$cbpay_config1['v_amount'] = trim($cbpay_config['money']);
		$cbpay_config1['v_moneytype'] = "CNY";
		$cbpay_config1['v_url'] = trim($cbpay_config['NotifyUrl']);
		$cbpay_config1['pmode_id'] = trim($cbpay_config['bankid']);
		$cbpay_config1['remark2'] = "[url:=".$cbpay_config['NotifyUrl']."]";
		$cbpay_config1['v_md5info'] = strtoupper(md5($cbpay_config1['v_amount'].$cbpay_config1['v_moneytype'].$cbpay_config1['v_oid'].$cbpay_config1['v_mid'].$cbpay_config1['v_url'].$cbpay_config2['key']));
		//更新订单号
		$q1 = $this->db->Query("UPDATE `@#_member_addmoney_record` SET `code` = '{$cbpay_config1['v_oid']}' where `code` = '{$cbpay_config['code']}'");
		if(empty($q1)){
			_message("操作失败");
		}
		
		$cbpaySubmit = new CbpaySubmit($cbpay_config1);
		
		
		
		$this->url = $cbpaySubmit->buildRequestForm($cbpay_config1,'POST','submit');
	}

	//发送
	public function send_pay(){
		 echo  $this->url;
		 exit;
		//header("Location: $url");	
	}



}