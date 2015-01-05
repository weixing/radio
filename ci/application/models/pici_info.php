<?php
/**
 * 用户信息
 * 
 */
class Pici_info extends MY_Model {

    protected $_tableName = 'pici_info';
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
        //$this->insert( $para );
        $insert_id  =   $this->insert( $para,'insert_id');

        return $insert_id;
    }

    public function getPici( $pid )
    {
        $w = "`pid`='{$pid}'";
        $data = $this->query( $w );
        return $data[0];
    }

    public function getAll()
    {
        $sql = "select * from ".$this->_tableName." where status=1 order by pid desc";
        $ret    =   $this->rexec( $sql );
        return $ret->result_array();
    }

    public function getNewHZPici()
    {
        $sql = "select * from ".$this->_tableName." where huanzheng=1 order by pid desc";
        $data    =   $this->rexec( $sql );
        return $data[0];
    }


}