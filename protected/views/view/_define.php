<div class="row page-header header-container">
    <div class="col-md-12">
        <h4>数据项定义</h4>
    </div>
</div>
<div class="row table-container">
    <table class="table table-striped table-bordered detail">
        <tbody>
            <tr>
                <th width="30%">数据项名称</th>
                <th width="70%">定义</th>
            </tr>
            <?php if(!empty($columns_info)){
                foreach ($columns_info as $column => $info){
                    if (array_key_exists($column, $city_div_cols)) {
                        $divs = $city_div_cols[$column];
                        foreach ($divs as $k => $div) {
                            if (array_key_exists($div, $columns_info_citydiv)) {
            ?>
            <tr>
                <td width="30%" style="text-align:left;"><?php echo !empty($columns_info_citydiv[$div]['show_name']) ? $columns_info_citydiv[$div]['show_name'] : $div;?></td>
                <td width="70%" style="text-align:left;"><?php echo $columns_info_citydiv[$div]['define'];?></td>
            </tr>
            <?php }}}?>
            <tr>
                <td width="30%" style="text-align:left;"><?php echo !empty($info['show_name']) ? $info['show_name'] : $column;?></td>
                <td width="70%" style="text-align:left;"><?php echo $info['define'];?></td>
            </tr>
            <?php }}?>
        </tbody>
    </table>
</div>
