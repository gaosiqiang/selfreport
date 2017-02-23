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
                'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                var myChart = ec.init(document.getElementById('main<?php echo $key?>')); 
                
            var option = {
                    title : {
                        text: '<?php echo $dataCharts['title'] ;?>',
                        //subtext: '纯属虚构'
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
                            magicType : {show: true, type: ['line', 'bar']},
                            restore : {show: true},
                            saveAsImage : {show: true}
                        }
                    },
                    calculable : true,
                    xAxis : [
                        {
                            type : 'category',
                            data : [<?php echo $dataCharts['xAxisData'];?>]
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    series : [
                        <?php foreach ($dataCharts['series'] as  $value) {?>
                            {
                                name:'<?php echo $value['name']?>',
                                type:'<?php echo $value['type']?>',
                                data:[<?php echo $value['data']?>],
                                
                            },
                        <?php } ?>
                    ]
                };
                                    
    //             var option = {
                //     title : {
                //         text: '<?php echo $dataCharts['title'] ;?>',
                //         subtext: '<?php //echo $dataCharts['title_sub'] ;?>'
                //     },
                //     tooltip : {
                //         trigger: 'axis'
                //     },
                //     legend: {
                //         data:[<?php echo $dataCharts['yAxisData'];?>]
                //     },
                //     toolbox: {
                //         show : true,
                //         feature : {
                //             mark : {show: true},
                //             dataView : {show: true, readOnly: false},
                //             magicType : {show: true, type: ['<?php echo $dataCharts['chart'] ;?>']},
                //             restore : {show: true},
                //             saveAsImage : {show: true}
                //         }
                //     },
                //     calculable : true,
                //     xAxis : [
                //         {
                //             type : 'category',
                //             boundaryGap : false,
                //             data : [<?php echo $dataCharts['xAxisData'];?>]
                //         }
                //     ],
                //     yAxis : [
                //         {
                //             type : 'value',
                //             axisLabel : {
                //                 formatter: '{value}'
                //             }
                //         }
                //     ],
                    // series : [
                    //     <?php foreach ($dataCharts['series'] as  $value) {?>
                    //         {
                       //          name:'<?php echo $value['name']?>',
                       //          type:'<?php echo $value['type']?>',
                       //          data:[<?php echo $value['data']?>],
                                
                       //      },
                    //     <?php } ?>
                    // ]
                // };
        
                // 为echarts对象加载数据 
                myChart.setOption(option); 
            }
        );
    </script>