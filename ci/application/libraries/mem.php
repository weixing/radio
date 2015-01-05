<?php
/**
 * @Copyright (c) 2011, 新浪网-
 * All rights reserved.
 * MC基类
 * @time            2011/3/2 11:48
*/

class Mem{

    static private $memcache = array(); //MC连接池
    
    private $mc; //当前MC链接
    
    private $_keyPri = '';
    /*
     * @params string $mcName MC名称
     * @params array $mcConfig eg:array('servers'=>'192.168.1.1:7600 192.168.1.2:7700');
     */
    public function __construct($servers = '') 
    {
        /*$this->_keyPri = $_SERVER['SINASRV_MEMCACHED_KEY_PREFIX'];
        
        $servers = empty($servers) ? $_SERVER['SINASRV_MEMCACHED_SERVERS'] : $servers;
    
        $mcKey = md5($servers);
        if (isset( self::$memcache[$mcKey]) && (self::$memcache[$mcKey] instanceof Memcache)) {
            $this->mc = self::$memcache[$mcKey];
        } else {
            self::$memcache[$mcKey] = new Memcache();
            $serverArr = explode (" ", $servers);
            foreach ($serverArr as $v) {
                list($server, $port) = explode(":", $v);
                self::$memcache[$mcKey]->addServer($server, $port);
            }
            $this->mc = self::$memcache[$mcKey];
        }*/
    }
    
    public function set($key, $value, $time = 0) {
        $key = $this->_keyPri . $key;
        return $this->mc->set($key, $value, 0, $time);
    }

    public function get($key) {
        $key = $this->_keyPri . $key;
        $data = $this->mc->get($key);
        return $data;
    }

    public function delete($key) {
        $key = $this->_keyPri . $key;
        return $this->mc->delete($key);
    }
    /**
     * 取得mem  key 
     */ 
    public function getKey( $para , $pri = '')
    {
        $key = '';
        if( is_array( $para ))
        {
            ksort( $para );
            foreach( $para as $k => $v )
            {
                $key .= "{$k}_{$v}";
            }
        }else
        {
            $key = $para;
        }
        $key = md5( $pri . $key );
        return $key;
    }
}