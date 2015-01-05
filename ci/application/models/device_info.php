<?php
/**
 * 用户信息
 * 
 */
class Device_info extends MY_Model {

    protected $_tableName = 'device_info';
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
        unset($para['did']);
        $para['createtime'] =   time();
        $para['modifytime'] =   time();
        $para['createadmin'] =   $this->session->userdata('aid');
        $para['modifyadmin'] =   $this->session->userdata('aid');
        //print_r($para);
        $insert_id  =   $this->insert( $para,'insert_id');

        return $insert_id;
    }
    /**
     * 删除
     */ 
    public function del( $para )
    {
        $this->delete( $para );
        return TRUE;
    }
    /**
     * 增加项目
     */ 
    public function addItem( $para )
    {
        $ctype = $para['ctype'];
        $uid = $para['uid'];
        $oids = explode(',' , $para['oid']);
        $names = explode(',' , $para['name']);
        foreach( $oids as $k => $v)
        {
            if( !empty( $v ) && isset( $names[$k]) && !empty( $names[$k]))
            {           
                $data = array( );
                $data['uid'] = $uid;
                $data['ctype'] = $ctype;
                $data['name'] = $names[$k];
                $data['oid'] = $v;
                $data['status'] = 1;    
                $this->add( $data );            
            }
        }
    }

    public function getDeviceCount($uid,$isyj='')
    {
        $sql = "select count(*) as cnt from ".$this->_tableName." where `uid`='{$uid}'";
        if($isyj !== '')
        {
            $sql    .=  ' and `isyj`='.intval($isyj);
        }
        $ret    =   $this->rexec( $sql );
        $count_ret  =   $ret->row_array();
        return $count_ret['cnt'];
    }

    /**
     * 取得设置
     */ 
    public function getItem( $para )
    {
         $data = array();
         $ctype = $para['ctype'];
         $uid = $para['uid'];
         $w = "`ctype`='{$ctype}' AND `uid`='{$uid}'";
         $res = $this->query( $w );
         //排重
         if( !empty( $res ))
         {
             foreach( $res as $k => $v)
             {
                if( !array_key_exists( $v['oid'] , $data ) )
                {
                    $data[$v['oid']] = $v['name'];
                }
             }
         }
         return $data;
    }

    /**
     * 根据did获取设备信息
     */ 
    public function getDevice( $did )
    {
        $w = "`did`='{$did}'";
        $data = $this->query( $w );
        return $data[0];
    }

    /**
     * 增加足迹
     */ 
    public function addFootprint( $para )
    {
        $ctype = 'footprint';
        $para['ctype'] = $ctype;
        $this->addItem( $para );
        return true;
    }
    
    /**
     * 增加主题
     */ 
    public function addPreferences( $para )
    {
        $ctype = 'preferences';
        $para['ctype'] = $ctype;
        $this->addItem( $para );
        return true;
    }
    /**
     * 增加出发地
     */ 
    public function addFrom( $para )
    {
        $ctype = 'from';
        $para['ctype'] = $ctype;
        $this->addItem( $para );
        return true;
    }

    /**
     * 根据did获取设备信息
     */ 
    public function getDeviceByUid( $uid ,$isyj=1)
    {
        $sql = "select * from ".$this->_tableName." where uid=".$uid." and isyj=".$isyj." order by did desc";
        $ret    =   $this->rexec( $sql );
        $retData    =   $ret->result_array();
        if(count($retData)  >   0)
        {
            return $retData;
        }
        return false;
    }

    public function export( $sql )
    {
        $ret    =   $this->rexec( $sql );
        $data   =   $ret->result_array();
        //print_r($data);
        return $data;
    }
}