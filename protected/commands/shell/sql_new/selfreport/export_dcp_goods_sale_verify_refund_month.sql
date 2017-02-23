SELECT
    '{pt_month}',
    t.goods_id,
    t.goods_sname,
    t.first_cat_id,
    t.first_cat_name,
    t.second_cat_id,
    t.second_cat_name,
    CASE
        WHEN t.is_vender = 0 THEN
            '否'
        WHEN t.is_vender = 1 THEN
            '是'
        ELSE
            '否'
    END AS is_vender,
    t.supplier_id,
    t.supplier_name,
    t.industry_first_cat,
    CASE
        WHEN t.is_new_supplier = 0 THEN
            '否'
        WHEN t.is_new_supplier = 1 THEN
            '是'
        ELSE
            '否'
    END AS is_new_supplier,
    CASE
        WHEN t.is_first_online_supplier = 0 THEN
            '否'
        WHEN t.is_first_online_supplier = 1 THEN
            '是'
        ELSE
            '否'
    END AS is_first_online_supplier,
    t.customer_level,
    t.owner_id,
    t.owner_name,
    t.owner_city,
    t.owner_org,
    t.source_city_id,
    t.source_city_name,
    t.cost_price,
    t.goods_price,
    t.act_start_time,
    t.act_end_time,
    t.ticket_start_time,
    t.ticket_end_time,
    SUM(t.is_online) AS is_online,
    SUM(t.sale_nums) AS sale_nums,
    SUM(t.sale_money) AS sale_money,
    SUM(t.sale_profile) AS sale_profile,
    SUM(t.verify_num) AS verify_num,
    SUM(t.verify_nums) AS verify_nums,
    SUM(t.verify_money) AS verify_money,
    SUM(t.verify_profile) AS verify_profile,
    SUM(t.refund_num) AS refund_num,
    SUM(t.refund_nums) AS refund_nums,
    SUM(t.refund_money) AS refund_money,
    SUM(t.refund_profile) AS refund_profile
FROM
(
    SELECT
        ga.goods_id,
        ga.goods_sname,
        ga.first_cat_id,
        ga.first_cat_name,
        ga.second_cat_id,
        ga.second_cat_name,
        ga.is_vender,
        ga.supplier_id,
        ga.supplier_name,
        ga.industry_first_cat,
        ga.is_new_supplier,
        ga.is_first_online_supplier,
        ga.customer_level,
        ga.owner_id,
        ga.owner_name,
        ga.owner_city,
        ga.owner_org,
        ga.source_city_id,
        ga.source_city_name,
        ga.cost_price,
        ga.goods_price,
        ga.act_start_time,
        ga.act_end_time,
        ga.ticket_start_time,
        ga.ticket_end_time,
        1 AS is_online,
        gm.sale_nums,
        gm.sale_money,
        gm.sale_profile,
        0 AS verify_num,
        0 AS verify_nums,
        0 AS verify_money,
        0 AS verify_profile,
        0 AS refund_num,
        0 AS refund_nums,
        0 AS refund_money,
        0 AS refund_profile
    FROM
        wowo_rpt.rpt_goods_attr_month ga
    LEFT OUTER JOIN wowo_rpt.rpt_goods_sale_verify_refund_month gm ON gm.goods_id = ga.goods_id
    AND gm.dt = ga.dt
    WHERE
        ga.dt = '{pt}'
    AND ga.act_start_time < '{pt_next}'
    AND ga.act_end_time >= '{pt_this}'

    UNION ALL

    SELECT
        gm.goods_id,
        ga.goods_sname,
        ga.first_cat_id,
        ga.first_cat_name,
        ga.second_cat_id,
        ga.second_cat_name,
        ga.is_vender,
        ga.supplier_id,
        ga.supplier_name,
        ga.industry_first_cat,
        ga.is_new_supplier,
        ga.is_first_online_supplier,
        ga.customer_level,
        ga.owner_id,
        ga.owner_name,
        ga.owner_city,
        ga.owner_org,
        ga.source_city_id,
        ga.source_city_name,
        ga.cost_price,
        ga.goods_price,
        ga.act_start_time,
        ga.act_end_time,
        ga.ticket_start_time,
        ga.ticket_end_time,
        0 AS is_online,
        0 AS sale_nums,
        0 AS sale_money,
        0 AS sale_profile,
        gm.verify_num,
        gm.verify_nums,
        gm.verify_money,
        gm.verify_profile,
        gm.refund_num,
        gm.refund_nums,
        gm.refund_money,
        gm.refund_profile
    FROM
        wowo_rpt.rpt_goods_sale_verify_refund_month gm
    LEFT OUTER JOIN wowo_rpt.rpt_goods_attr_month ga ON gm.goods_id = ga.goods_id
    AND gm.dt = ga.dt
    WHERE
        ga.dt = '{pt}'
) t
GROUP BY
    t.goods_id,
    t.goods_sname,
    t.first_cat_id,
    t.first_cat_name,
    t.second_cat_id,
    t.second_cat_name,
    t.is_vender,
    t.supplier_id,
    t.supplier_name,
    t.industry_first_cat,
    t.is_new_supplier,
    t.is_first_online_supplier,
    t.customer_level,
    t.owner_id,
    t.owner_name,
    t.owner_city,
    t.owner_org,
    t.source_city_id,
    t.source_city_name,
    t.cost_price,
    t.goods_price,
    t.act_start_time,
    t.act_end_time,
    t.ticket_start_time,
    t.ticket_end_time;
