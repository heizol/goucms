<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
class wxpay_url extends SystemAction {
	public function __construct(){			
		$this->db=System::load_sys_class('model');
	}
    public function checkpay(){
        $out_trade_no = $_POST["out_trade_no"];
        $dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '".$out_trade_no."'");
        if($dingdaninfo['status']=='已付款'){
            echo json_encode(array('code'=>4,'msg'=>"支付成功!"));exit;

        }elseif($dingdaninfo['status']=='未付款'){
            echo json_encode(array('code'=>999,'msg'=>"等待!"));exit;
        }else{
            echo json_encode(array('code'=>0,'msg'=>"支付失败!"));exit;
        }
    }
    public function houtai(){

        $pay_type = $this->db->GetOne("select * from `@#_pay` where `pay_class` = 'wxpay'");
        include_once dirname(__FILE__)."/lib/wxpay/WxPayPubHelper.php";//引入文件需求
        $notify = new Notify_pub();
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        $arr=$notify->getData();
        $out_trade_no=$arr['out_trade_no'];
        $total_fee_t=$arr['total_fee']/100;

        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        if(is_array($arr)){
            $returnXml = $notify->returnXml();
            echo $returnXml;
        }
        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======

        if($notify->checkSign() == TRUE)
        {
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                //$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
                echo "通信出错!";exit;
            }
            elseif($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                //$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
                echo "业务出错!";exit;
            }else{
                $this->do_order($out_trade_no,$total_fee_t,$pay_type);
            }
            exit;
        }
        if(!isset($_POST["out_trade_no"]))
        {
            $out_trade_no = " ";
        }else{
            $out_trade_no = $_POST["out_trade_no"];
            //使用订单查询接口
            $orderQuery = new OrderQuery_pub();
            //设置必填参数

            $orderQuery->setParameter("out_trade_no","$out_trade_no");//商户订单号
            $time=time();

            //获取订单查询结果
            $orderQueryResult = $orderQuery->getResult();
            //商户根据实际情况设置相应的处理流程,此处仅作举例
            if ($orderQueryResult["return_code"] == "FAIL") {
                //echo "通信出错：".$orderQueryResult['return_msg']."<br>";
                //file_put_contents("ccc.txt","通信出错：".$orderQueryResult['return_msg']."\n",FILE_APPEND);
            }elseif($orderQueryResult["result_code"] == "FAIL"){
                echo "错误代码：".$orderQueryResult['err_code']."<br>";
                echo "错误代码描述：".$orderQueryResult['err_code_des']."<br>";

            }else{
                $total_fee_t = $orderQueryResult['total_fee']/100;
                $out_trade_no=$orderQueryResult['out_trade_no'];
                $this->do_order($out_trade_no,$total_fee_t,$pay_type);
            }

        }
    }
    private function do_order($out_trade_no,$total_fee_t,$pay_type){
        $this->db=System::load_sys_class('model');
        $this->db->Autocommit_start();
        $dingdaninfo = $this->db->GetOne("select * from `@#_member_addmoney_record` where `code` = '$out_trade_no' and `money` = '$total_fee_t' and `status` = '未付款' for update");
        if(!$dingdaninfo){
            echo "fail";exit;
        }
        $time = time();
        $up_q1 = $this->db->Query("UPDATE `@#_member_addmoney_record` SET `pay_type` = '微信支付', `status` = '已付款' where `id` = '$dingdaninfo[id]' and `code` = '$dingdaninfo[code]'");
        $up_q2 = $this->db->Query("UPDATE `@#_member` SET `money` = `money` + $total_fee_t where (`uid` = '$dingdaninfo[uid]')");
        $up_q3 = $this->db->Query("INSERT INTO `@#_member_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$dingdaninfo[uid]', '1', '账户', '充值', '$total_fee_t', '$time')");
        if($up_q1 && $up_q2 && $up_q3){
            $this->db->Autocommit_commit();
        }else{
            $this->db->Autocommit_rollback();
            echo "fail";exit;
        }
        if(empty($dingdaninfo['scookies'])){
            echo "success";exit;
        }
        $uid = $dingdaninfo['uid'];
        $scookies = unserialize($dingdaninfo['scookies']);
        $pay = System::load_app_class('pay','pay');
        $pay->scookie = $scookies;
        $ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品
        if($ok != 'ok'){
            _setcookie('Cartlist',NULL);
            echo "fail";exit;	//商品购买失败
        }
        $check = $pay->go_pay(1);
        if($check){
            $this->db->Query("UPDATE `@#_member_addmoney_record` SET `scookies` = '1' where `code` = '$out_trade_no' and `status` = '已付款'");
            _setcookie('Cartlist',NULL);
            echo "success";exit;
        }else{
            echo "fail";exit;
        }

    }
	
}//


?>