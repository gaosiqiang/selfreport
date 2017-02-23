SELECT
    t.user_area_name,
    t.user_city_id,
    c1.city_name AS user_city_name,
    t.user_position,
    t.user_name,
    t.user_code,
    t.start_date,
    t.follow_type,
    t.action_type,
    t.supplier_name,
    t.supplier_cat_id,
    c3.name AS supplier_cat_name,
    t.contact_name,
    t.contact_telphone,
    t.sale_area_name,
    t.sale_city_id,
    c2.city_name AS sale_city_name,
    t.sale_position,
    t.sale_name,
    t.sale_code
FROM
    wowo_dw.fact_false_action t
LEFT OUTER JOIN wowo_dw.dim_city c1 ON t.user_city_id = c1.id
LEFT OUTER JOIN wowo_dw.dim_city c2 ON t.sale_city_id = c2.id
LEFT OUTER JOIN wowo_dw.dim_category c3 ON t.supplier_cat_id = c3.id
