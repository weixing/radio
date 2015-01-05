<?php
/**
 * 用户信息
 * 
 */
class Pay_info extends MY_Model {

    protected $_tableName = 'pay_info';
    //类型一 主题 topic  
    //类型二  目的地 aim
    public function __construct()
    {
        parent::__construct();
        $this->load->model('general_data' , 'general_data');
    }
    /**
     * 增加数据
     */ 
    public function add( $para )
    {
        if($para['pay_type']    ==  0)
        {
            $year_data  =   $this->general_data->getYear();
            if($year_data['content']    <   $para['year'])
            {
                MY_Controller::showMsg('会费缴纳年限错误，当前只能缴纳'.$year_data['content'].'年会费');
            }
        }
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
        //$this->insert( $para );
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

    public function getAmountByType( $type,$uid )
    {
        $sql = "select * from ".$this->_tableName." where `uid`='{$uid}' and `pay_type`='{$type}' order by id desc limit 1";
        $ret    =   $this->rexec( $sql );
        return $ret->row_array();
    }

    public function getDeviceCount($uid)
    {
        $sql = "select SUM(count) as cnt from ".$this->_tableName." where `uid`='{$uid}' and `pay_type`=3";
        $ret    =   $this->rexec( $sql );
        $count_ret  =   $ret->row_array();
        return $count_ret['cnt'];
    }

    public function getPay( $id )
    {
        $w = "`id`='{$id}'";
        $data = $this->query( $w );
        return $data[0];
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

    public function getPayList($uid)
    {
        $sql = "select * from ".$this->_tableName." where uid=".$uid;
        $ret    =   $this->rexec( $sql );
        return $ret->result_array();
    }

    public function payDelete($id)
    {
        $sql = "update ".$this->_tableName." set `uid`=0-`uid` where id=".$id;
        $ret    =   $this->rexec( $sql );
        return;
    }

    public function importAll($sql,$fp,$pay_type_arr,$admin_list_arr)
    {
        $dbr = $this->load->database('dbr' , TRUE);
        $dbr->reconnect(); 
        $query  =   $dbr->query($sql);
        //echo $sql;
        //return $query->result_array();
        foreach($query->result_array() as $row)
        {
            $str_tmp    =   '';
            foreach($row as $key=>$value)
            {
                if($key ==  'pay_type')
                {
                    $value  =   iconv("UTF-8",'GBK//IGNORE',$pay_type_arr[$value]);
                }
                if($key ==  'username' ||  $key ==  'txAd')
                {
                    $value  =   iconv("UTF-8",'GBK//IGNORE',$value);
                }

                if($key ==  'time')
                {
                    $value  =   date('Y-m-d H:i:s',$value);
                }

                if($key ==  'admin')
                {
                    $value  =   $admin_list_arr[$value];
                }


                $str_tmp    .=  "\t".$value.",";
            }
            fputs($fp,$str_tmp."\n");
        }
    }
}