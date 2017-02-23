<ul class="nav nav-tabs">
    <li <?php echo $this->controller->id=='admin' ? 'class="active"' : ''?>><?php echo CHtml::link('用户管理', array('admin/index'));?></li>
    <li <?php echo $this->controller->id=='announcement' ? 'class="active"' : ''?>><?php echo CHtml::link('公告管理', array('announcement/admin'));?></li>
    <!-- <li <?php //echo $this->controller->id=='feedback' ? 'class="active"' : ''?>><?php //echo CHtml::link('意见反馈', array('feedback/admin'));?></li> -->
    <li <?php echo $this->controller->id=='log' ? 'class="active"' : ''?>><?php echo CHtml::link('访问日志', array('log/index'));?></li>
</ul>