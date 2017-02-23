#/bin/sh
#usage : sh selfreport_month.sh 201412

source /home/hadoop/.bash_profile
source base.conf

MYSQL_COMMAND="$__MYSQL_BIN/mysql -h$__DB_DCP_HOST -u$__DB_DCP_USER -p$__DB_DCP_PASSWD -P$__DB_DCP_PORT -D $__DB_DCP_DBNAME --default-character-set=utf8"

######################################商品销售验证退款月报######################################

function dcp_goods_sale_verify_refund_month() {
    pt=$1
    DEAL_DATE=$(date --date "${pt}01" +%Y-%m)
    pt_this=$DEAL_DATE"-01"
    pt_next=$(date --date "$(date --date ${pt}01 +%F) +1 month" +%Y-%m-01)
    cat export_dcp_goods_sale_verify_refund_month.sql > run_export_dcp_goods_sale_verify_refund_month.sql
    sed -i 's/{pt}/'$pt'00/g' run_export_dcp_goods_sale_verify_refund_month.sql
    sed -i 's/{pt_month}/'$DEAL_DATE'/g' run_export_dcp_goods_sale_verify_refund_month.sql
    sed -i 's/{pt_next}/'$pt_next'/g' run_export_dcp_goods_sale_verify_refund_month.sql
    sed -i 's/{pt_this}/'$pt_this'/g' run_export_dcp_goods_sale_verify_refund_month.sql
    hive -f run_export_dcp_goods_sale_verify_refund_month.sql  > dcp_goods_sale_verify_refund_month.txt

    cat import_dcp_goods_sale_verify_refund_month.sql > run_import_dcp_goods_sale_verify_refund_month.sql
    sed -i 's/{pt_month}/'$DEAL_DATE'/g' run_import_dcp_goods_sale_verify_refund_month.sql
    $DB_DCP_COMMAND < run_import_dcp_goods_sale_verify_refund_month.sql
}


function main() {
    DEAL_DATE=$1
    dcp_goods_sale_verify_refund_month $DEAL_DATE
}

if [ $# -eq 0 ];then
    DEAL_DATE=`date --date='1 month ago' +%Y%m`
    main $DEAL_DATE
fi

if [ $# -eq 1 ] ; then
    #DEAL_DATE=$1	#201412
    main $1
fi

if [ $# -eq 2 ] ; then
    START_DATE=$1   #201410
    END_DATE=$2     #201412

    while [ 1 -eq 1 ]
    do
        main $START_DATE
        START_DATE=$(date --date "$(date --date ${START_DATE}01 +%F) +1 month" +%Y%m)
        if [ "$START_DATE" \> "$END_DATE" ] ; then
            break;
        fi
    done
fi

