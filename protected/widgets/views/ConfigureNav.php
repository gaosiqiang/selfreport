<ul class="nav nav-tabs">
    <li <?php echo $this->controller->id=='configure' ? 'class="active"' : ''?>><?php echo CHtml::link('报表管理', array('configure/index'));?></li>
    <li <?php echo $this->controller->id=='datasource' ? 'class="active"' : ''?>><?php echo CHtml::link('数据源管理', array('datasource/index'));?></li>
    <li <?php echo $this->controller->id=='usergroup' ? 'class="active"' : ''?>><?php echo CHtml::link('用户组管理', array('usergroup/index'));?></li>
    <!-- <li <?php //echo $this->controller->id=='menu' ? 'class="active"' : ''?>><?php //echo CHtml::link('菜单管理', array('menu/index'));?></li> -->
</ul>
