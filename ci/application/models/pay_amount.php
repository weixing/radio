<?php
/**
 * 用户信息
 * 
 */
class Pay_amount extends MY_Model {

    protected $_tableName = 'pay_amount';
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
     * 根据uid获取用户信息
     */ 
    public function getUser( $uid )
    {
        $w = "`uid`='{$uid}'";
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

    public function showList()
    {
        foreach($this->_global_data['pay_type'] as $key    =>  $value)
        {
            $retData[]  =   $this->getAmountByType($key);
        }
        return $retData;
    }

    /**
     * 根据类型获取金额
     */ 
    public function getAmountByType( $type )
    {
        $sql = "select * from ".$this->_tableName." where `pay_type`='{$type}' order by id desc limit 1";
        $ret    =   $this->rexec( $sql );
        return $ret->row_array();
    }

    /**
     * 根据id获取设置信息
     */ 
    public function getAmount( $id )
    {
        $w = "`id`='{$id}'";
        $data = $this->query( $w );
        return $data[0];
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
}