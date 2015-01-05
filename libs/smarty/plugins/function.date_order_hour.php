<?php
/**
 * 
 * 实现1小时内倒计时日期
 * 
 * {=date_order_hour ctime='2013-03-18 11:26:00' type=1=}   
 */ 
function smarty_function_date_order_hour( $params ) 
{
    $ctime = isset( $params['ctime'] ) ?  $params['ctime'] : 0 ;
    $type = isset( $params['type'] ) ?  $params['type'] : 1 ;
    $format = isset( $params['format'] ) ?  $params['format'] : 'Y-m-d H:i:s' ;
    $interval = isset( $params['interval '] ) ?  $params['interval '] : 60 ;
    $interval = 60*60;
    
    if( empty( $ctime ))
    {
        return '';
    }
   
    //默认倒计时的时间格式为  2012-12-12 12:23:34
    $time = time();
    if( $type == 1 )
    {
        $ctime = strtotime( $ctime );
    }
    $ctime = intval( $ctime );
    $subtime = $time - $ctime ;
    //间隔时间以外
    if( $subtime > $interval )
    {
        $ctime = date ( $format , $ctime);
        return $ctime;
    }
    if( $time < $ctime )
    {
        return 0 .' 秒前' ;
    }
    //间隔时间60秒以内
    if( $subtime < 60 )
    {
        return $subtime .' 秒前' ;
    }
    $pri = $subtime / 60;
    $pri = ceil( $pri );
    return  $pri .'分钟前';
}