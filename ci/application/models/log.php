<?php
/**
 * 用户信息
 * 
 */
class Log extends MY_Model {

    protected $_tableName = 'log';
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
        $para['time'] =   time();
        $para['admin'] =   $this->session->userdata('aid');
        //print_r($para);
        $insert_id  =   $this->insert( $para,'insert_id');
        return $insert_id;
    }

}