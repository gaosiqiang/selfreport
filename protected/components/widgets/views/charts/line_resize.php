<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
    <div id="main1" style="height:400px;width:100%;"></div>
    <!-- ECharts单文件引入 -->
    <script type="text/javascript">
        var width = $("#main").css("width");
        if (width!='undefined') {
            $("#main<?php echo $key?>").css("width",width);
        }
        // 路径配置
        require.config({
            paths: {
                echarts: '<?php echo Yii::app()->request->baseUrl; ?>/static/js/echarts'
            }
        });
        
        // 使用
        require(
            [
                'echarts',
                'echarts/chart/line',
                'echarts/chart/bar',
                'echarts/chart/scatter',
                'echarts/chart/k',
                'echarts/chart/pie',
                'echarts/chart/radar',
                'echarts/chart/force',
                'echarts/chart/chord',
                'echarts/chart/gauge',
                'echarts/chart/funnel',
                'echarts/chart/eventRiver'
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                var myChart = ec.init(document.getElementById('main1')); 
                
                var option = {
                    tooltip : {
                        trigger: 'axis'
                    },
                    toolbox: {
                        show : true,
                        orient : 'vertical',
                        feature : {
                            mark : {show: true},
                            dataZoom : {show: true},
                            dataView : {show: true},
                            magicType : {show: true, type: ['line', 'bar']},
                            restore : {show: true},
                            saveAsImage : {show: true}
                        }
                    },
                    dataZoom : {
                        show : true,
                        realtime : true,
                        //orient: 'vertical',   // 'horizontal'
                        //x: 0,
                        y: 36,
                        //width: 400,
                        height: 20,
                        //backgroundColor: 'rgba(221,160,221,0.5)',
                        //dataBackgroundColor: 'rgba(138,43,226,0.5)',
                        //fillerColor: 'rgba(38,143,26,0.6)',
                        //handleColor: 'rgba(128,43,16,0.8)',
                        //xAxisIndex:[],
                        //yAxisIndex:[],
                        start : 0,
                        end : 60
                    },
                    xAxis : [
                        {
                            type : 'category',
                            boundaryGap : false,
                            data : [<?php echo $dataCharts['xAxisData'];?>],
                            splitLine: {
                            show:false
                     }
                            
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value',
                            splitLine: {
                            show:true,
                                lineStyle: {
                                    color: '#EEE',
                                    width: 2
                                }
                            }
                        }
                    ],
                    series : [
                    <?php foreach ($dataCharts['series'] as  $value) {?>
                        {
                            name:'<?php echo $value['name']?>',
                            type:'line',
                            data:[<?php echo $value['data']?>]
                            
                        }
                        <?php } ?>
                    ],
                    calculable:false
                };
        
                // 为echarts对象加载数据 
                myChart.setOption(option); 
            }
        );
    </script>