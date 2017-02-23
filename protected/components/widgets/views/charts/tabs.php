<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="main" style="height:450px;width:100%;">
    <!-- ECharts单文件引入 -->
    <ul class="nav nav-tabs">
        <?php $show = false;?>
        <?php 
            $effective_num = 0;
            foreach ($dataChartsAll as $key => $dataCharts) { 
                if (array_key_exists('xAxisData', $dataCharts) && !empty($dataCharts['xAxisData']) && 
                    array_key_exists('yAxisData', $dataCharts) && !empty($dataCharts['yAxisData'])) {
                    $show = true;
                    ++$effective_num;
        ?>
            <li <?php if($effective_num==1){?>class="active"<?php }?>><a href="#tab-pane<?php echo $key?>" id="tab<?php echo $key?>"><?php echo $dataCharts['title']?></a></li>
        <?php }}?>
    </ul>
    <div class="tab-content">
        <?php 
            $effective_num = 0;
            foreach ($dataChartsAll as $key => $dataCharts) { 
                if (array_key_exists('xAxisData', $dataCharts) && !empty($dataCharts['xAxisData']) && 
                    array_key_exists('yAxisData', $dataCharts) && !empty($dataCharts['yAxisData'])) {
                    $show = true;
                    ++$effective_num;
        ?>
            <div id="tab-pane<?php echo $key?>" class="tab-pane <?php if($effective_num==1){?>active<?php }?>">
                <div class="charts-container">
                    <div class="charts" id="chart<?php echo $key?>">
                        <?php
                            $this->render('charts/'.$dataCharts['chart'], array(
                                'dataCharts' => $dataCharts,
                                'key' => $key
                            ));
                        ?>
                    </div>
                </div>
            </div>
        <?php 
                }
            }
        ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    //render_chart1();
    <?php if (!$show) { ?>
        $("#main").remove();
    <?php }?>
});
<?php foreach ($dataChartsAll as $key => $dataCharts) { ?>
$('#tab<?php echo $key?>').click(function (e) {
    e.preventDefault();
    $(this).tab('show');
    //render_chart1();
})
<?php }?>
</script>