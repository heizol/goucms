<?php defined('G_IN_SYSTEM')or exit('No permission resources.'); ?><footer class="footer">
	<div class="u-ft-nav" id="fLoginInfo">
	    <span><a href="<?php echo WEB_PATH; ?>/mobile/mobile/">首页</a><b></b></span>
		<span><a href="<?php echo WEB_PATH; ?>/mobile/mobile/about">新手指南</a><b></b></span>
		<span><a href="<?php echo WEB_PATH; ?>/mobile/user/login">登录</a><b></b></span>
		<span><a href="<?php echo WEB_PATH; ?>/mobile/user/register">注册</a></span>
	</div>
	<?php 
		$g_web_path = _cfg("web_path");
		$g_web_path = trim($g_web_path,"/");		
	 ?>
	<p class="m-ftA"><a href="<?php echo WEB_PATH; ?>/mobile/mobile">触屏版</a><a href="<?php echo $g_web_path; ?>/?PC_SEE=1" target="_blank">电脑版</a></p>
	<p class="grayc">客服热线<span class="orange">023-67898742</span><?php echo _cfg('web_copyright'); ?></p>
	<a id="btnTop" href="javascript:;" class="z-top" style="display:none;"><b class="z-arrow"></b></a>
</footer>