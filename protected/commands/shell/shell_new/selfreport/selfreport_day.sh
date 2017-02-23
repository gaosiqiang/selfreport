#/bin/sh
#自助报表日报
#usage : sh selfreport_day.sh 20141201
usage () {
    echo "usage: $0 START_DATE(DEAL_DATE) END_DATE" 1>&2
    exit 2
}


if [ $# -gt 2 ] ; then
    usage
fi


__HOME_PATH=/home/hadoop/dc_get_data
__CONF_PATH=$__HOME_PATH/conf
source $__CONF_PATH/base.conf

DB_DCP_COMMAND="$__MYSQL_BIN/mysql -h$__DB_DCP_HOST -u$__DB_DCP_USER -p$__DB_DCP_PASSWD -P$__DB_DCP_PORT -D $__DB_DCP_DBNAME --default-character-set=utf8"


######################################dcp_source_city_data######################################

function dcp_source_city_data() {
    pt=$1
    DEAL_DATE=`date -d $pt +"%Y-%m-%d"`
    cat $__SQL_PATH_NEW/selfreport/export_dcp_source_city_data.sql > $__TEMP_SQL_PATH_NEW/run_export_dcp_source_city_data.sql
    sed -i 's/{pt}/'$pt'/g' $__TEMP_SQL_PATH_NEW/run_export_dcp_source_city_data.sql
    sed -i 's/{pt_day}/'$DEAL_DATE'/g' $__TEMP_SQL_PATH_NEW/run_export_dcp_source_city_data.sql
    hive -f $__TEMP_SQL_PATH_NEW/run_export_dcp_source_city_data.sql  > $__DATA_PATH_NEW/dcp_source_city_data.txt

    cat $__SQL_PATH_NEW/selfreport/import_dcp_source_city_data.sql > $__TEMP_SQL_PATH_NEW/run_import_dcp_source_city_data.sql
    sed -i 's|{__DATA_PATH}|'$__DATA_PATH_NEW'|g' $__TEMP_SQL_PATH_NEW/run_import_dcp_source_city_data.sql
    sed -i 's/{pt_day}/'$DEAL_DATE'/g' $__TEMP_SQL_PATH_NEW/run_import_dcp_source_city_data.sql
    $DB_DCP_COMMAND < $__TEMP_SQL_PATH_NEW/run_import_dcp_source_city_data.sql

    rm -rf $__TEMP_SQL_PATH_NEW/run_export_dcp_source_city_data.sql
    rm -rf $__TEMP_SQL_PATH_NEW/run_import_dcp_source_city_data.sql
}


######################################dcp_false_action######################################

function dcp_false_action() {

    cat $__SQL_PATH_NEW/selfreport/export_dcp_false_action.sql > $__TEMP_SQL_PATH_NEW/run_export_dcp_false_action.sql
    hive -f $__TEMP_SQL_PATH_NEW/run_export_dcp_false_action.sql  > $__DATA_PATH_NEW/dcp_false_action.txt

    cat $__SQL_PATH_NEW/selfreport/import_dcp_false_action.sql > $__TEMP_SQL_PATH_NEW/run_import_dcp_false_action.sql
    sed -i 's|{__DATA_PATH}|'$__DATA_PATH_NEW'|g' $__TEMP_SQL_PATH_NEW/run_import_dcp_false_action.sql
    $DB_DCP_COMMAND < $__TEMP_SQL_PATH_NEW/run_import_dcp_false_action.sql

    rm -rf $__TEMP_SQL_PATH_NEW/run_export_dcp_false_action.sql
    rm -rf $__TEMP_SQL_PATH_NEW/run_import_dcp_false_action.sql
}





function main() {
    DEAL_DATE=$1
    dcp_source_city_data $DEAL_DATE
    dcp_false_action
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

