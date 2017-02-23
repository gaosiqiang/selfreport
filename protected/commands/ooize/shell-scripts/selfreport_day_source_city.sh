#/bin/sh
#usage : sh selfreport_day.sh 20141201

source /home/hadoop/.bash_profile
source base.conf

MYSQL_COMMAND="$__MYSQL_BIN/mysql -h$__DB_DCP_HOST -u$__DB_DCP_USER -p$__DB_DCP_PASSWD -P$__DB_DCP_PORT -D $__DB_DCP_DBNAME --default-character-set=utf8"

######################################餐饮事业部在线商家######################################

function dcp_source_city_data() {
    pt=$1
    DEAL_DATE=`date -d $pt +"%Y-%m-%d"`
    cat export_dcp_source_city_data.sql > run_export_dcp_source_city_data.sql
    sed -i 's/{pt}/'$pt'/g' run_export_dcp_source_city_data.sql
    sed -i 's/{pt_day}/'$DEAL_DATE'/g' run_export_dcp_source_city_data.sql
    hive -f run_export_dcp_source_city_data.sql  > dcp_source_city_data.txt

    cat import_dcp_source_city_data.sql > run_import_dcp_source_city_data.sql
    sed -i 's/{pt_day}/'$DEAL_DATE'/g' run_import_dcp_source_city_data.sql
    $DB_DCP_COMMAND < run_import_dcp_source_city_data.sql

}



function main() {
    DEAL_DATE=$1
    dcp_source_city_data $DEAL_DATE
}

if [ $# -eq 0 ];then
    DEAL_DATE=`date --date='1 day ago' +%Y%m%d`
    main $DEAL_DATE
fi

if [ $# -eq 1 ] ; then
    main $1	#20141201
fi

if [ $# -eq 2 ] ; then
    START_DATE=$1   #20141201
    END_DATE=$2     #20141231

    while [ 1 -eq 1 ]
    do
        main $START_DATE
        START_DATE=$(date --date "$(date --date $START_DATE +%F) +1 day" +%Y%m%d)
        if [ "$START_DATE" \> "$END_DATE" ] ; then
            break;
        fi
    done
fi

