<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
    <div id="main<?php echo $key?>" style="height:400px;width:100%;"></div>
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
                'echarts/chart/<?php echo $dataCharts['chart'] ;?>' ,// 使用柱状图就加载bar模块，按需加载
                'echarts/chart/bar', // 使用柱状图就加载bar模块，按需加载
                'echarts/macarons', // 主题
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                var myChart = ec.init(document.getElementById('main<?php echo $key?>'),'macarons'); 
                
                var option = {
                    title : {
                        text: '<?php echo $dataCharts['title'] ;?>',
                        subtext: '<?php //echo $dataCharts['title_sub'] ;?>'
                    },
                    tooltip : {
                        trigger: 'axis'
                    },
                    legend: {
                        data:[<?php echo $dataCharts['yAxisData'];?>]
                    },
                    toolbox: {
                        show : true,
                        orient : 'vertical',
                        feature : {
                            mark : {show: true},
                            dataView : {show: true, readOnly: false},
                            magicType : {show: true, type: ['<?php echo $dataCharts['chart'] ;?>']},
                            restore : {show: true},
                            saveAsImage : {show: true}
                        }
                    },
                    <?php if($dataCharts['xAxisDataCnt']>30){?>
                    calculable : true,
                    dataZoom : {
                        show : true,
                        realtime : true,
                        start : 70,
                        end : 100
                    },
                    <?php }?>
                    xAxis : [
                        {
                            type : 'category',
                            boundaryGap : false,
                            color: '#eee',
                            data : [<?php echo $dataCharts['xAxisData'];?>],
                            axisLine:{
                                lineStyle:{
                                    color:'#8A8A8A', //刻度颜色
                                    width: 1,
                                    type: 'solid'
                                }
                            }
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value',
                            axisLabel : {
                                formatter: '{value}'
                            },
                    //         splitLine: {
                    // 　　　　        show:true,
                    //             lineStyle: {
                 //                    color: '#eee',
                 //                    width: 2
                 //                }
                    // 　　　　 },
                            axisLine:{
                                lineStyle:{
                                    color:'#8A8A8A', //刻度颜色
                                    width: 1,
                                    type: 'solid'
                                }
                            },
                            //splitArea : {show : true}
                        }
                    ],
                    series : [
                        <?php foreach ($dataCharts['series'] as  $value) {?>
                            {
                                name:'<?php echo $value['name']?>',
                                type:'<?php echo $value['type']?>',
                                data:[<?php echo $value['data']?>],
                                // markPoint : {
                                //     data : [
                                //         {type : 'max', name: '最大值'},
                                //         {type : 'min', name: '最小值'}
                                //     ]
                                // },
                                // markLine : {
                                //     data : [
                                //         {type : 'average', name: '平均值'}
                                //     ]
                                // }
                            },
                        <?php } ?>
                    ]
                };
        
                // 为echarts对象加载数据 
                myChart.setOption(option); 
            }
        );
    </script>