<?php
/**
 * 用户信息
 * 
 */
class Remark extends MY_Model {

    protected $_tableName = 'remark';
    //类型一 主题 topic  
    //类型二  目的地 aim
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 增加数据
     */ 
    public function add( $para )
    {
        /*$info = $this->getOneByKey( $para );
        if( !empty( $info ))
        {
            return FALSE;
        }
        $time = time( );
        $para['ctime'] = $time;*/
        $para['createtime'] =   time();
        $para['modifytime'] =   time();
        $para['createadmin'] =   $this->session->userdata('aid');
        $para['modifyadmin'] =   $this->session->userdata('aid');
        //print_r($para);
        $this->insert( $para );
        return TRUE;
    }

    public function getRemark( $id )
    {
        $w = "`id`='{$id}'";
        $data = $this->query( $w );
        return $data[0];
    }

    /**
     * 根据uid获取信息
     */ 
    public function getRemarkByUid( $uid )
    {
        $sql = "select * from ".$this->_tableName." where uid=".$uid." order by id desc";
        $ret    =   $this->rexec( $sql );
        $retData    =   $ret->result_array();
        if(count($retData)  >   0)
        {
            return $retData;
        }
        return false;
    }

    public function getUnOpRemarkByUid( $uid )
    {
        $sql = "select * from ".$this->_tableName." where uid=".$uid." and status=0 order by id desc";
        $ret    =   $this->rexec( $sql );
        $retData    =   $ret->result_array();
        if(count($retData)  >   0)
        {
            return $retData;
        }
        return false;
    }
}