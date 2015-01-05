<?php
/**
 * 
 * 实现1小时内倒计时日期
 * 
 * {=sub_str ctime='2013-03-18 11:26:00' type=1=}   
 */ 
function smarty_function_sub_str( $params ) 
{
    $content = isset( $params['content'] ) ?  $params['content'] : '' ;
    $is_strip_tags = isset( $params['is_strip_tags'] ) ?  $params['is_strip_tags'] : 1 ;
    $words = isset( $params['words'] ) ?  $params['words'] : 0 ;
    $dot = isset( $params['dot'] ) ?  $params['dot'] : '';
    $intval = isset( $params['intval'] ) ?  $params['intval'] : 0 ;
    if( empty( $content ))
    {
        return $content;
    }
    if( $is_strip_tags )
    {
        $content = strip_tags( $content );
    }
    if( $words )
    {
        $content = mb_substr( $content ,0 , $words  , 'utf-8');
		$len = mb_strlen( $content , 'utf-8');
		if( $len >= $words)
		{
		      $content .= $dot;
		}
    }
    if( $intval )
    {
        $content = intval( $content);
    }
    return $content;
}