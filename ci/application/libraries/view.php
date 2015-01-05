<?php 
require './libs/smarty/Smarty.class.php';
class View Extends Smarty{
    
	public function __construct() {
        parent::__construct();
        
	    $rootPath =  ROOTPATH;
        $server = $_SERVER['SERVER_NAME'];
      
        $pos = strpos( $server , 'admin' );
       

        $this->compile_dir  =   $rootPath.'/template_c/';
        $this->template_dir =   $rootPath.'/templates/';

				
        //创建smarty 编译目录	
        $this->recursiveMkdir( $this->compile_dir );
        
		$this->left_delimiter  = '{{';
		$this->right_delimiter = '}}';
		$this->compile_locking = false;
	}
    /**
     * 建目录
     */ 
    private  function recursiveMkdir($pathname , $mode=0700) {
  		is_dir(dirname($pathname)) || $this->recursiveMkdir(dirname($pathname), $mode);
   	    return is_dir($pathname) || mkdir($pathname, $mode);
	}
}