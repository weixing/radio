<?php
/**
 * 用户信息
 * 
 */
class General_data extends MY_Model {

    protected $_tableName = 'general_data';
    //类型一 主题 topic  
    //类型二  目的地 aim
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 增加数据
     */ 

    public function getData( $id )
    {
        $w = "`id`='{$id}'";
        $data = $this->query( $w );
        return $data[0];
    }

    public function getYear()
    {
         $res = $this->getData( 1 );    //id=1的数据是会费年限
         //print_r($res);
         return $res;
    }

    public function addYear()
    {
        $ret    =   $this->getYear();
        $year   =   $ret['content']+1;
        $this->update(array('content'=>$year),array('id'=>1));
        return $year;
    }

    public function deductYear()
    {
        $ret    =   $this->getYear();
        $year   =   $ret['content']-1;
        $this->update(array('content'=>$year),array('id'=>1));
        return $year;
    }

}