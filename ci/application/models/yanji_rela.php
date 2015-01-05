<?php
/**
 * 用户信息
 * 
 */
class Yanji_rela extends MY_Model {

    protected $_tableName = 'yanji_rela';
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

    public function getPici( $pid )
    {
        $w = "`pid`='{$pid}'";
        $data = $this->query( $w );
        return $data[0];
    }

    public function getItem( $uid , $pid)
    {
         $w = "`uid`='{$uid}' AND `yanjipid`='{$pid}'";
         $res = $this->query( $w );
         //print_r($res);
         return $res;
    }

    public function getAllByPid($pid)
    {
        $sql = "select * from ".$this->_tableName." where yanjipid=$pid order by uid desc";
        $ret    =   $this->rexec( $sql );
        return $ret->result_array();
    }

    public function getAllByUid($uid)
    {
        $sql = "select * from ".$this->_tableName." where uid=$uid order by yanjipid desc";
        $ret    =   $this->rexec( $sql );
        return $ret->result_array();
    }


    public function change_pici($uid,$yanjipid  =   0)
    {
        $yanjipid   =   intval($yanjipid);
        $this->load->model('yanjipici_info' , 'yanjipici_info');
        if($yanjipid    ==   0)
        {
            $yanjipici_list =   $this->yanjipici_info->getAll();
            $yanjipid   =   $yanjipici_list[0]['pid'];
        }
        $check_data =   $this->getItem($uid,$yanjipid);
        //exit();
        if(!$check_data)
        {
            $para   =   array('yanjipid'=>$yanjipid,'uid'=>$uid);
            $this->add($para);
        }
    }
    public function getCountByPid($pid)
    {
        $sql = "select count(*) as cnt from ".$this->_tableName." where yanjipid='".$pid."'";
        $ret    =   $this->rexec( $sql );
        $data   =   $ret->result_array();
        return $data[0]['cnt'];
    }
}