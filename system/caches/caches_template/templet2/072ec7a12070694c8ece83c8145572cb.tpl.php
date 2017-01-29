<?php defined('G_IN_SYSTEM')or exit('No permission resources.'); ?>

<!DOCTYPE html>
<html>
<head><title>
	帐户充值 - <?php echo $webname; ?>触屏版
</title><meta content="app-id=518966501" name="apple-itunes-app" /><meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, user-scalable=no, maximum-scale=1.0" /><meta content="yes" name="apple-mobile-web-app-capable" /><meta content="black" name="apple-mobile-web-app-status-bar-style" /><meta content="telephone=no" name="format-detection" /><link href="<?php echo G_TEMPLATES_CSS; ?>/mobile/comm.css?v=130715" rel="stylesheet" type="text/css" /><link href="<?php echo G_TEMPLATES_CSS; ?>/mobile/member.css?v=130726" rel="stylesheet" type="text/css" /><script src="<?php echo G_TEMPLATES_JS; ?>/mobile/jquery190.js" language="javascript" type="text/javascript"></script><script id="pageJS" data="<?php echo G_TEMPLATES_JS; ?>/mobile/recharge.js" language="javascript" type="text/javascript"></script></head>
<body>
<div class="h5-1yyg-v1" id="loadingPicBlock">
    
<!-- 栏目页面顶部 -->


<!-- 内页顶部 -->

    <header class="g-header">
        <div class="head-l">
	        <a href="javascript:;" onclick="history.go(-1)" class="z-HReturn"><s></s><b>返回</b></a>
        </div>
        <h2>帐户充值</h2>
        <div class="head-r">
	        <a href="<?php echo WEB_PATH; ?>/mobile/home/userbalance" class="z-RCornerBtn"><i></i>帐户明细</a>
        </div>
    </header>

    <div class="g-Total gray9">您的当前余额：<span class="orange arial"><?php echo $member['money']; ?></span>元</div>
    <section class="clearfix g-member">
        <div class="g-Recharge">
	        <ul id="ulOption">
		        <li money="10"><a href="javascript:;" class="z-sel">10元<s></s></a></li>
		        <li money="20"><a href="javascript:;">20元<s></s></a></li>
		        <li money="30"><a href="javascript:;">30元<s></s></a></li>
		        <li money="100"><a href="javascript:;">100元<s></s></a></li>
		        <li money="200"><a href="javascript:;">200元<s></s></a></li>
		        <li><b><input type="text" class="z-init" placeholder="    输入金额" maxlength="8" /><s></s></b></li>
	        </ul>
	    </div>
	    <!--article class="clearfix mt10 m-round g-pay-ment g-bank-ct">
		    <ul id="ulBankList">
			    <li class="gray6">选择网银充值<em class="orange">10.00</em>元</li>
			    <li class="gray9" urm="CMBCHINA-WAP"><i class="z-bank-Roundsel"><s></s></i>招商银行</li>
			    <li class="gray9" urm="ICBC-WAP"><i class="z-bank-Round"><s></s></i>工商银行</li>
			    <li class="gray9" urm="CCB-WAP"><i class="z-bank-Round"><s></s></i>建设银行</li>
		    </ul>
	    </article-->
		 <article  class="clearfix mt10 m-round g-pay-ment g-bank-ct">
		      <ul id="ulBankList">
			    <li class="gray6"><s class="z-arrow"></s>选择支付方式</li> 
				<?php $paydata=$this->DB()->GetList("SELECT pay_class,pay_name FROM `@#_pay` WHERE `pay_start` = '1' and `pay_mobile` = '1'",array("type"=>1,"key"=>'',"cache"=>0)); ?>
				<?php $ln=1;if(is_array($paydata)) foreach($paydata AS $row): ?>
				<li class="gray9"  urm='<?php echo $row['pay_class']; ?>' bank="nobank"><i class="z-bank-Round"><s></s></i><?php echo $row['pay_name']; ?></li>	
				<?php  endforeach; $ln++; unset($ln); ?>			
				<?php if(defined('G_IN_ADMIN')) {echo '<div style="padding:8px;background-color:#F93; color:#fff;border:1px solid #f60;text-align:center"><b>This Tag</b></div>';}?>
		    </ul>
	    </article>
	    <div class="mt10 f-Recharge-btn">
		    <a id="btnSubmit" href="javascript:;" class="orgBtn">确认充值</a>
	    </div>
    </section>
    
<?php include templates("mobile/index","footer");?>
<script language="javascript" type="text/javascript">
  var Path = new Object();
  Path.Skin="<?php echo G_TEMPLATES_STYLE; ?>";  
  Path.Webpath = "<?php echo WEB_PATH; ?>";
  
var Base={head:document.getElementsByTagName("head")[0]||document.documentElement,Myload:function(B,A){this.done=false;B.onload=B.onreadystatechange=function(){if(!this.done&&(!this.readyState||this.readyState==="loaded"||this.readyState==="complete")){this.done=true;A();B.onload=B.onreadystatechange=null;if(this.head&&B.parentNode){this.head.removeChild(B)}}}},getScript:function(A,C){var B=function(){};if(C!=undefined){B=C}var D=document.createElement("script");D.setAttribute("language","javascript");D.setAttribute("type","text/javascript");D.setAttribute("src",A);this.head.appendChild(D);this.Myload(D,B)},getStyle:function(A,B){var B=function(){};if(callBack!=undefined){B=callBack}var C=document.createElement("link");C.setAttribute("type","text/css");C.setAttribute("rel","stylesheet");C.setAttribute("href",A);this.head.appendChild(C);this.Myload(C,B)}}
function GetVerNum(){var D=new Date();return D.getFullYear().toString().substring(2,4)+'.'+(D.getMonth()+1)+'.'+D.getDate()+'.'+D.getHours()+'.'+(D.getMinutes()<10?'0':D.getMinutes().toString().substring(0,1))}
Base.getScript('<?php echo G_TEMPLATES_JS; ?>/mobile/Bottom.js?v='+GetVerNum());
</script>
 
</div>
</body>
</html>
