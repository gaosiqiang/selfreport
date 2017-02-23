truncate table dcp_false_action;
load data local infile '{__DATA_PATH}/dcp_false_action.txt' into table dcp_false_action(
    user_area_name,
    user_city_id,
    user_city_name,
    user_position,
    user_name,
    user_code,
    start_date,
    follow_type,
    action_type,
    supplier_name,
    supplier_cat_id,
    supplier_cat_name,
    contact_name,
    contact_telphone,
    sale_area_name,
    sale_city_id,
    sale_city_name,
    sale_position,
    sale_name,
    sale_code
);
