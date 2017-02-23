<div class="post">
    <div class="title"><?php echo CHtml::link($data->title, array('announcement/view', 'id'=>$data->id), array('target'=>'_blank')); ?></div>
    <div class="meta row">
        <div class="author col-md-12">
            <?php echo '发布时间：'.date('Y-m-d H:i',$data->create_time); ?>&nbsp;&nbsp;
            <?php echo '有效期：'.date('Y-m-d',$data->start_time).' ~ '.date('Y-m-d',$data->end_time); ?>
        </div>
    </div>
    <div class="content">
        <?php echo $data->content;?>
    </div>
</div>