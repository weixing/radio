<?php
/**
 * 用户信息
 * 
 */
class User_info extends MY_Model {

    protected $_tableName = 'user_info';
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
        unset($para['uid']);
        $para['createtime'] =   time();
        $para['modifytime'] =   time();
        $para['createadmin'] =   $this->session->userdata('aid');
        $para['modifyadmin'] =   $this->session->userdata('aid');
        //print_r($para);
        $insert_id  =   $this->insert( $para,'insert_id');
        $uid    =   $insert_id;

        //生成brsa会员号
        $insert_id  =   str_pad($insert_id, 8, "0", STR_PAD_LEFT); 
        $brsa   =   'BRAC'.$insert_id;

        //生成pa码
        $pa =   $this->build_pa( $uid,date('Y',$para['createtime']) );
        
        $para   =   array('membernum'=>$brsa,'pa'=>$pa);

        $this->update( $para ,  array('uid' => $uid ));

        return $uid;
    }

    /**
     * 生成pa码
     */ 
    public function build_pa( $uid,$year )
    {
        $yeartime   =   intval(strtotime($year.'-01-01 00:00:00'));
        $min_uid    =   $this->user_info->getMinUid('createtime>='.$yeartime);
        $pa_tag =   $uid-$min_uid+1+1000;
        $pa =   '1100'.$year.$pa_tag;
        return $pa;
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
     * 根据para获取用户信息
     */ 
    public function getUserByCus( $para )
    {
        $where_arr  =   array();
        foreach($para as $key=>$value)
        {
            $whre_arr[] =   "`".$key."`='{$value}'";
        }
        $w = implode(' and ',$whre_arr);
        $data = $this->query( $w );
        return $data[0];
    }


    /**
     * 根据身份证获取用户信息
     */ 
    public function getUserByCard( $carid )
    {
        $w = "`userIDcard`='{$carid}'";
        $o = "`uid` desc";
        $data = $this->query( $w , $o);
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

    public function getCountByPici($pici)
    {
        $sql = "select count(*) as cnt from ".$this->_tableName." where pci='".$pici."'";
        $ret    =   $this->rexec( $sql );
        $data   =   $ret->result_array();
        return $data[0]['cnt'];
    }

    public function getLastHuhao($limit =   50)
    {
        $sql = "select * from ".$this->_tableName." where huhao!='' order by huhao desc limit ".$limit;
        $ret    =   $this->rexec( $sql );
        $data   =   $ret->result_array();
        return $data;
    }


    public function getMinUid($where='')
    {
        if($where)
        {
            $where  =   " where ".$where;
        }
        else
        {
            $where='';
        }
        $sql = "select min(uid) as uid from ".$this->_tableName.$where;
        $ret    =   $this->rexec( $sql );
        $data   =   $ret->result_array();
        return $data[0]['uid'];
    }

    public function getNewCountByPici($pici)
    {
        $sql = "select count(*) as cnt from ".$this->_tableName." where pci='".$pici."' and change_pici=0";
        $ret    =   $this->rexec( $sql );
        $data   =   $ret->result_array();
        return $data[0]['cnt'];
    }

    public function export( $sql )
    {
        $ret    =   $this->rexec( $sql );
        $data   =   $ret->result_array();
        //print_r($data);
        return $data;
    }

    public function add_test($uid)
    {
        $sql = "update ".$this->_tableName." set test_times=test_times+1 where uid=".$uid;
        $ret    =   $this->rexec( $sql );
        return true;
    }

    public function add_testb($uid)
    {
        $sql = "update ".$this->_tableName." set testb_times=testb_times+1 where uid=".$uid;
        $ret    =   $this->rexec( $sql );
        return true;
    }

    public function add_testc($uid)
    {
        $sql = "update ".$this->_tableName." set testc_times=testc_times+1 where uid=".$uid;
        $ret    =   $this->rexec( $sql );
        return true;
    }

    public function add_testcharge($uid)
    {
        $sql = "update ".$this->_tableName." set test_charge=test_charge+1 where uid=".$uid;
        $ret    =   $this->rexec( $sql );
        return true;
    }

    public function add_testBcharge($uid)
    {
        $sql = "update ".$this->_tableName." set testb_charge=testb_charge+1 where uid=".$uid;
        $ret    =   $this->rexec( $sql );
        return true;
    }

    public function add_testCcharge($uid)
    {
        $sql = "update ".$this->_tableName." set testc_charge=testc_charge+1 where uid=".$uid;
        $ret    =   $this->rexec( $sql );
        return true;
    }
}