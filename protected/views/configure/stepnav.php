<ul class="nav nav-pills nav-stacked">
    <?php if ($action == 'create' && $visiable >= 1) {?>
    <li <?php echo $this->route == 'configure/create'&&$step==1 ? 'class="active"' : ''?>><?php echo CHtml::link('Step1.名称与路径', array('configure/create', 'step'=>1, 'report_id'=>$report_id));?></li>
    <?php } elseif ($action == 'update') {?>
    <li <?php echo $this->route == 'configure/update'&&$step==1 ? 'class="active"' : ''?>><?php echo CHtml::link('Step1.名称与路径', array('configure/update', 'step'=>1, 'report_id'=>$report_id));?></li>
    <?php }?>
    
    <?php if ($action == 'create' && $visiable >= 2) {?>
    <li <?php echo $this->route == 'configure/create'&&$step==2 ? 'class="active"' : ''?>><?php echo CHtml::link('Step2.查询配置', array('configure/create', 'step'=>2, 'report_id'=>$report_id));?></li>
    <?php } elseif ($action == 'update') {?>
    <li <?php echo $this->route == 'configure/update'&&$step==2 ? 'class="active"' : ''?>><?php echo CHtml::link('Step2.查询配置', array('configure/update', 'step'=>2, 'report_id'=>$report_id));?></li>
    <?php }?>
    
    <?php if ($action == 'create' && $visiable >= 3) {?>
    <li <?php echo $this->route == 'configure/create'&&$step==3 ? 'class="active"' : ''?>><?php echo CHtml::link('Step3.展示内容', array('configure/create', 'step'=>3, 'report_id'=>$report_id));?></li>
    <?php } elseif ($action == 'update') {?>
    <li <?php echo $this->route == 'configure/update'&&$step==3 ? 'class="active"' : ''?>><?php echo CHtml::link('Step3.展示内容', array('configure/update', 'step'=>3, 'report_id'=>$report_id));?></li>
    <?php }?>
    
    <?php if ($action == 'create' && $visiable >= 4) {?>
    <li <?php echo $this->route == 'configure/create'&&$step==4 ? 'class="active"' : ''?>><?php echo CHtml::link('Step4.权限分配', array('configure/create', 'step'=>4, 'report_id'=>$report_id));?></li>
    <?php } elseif ($action == 'update') {?>
    <li <?php echo $this->route == 'configure/update'&&$step==4 ? 'class="active"' : ''?>><?php echo CHtml::link('Step4.权限分配', array('configure/update', 'step'=>4, 'report_id'=>$report_id));?></li>
    <?php }?>
    
    <?php if ($action == 'create' && $visiable >= 5) {?>
    <li <?php echo $this->route == 'configure/create'&&$step==5 ? 'class="active"' : ''?>><?php echo CHtml::link('Step5.数据项定义', array('configure/create', 'step'=>5, 'report_id'=>$report_id));?></li>
    <?php } elseif ($action == 'update') {?>
    <li <?php echo $this->route == 'configure/update'&&$step==5 ? 'class="active"' : ''?>><?php echo CHtml::link('Step5.数据项定义', array('configure/update', 'step'=>5, 'report_id'=>$report_id));?></li>
    <?php }?>
    
    <?php if ($action == 'create' && $visiable >= 6) {?>
    <li <?php echo $this->route == 'configure/create'&&$step==6 ? 'class="active"' : ''?>><?php echo CHtml::link('Step6.图表配置', array('configure/create', 'step'=>6, 'report_id'=>$report_id));?></li>
    <?php } elseif ($action == 'update') {?>
    <li <?php echo $this->route == 'configure/update'&&$step==6 ? 'class="active"' : ''?>><?php echo CHtml::link('Step6.图表配置', array('configure/update', 'step'=>6, 'report_id'=>$report_id));?></li>
    <?php }?>
    
    <?php if ($action == 'create' && $visiable == 7) {?>
    <li <?php echo $this->route == 'configure/create'&&$step==7 ? 'class="active"' : ''?>><?php echo CHtml::link('Step7.查询预览', array('configure/create', 'step'=>7, 'report_id'=>$report_id));?></li>
    <?php } elseif ($action == 'update') {?>
    <li <?php echo $this->route == 'configure/update'&&$step==7 ? 'class="active"' : ''?>><?php echo CHtml::link('Step7.查询预览', array('configure/update', 'step'=>7, 'report_id'=>$report_id));?></li>
    <?php }?>
</ul>
