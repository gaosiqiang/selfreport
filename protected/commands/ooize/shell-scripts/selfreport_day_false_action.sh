#/bin/sh
#usage : sh selfreport_day.sh 20141201

source /home/hadoop/.bash_profile
source base.conf

MYSQL_COMMAND="$__MYSQL_BIN/mysql -h$__DB_DCP_HOST -u$__DB_DCP_USER -p$__DB_DCP_PASSWD -P$__DB_DCP_PORT -D $__DB_DCP_DBNAME --default-character-set=utf8"

######################################疑似虚假行动量数据######################################

function dcp_false_action() {

    cat export_dcp_false_action.sql > run_export_dcp_false_action.sql
    hive -f run_export_dcp_false_action.sql  > dcp_false_action.txt

    cat import_dcp_false_action.sql > run_import_dcp_false_action.sql
    sed -i 's|{__DATA_PATH}|'$__DATA_PATH_NEW'|g' run_import_dcp_false_action.sql
    $DB_DCP_COMMAND < run_import_dcp_false_action.sql

}



function main() {
    DEAL_DATE=$1
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

