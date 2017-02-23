SELECT
    '{pt_day}',
    t.source_city_id,
    t.source_city_name,
    t.online_supplier_num,
    t.online_shop_num,
    t.online_good_num
FROM
    wowo_grp.grp_source_city_data t
WHERE
    t.dt = '{pt}'
