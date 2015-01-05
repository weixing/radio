<?php
/**
 * 用户信息
 * 
 */
class admin extends MY_Model {

    protected $_tableName = 'admin';
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
        $para['createtime'] =   time();
        $para['modifytime'] =   time();
        $para['createadmin'] =   $this->session->userdata('aid');
        $para['modifyadmin'] =   $this->session->userdata('aid');
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
     * 获取全部用户信息
     */ 
    public function getAll()
    {
        $data = $this->query();
        return $data;
    }

    /**
     * 根据aid获取用户信息
     */ 
    public function getAdmin( $aid )
    {
        $w = "`aid`='{$aid}'";
        $data = $this->query( $w );
        return $data[0];
    }
}