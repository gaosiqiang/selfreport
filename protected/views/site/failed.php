<?php
$this->pageTitle=Yii::app()->name . ' - Login';
?>
<div class="container">
	<div class="content">
		<div class="form-container-site" style="padding-top: 100px;">
			<p class="alert alert-danger">
                <?php switch ($type) {
                    case 1:
                        echo "您的操作过于频繁，请明天再试";
                        break;
                    case 2:
                        echo "验证码失效，请1小时后重新登录";
                        break;
                    case 3:
                        echo "对不起，您没有此菜单的访问权限!";
                        break;
                    case 4:
                        echo "您没有自助报表平台的任何权限!";
                        break;
                    case 5:
                        echo "您的登录位置发生改变（IP或浏览器），请重新登录";
                        break;
                    case 6:
                        echo "您尚未登录自助报表平台，请<br><a href=\"".Yii::app()->params['selfreport']."/login\"><u>重新登录</u></a>";
                        break;
                }?>
			</p>
		</div>
	</div>
</div>