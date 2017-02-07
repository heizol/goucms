<?php defined('G_IN_SYSTEM')or exit('No permission resources.'); ?><!-- 栏目页面顶部 -->

    <header class="header">
        <div class="fl" style="margin:0 auto;text-align:center;float:left;width:73%;color:#fff;font-size: 18px;height:43px;line-height:43px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1元夺宝</div>
        <div class="fr head-r">
            <a href="<?php echo WEB_PATH; ?>/mobile/user/login" class="z-Member"></a>
            <a id="btnCart" href="<?php echo WEB_PATH; ?>/mobile/cart/cartlist" class="z-shop"></a>
        </div>
    </header> 
    <!-- 栏目导航 -->
    <nav class="g-snav u-nav">
	    <div class="g-snav-lst"><a href="<?php echo WEB_PATH; ?>/mobile/mobile/glist" <?php if($key=="所有商品" ): ?>class="nav-crt"<?php endif; ?>>所有商品</a> <?php if($key=="所有商品" ): ?><s class="z-arrowh"></s><?php endif; ?></div>
		
		
	    <div class="g-snav-lst"><a href="<?php echo WEB_PATH; ?>/mobile/mobile/lottery" <?php if($key=="最新揭晓" ): ?>class="nav-crt"<?php endif; ?>>最新揭晓</a><?php if($key=="最新揭晓" ): ?><s class="z-arrowh"></s><?php endif; ?></div>
		<!--
	      <div class="g-snav-lst"><a href="<?php echo WEB_PATH; ?>/mobile/shaidan/" <?php if($key=="晒单" ): ?>class="nav-crt"<?php endif; ?>>晒单</a><?php if($key=="晒单" ): ?><s class="z-arrowh"></s><?php endif; ?></div>
		-->
    </nav>