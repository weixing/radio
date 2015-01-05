<?php 
class MY_Model extends CI_Model {
    
    // 表名
    protected $_tableName = '';
    
    public  function __construct()
    {
        parent::__construct();
        $dbr = $this->load->database('dbr' , TRUE);
        $dbr->reconnect();
        $dbr->query("SET NAMES UTF8");
        $this->load->library('mem');
    }
    /**
     * 得到key
     */ 
    public function getMemKey( $para )
    {
        return $this->mem->getKey( $para , $this->_tableName);   
    }
    /**
     * 删除mem 数据
     */ 
    public function delMemKey( $para )
    {
        $key = $this->getMemKey( $para );
        $this->mem->delete( $key );
    }
    
    public function _getSqlKey( $para )
    {
        $w = '';
        $i = 0;
        foreach( $para as $k => $v)
        {
            $w .= $i!=0 ? ' AND ' : '';
            $i++;
            $v = mysql_escape_string( $v );
            $w .= "`{$k}`='{$v}'"; 
        }
        return $w;
    }
    /**
     * 根据主键取一条数据
     */ 
    public function getOneByKey( $para , $isUseMem = TRUE , $memMaxTime = MEM_MAX_TIME)
    {
        $dbr = $this->load->database('dbr' , TRUE);
        $dbr->reconnect();
        
        $clearmem = $this->input->get('clearmem', TRUE);
        if( $clearmem == 'yes')
        {
            $isUseMem = FALSE;
        }
        $rt = '';
        $key = $this->getMemKey( $para );
        //是否使用缓存
        if( $isUseMem )
        {
            $rt = $this->mem->get( $key );
        } 
        if( !empty( $rt ))
        {
            //关闭数据库连接
            $dbr->close();
            return $rt;
        }
        $w = $this->_getSqlKey( $para );        
        $sql = "SELECT * FROM  `{$this->_tableName}`  WHERE {$w} LIMIT 1;";

        $query = $dbr->query( $sql );
        if ( $query->num_rows() > 0)
        {
            $row = $query->row_array(); 
            //重新设置缓存
            if( $clearmem == 'yes' || $isUseMem)
            {
                $this->mem->set( $key , $row , $memMaxTime);
            }
            //关闭数据库连接
            $dbr->close();
            return $row;
        }
        //关闭数据库连接
        $dbr->close();
        return FALSE;
    }  
    
    /**
     * 根据主键取一条数据
     */ 
    public function getCountByKey( $para , $isUseMem = TRUE , $memMaxTime = MEM_MAX_TIME)
    {
        $dbr = $this->load->database('dbr' , TRUE);
        $dbr->reconnect();        
        $clearmem = $this->input->get('clearmem', TRUE);
        if( $clearmem == 'yes')
        {
            $isUseMem = FALSE;
        }
        $rt = '';
        $mpara = $para;
        $mpara['appendkey'] = 'getOneByKey';
        $key = $this->getMemKey( $para );
        //是否使用缓存
        if( $isUseMem )
        {
            $rt = $this->mem->get( $key );
        } 
        if( !empty( $rt ))
        {
            //关闭数据库连接
            $dbr->close();
            return $rt;
        }
        $where = $this->_getSqlKey( $para ); 
        $countSql = "SELECT COUNT(*) as cnt FROM  `{$this->_tableName}` WHERE {$where}  LIMIT 1;";    
         
        $query = $dbr->query( $countSql );
        if ( !($query->num_rows() > 0))
        {
            // 如果没有数据直接返回
            //关闭数据库连接
            $dbr->close();
            return 0;            
        }         
        $row = $query->row_array(); 
        $cnt = $row['cnt'];
        //重新设置缓存
        if( $clearmem == 'yes' || $isUseMem)
        {
            $this->mem->set( $key , $cnt , $memMaxTime);
        }
        //关闭数据库连接
        $dbr->close();
        return $cnt;
    } 
    
    /**
     * 执行 sql 语句
     */ 
    public function exec( $sql )
    {
        $dbw = $this->load->database('dbw' , TRUE);   
        $dbw->reconnect();
        $isSucc = $dbw->query( $sql );
        //关闭数据库连接
        $dbw->close();
        return $isSucc;
    }
    
