<?php
/**
 * 用户信息
 * 
 */
class Level_info extends MY_Model {

    protected $_tableName = 'level_info';
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
        $insert_id  =   $this->insert( $para,'insert_id');

        return $insert_id;
    }

    public function getAll()
    {
        $sql = "select * from ".$this->_tableName." where status=1";
        $ret    =   $this->rexec( $sql );
        return $ret->result_array();;
    }

}