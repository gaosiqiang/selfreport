<div class="form-container">
    <div class="post" style="border:none">
        <h2><?php echo $model->title;?></h2>
        <div class="meta">
            <div class="tag">
                <div style="float:left;"><?php echo '发布时间：'.date('Y-m-d G:i', $model->create_time); ?></div>
                <div><?php echo '有效期：'.date('Y-m-d',$model->start_time).' ~ '.date('Y-m-d',$model->end_time); ?></div>
            </div>
        </div>
        <div class="content">
        <?php echo $model->content;?>
        </div>
    </div>
</div>
