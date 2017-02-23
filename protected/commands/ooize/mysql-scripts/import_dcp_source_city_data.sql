delete from dcp_source_city_data where report_date = '{pt_day}';
load data local infile 'dcp_source_city_data.txt' into table dcp_source_city_data(
    report_date,
    source_city_id,
    source_city_name,
    online_supplier_num,
    online_shop_num,
    online_good_num
);