     /**
     * 执行 sql 语句
     */ 
    public function rexec( $sql )
    {
        $dbr = $this->load->database('dbr' , TRUE);   
        $dbr->reconnect();
        $isSucc = $dbr->query( $sql );
        //关闭数据库连接
        $dbr->close();
        return $isSucc;
    }
    /**
     * 删除数据
     */ 
    public function delete( $para )
    {
        $dbw = $this->load->database('dbw' , TRUE);
        $dbw->reconnect();
        $dbw->delete( $this->_tableName , $para );
        //$this->delMemKey( $para );
        $row = $dbw->affected_rows();
        //关闭数据库连接
        $dbw->close();        
        return $row;
    }
    /**
     * 根据主键更新一条数据
     */
     public function update( $data ,  $para )
     {
        $dbw = $this->load->database('dbw' , TRUE);
        $dbw->reconnect();
        $data['modifytime'] =   time();
        $data['modifyadmin'] =   $this->session->userdata('aid');
        $dbw->update( $this->_tableName , $data , $para );
        //$this->delMemKey( $para );
        $row = $dbw->affected_rows();
        //关闭数据库连接
        $dbw->close();   
        return $row;
     }

    /**
     * 插入数据
     * 当 $type = insert_id 时 返回数据的自增ID
     */ 
    public function insert( $data , $type = '' )
    {
        $dbw = $this->load->database('dbw' , TRUE);
        $dbw->reconnect();
        $res = $dbw->insert( $this->_tableName , $data);
        if( empty( $type ))
        {
            //关闭数据库连接
            $dbw->close();  
            return $res;
        }
        if( $type == 'insert_id')
        {
            $insert_id = $dbw->insert_id();
            //关闭数据库连接
            $dbw->close(); 
            return $insert_id;
        }
        
        if( $type == 'affected_rows')
        {
            $affected_rows = $dbw->affected_rows();
            //关闭数据库连接
            $dbw->close();  
            return $affected_rows;
        }
    }
    /**
     * 查询
     */ 
    public function query( $w ='', $o = '' , $isUseMem = false , $memMaxTime = MEM_MAX_TIME)
    {
        $dbr = $this->load->database('dbr' , TRUE);
        $dbr->reconnect();
        $clearmem = $this->input->get('clearmem', TRUE);
        if( $clearmem == 'yes')
        {
            $isUseMem = FALSE;
        }
        $key = $this->getMemKey( "query{$w}" );
        //是否使用缓存
        if( $isUseMem )
        {
            $rt = $this->mem->get( $key );
        } 
        if( !empty( $rt ))
        {
           //关闭数据库连接
            $dbr->close();
            return $rt;
        }
        $where = '';
        if( !empty( $w ))
        {
            $where = "WHERE {$w}";
        }

        $order = '';
        if( !empty( $o ))
        {
            $order = "order by {$o}";
        }

        $sql = "SELECT * FROM  `$this->_tableName`  {$where} {$order};";
        $query = $dbr->query( $sql );
        if ( $query->num_rows() > 0)
        {
            $data = $query->result_array();
            //重新设置缓存
            if( $clearmem == 'yes' || $isUseMem)
            {
                $this->mem->set( $key , $data , $memMaxTime);
            }
            //关闭数据库连接
            $dbr->close();
            return $data;
        }
        //关闭数据库连接
        $dbr->close();
        return FALSE;
    }    
    /**
     * 去掉重复数据
     */ 
    public function distinctquery( $w = '' , $field='*' ,$isUseMem = false , $memMaxTime = MEM_MAX_TIME)
    {
        $dbr = $this->load->database('dbr' , TRUE);
        $dbr->reconnect();
        $clearmem = $this->input->get('clearmem', TRUE);
        if( $clearmem == 'yes')
        {
            $isUseMem = FALSE;
        }
        $key = $this->getMemKey( "distinctqueryquery{$w}" );
        //是否使用缓存
        if( $isUseMem )
        {
            $rt = $this->mem->get( $key );
        } 
        if( !empty( $rt ))
        {
           //关闭数据库连接
            $dbr->close();
            return $rt;
        }
        $where = '';
        if( !empty( $w ))
        {
            $where = "WHERE {$w}";
        }
        $sql = "SELECT DISTINCT {$field} FROM  `$this->_tableName`  {$where};";
        //echo $sql;
        $query = $dbr->query( $sql );
        if ( $query->num_rows() > 0)
        {
            $data = $query->result_array();
            //重新设置缓存
            if( $clearmem == 'yes' || $isUseMem)
            {
                $this->mem->set( $key , $data , $memMaxTime);
            }
            //关闭数据库连接
            $dbr->close();
            return $data;
        }
        //关闭数据库连接
        $dbr->close();
        return FALSE;
    } 
    
