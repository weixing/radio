<?php
/**
 * 
 * 实现1小时内倒计时日期
 * 
 * {=change_img img='2013-03-18 11:26:00'  width=''=}   
 * pic_101.*
   均可改为
   pic_150.*
   pic_235.*
   pic_320.*
   pic_640.*
 */ 
function smarty_function_change_img( $params ) 
{
    $img= isset( $params['img'] ) ?  $params['img'] : '' ;
	$width = isset( $params['width'] ) ?  $params['width'] : '' ;
	if( empty( $img ))
	{
	     return $img;
	}
    if( empty( $width ) || !in_array( $width , array('150' ,'235','320','640')))
    {
        return $img;
    }
    $findme = 'pic_101.';
    $pos = strpos($img, $findme);

    if ($pos === false) 
    {
        return $img;
    }
    $replaceStr = "pic_{$width}.";
    $img = str_replace( $findme , $replaceStr ,$img);
    return $img;
}