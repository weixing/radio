<?php
/**
 * 用户信息
 * 
 */
class Photo extends MY_Model {

    protected $_tableName = 'photo';
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
        $pid    =   $insert_id;

        return $pid;
    }

    public function getAllPhoto($uid)
    {
        $sql = "select * from ".$this->_tableName." where uid=".$uid." and status=1 order by pid asc";
        $ret    =   $this->rexec( $sql );
        return $ret->result_array();
    }

    public function getAvatar($uid)
    {
        $w = "`uid`='{$uid}' and status=1 and photo_tag=1";
        $data = $this->query( $w );
        if(count($data) <   1)
        {
            return false;
        }
        return $data[0];
    }

    public function getPhoto( $pid )
    {
        $w = "`pid`='{$pid}'";
        $data = $this->query( $w );
        return $data[0];
    }
}