    /**
     * 查询
     */ 
    public function queryList( $para , $isUseMem = false , $memMaxTime = MEM_MAX_TIME)
    {
        $dbr = $this->load->database('dbr' , TRUE);
        $dbr->reconnect();   
        $clearmem = $this->input->get('clearmem', TRUE);
        if( $clearmem == 'yes')
        {
            $isUseMem = FALSE;
        }
        $resData = array();
        $resData['cnt'] = 0;
        $resData['page'] = 0 ;
        $resData['perPage'] = 0;
        $resData['maxPage'] = 0;
        $resData['list'] = array();
        //当条件时
        $key = $this->getMemKey( $para );
        //是否使用缓存
        if( $isUseMem )
        {
            $rt = $this->mem->get( $key );
        }         
        if( !empty( $rt ))
        {
            //关闭数据库连接
            $dbr->close();
            return $rt;
        }
        $where = '';
        $orderBy = '';
        if( isset( $para['where'] ) && !empty( $para['where'] ) )
        {
            $where = " WHERE {$para['where']} ";
        }        
        $countSql = "SELECT COUNT(*) as cnt FROM  `{$this->_tableName}` {$where}  LIMIT 1;";        
        $query = $dbr->query( $countSql );
        if ( !($query->num_rows() > 0))
        {
            // 如果没有数据直接返回
            //关闭数据库连接
            $dbr->close();
            return $resData;            
        }         
        $row = $query->row_array(); 
        $resData['cnt'] = $row['cnt'];        
        //排序
        if( isset( $para['order'] ))
        {
            $orderBy = " ORDER BY  {$para['order']} ";
        }
        //分页限制
        $limit = '';
        if(isset( $para['page'] ) && !empty( $para['page']) && isset( $para['perPage'] ) )
        {
            $page = intval( $para['page'] );
            $perPage = intval( $para['perPage'] );            
            $limit = ' LIMIT ' . ( $page - 1 ) * $perPage .','.$perPage;
            $resData['page'] = $page ;
            $resData['perPage'] = $para['perPage'];
            $resData['maxPage'] = ceil( $resData['cnt'] / $perPage);
        }
      
        $sql = "SELECT * FROM  `{$this->_tableName}`  {$where}  {$orderBy} {$limit}";
        //echo $sql;
        $query = $dbr->query( $sql );
        if ( !($query->num_rows() > 0) )
        {
            // 如果没有数据直接返回
            //关闭数据库连接
            $dbr->close();
            return $resData;            
        } 
        $data = $query->result_array();
        $resData['list'] = $data;
        //重新设置缓存
        if( $clearmem == 'yes' || $isUseMem)
        {
            $this->mem->set( $key , $resData , $memMaxTime);
        }
        //关闭数据库连接
        $dbr->close();
        return $resData;
    }
    /**
     * 得到 count 
     */ 
    public function getCount( $where = '' )
    {
        $dbr = $this->load->database('dbr' , TRUE);
        $dbr->reconnect();  
        $countSql = "SELECT COUNT(*) as cnt FROM  `{$this->_tableName}` {$where}  LIMIT 1;";        
        $query = $dbr->query( $countSql );
        if ( !($query->num_rows() > 0))
        {
            $dbr->close();
            return 0;            
        } 
        $row = $query->row_array(); 
        return $row['cnt']; 
    }

} 