<?php 
class MY_Controller extends CI_Controller 
{
    //是否登录
    //protected $isLogined = FALSE;
    //protected $cuid = '';
    //protected $ssoUserInfo = '';
    //当前访问用户信息
    protected $cSESSION = array(); 
    //protected $visitUserInfo = array(); 
    //protected $visitUid = ''; 
    //protected $isself = 0;
    /**
     * @param  $isInit  是否需要初始化一些信息
     */ 
    public  function __construct()
    {
        parent::__construct();
        $CI =& get_instance();
        $this->_global_data = $CI->config->item('global');
        $this->_global_permission = $CI->config->item('permission');
        $this->_photo_folder =   $CI->config->item('photo_path');
        $this->_photo_url =   $CI->config->item('photo_url');
        $this->_photo_zip =   $CI->config->item('photo_zip');
        $this->_global_pay_type = $CI->config->item('pay_type');
        $this->_global_pay_type_print = $CI->config->item('pay_type_print');
        $this->_global_lv_to_int = $CI->config->item('lv_to_int');
        $this->_global_test_times = $CI->config->item('test_times');
        $this->_user_export = $CI->config->item('user_export');
        $this->_user_info_desc = $CI->config->item('user_info_desc');
        $this->_user_export_wgj = $CI->config->item('user_export_wgj');
        $this->_area = $CI->config->item('area');
        $this->_province = $CI->config->item('province');
        $this->_user_type = $CI->config->item('user_type');

        $this->load->model('log' , 'log');

        $this->load->library('view'); 
        $this->load->library('session');

        //$this->cSESSION =   $this->session->all_userdata();
        if((!($this->session->userdata('aid') >   0) || (time()-$this->session->userdata('logintime'))   >=  (6*3600)) &&  $this->getClass()   !=  'login' &&  $this->getClass()   !=  'api')
        {
            header('location:/');
            exit();
        }

        $this->load->model('admin' , 'admin');
        if($this->session->userdata('aid') >   0)
        {
            $cAdmin =   $this->admin->getAdmin( $this->session->userdata('aid'));
            if($cAdmin['status']    !=  1)
            {
                $this->showMsg('账号已冻结');
                exit();
            }
        }

        $this->view->assign('pro_name' , PRO_NAME);

        $this->cSESSION =   $this->session->all_userdata();

        $this->view->assign('session' , $this->cSESSION);

        if(!$this->checkPermission())
        {
            //$this->view->assign('session' , array('username'=>''));
            $this->showMsg('您没有权限进行此操作');
        }

        $this->view->assign('cTime' , time());
        $this->view->assign('cClass' , $this->getClass());
        $this->view->assign('cMethod' , $this->getMethod());
        $this->view->assign('pay_type' , $this->_global_pay_type);
        $this->view->assign('pay_type_print' , $this->_global_pay_type_print);
        $this->view->assign('test_times' , $this->_global_test_times);

        $this->view->assign('_area' , $this->_area);
        $this->view->assign('_province' , $this->_province);
        $this->view->assign('_global_data' , $this->_global_data);
        $this->view->assign('_global_permission' , $this->_global_permission);

        //print_r($this->cSESSION);
        //控制不同的类型初始化不同的数据
        /*$initTypes = array('web' , 'admin' , 'api' ,'open'); 
        if( $initType == 'open' )
        {
            return '';
        }  
        $this->load->library('view'); 
        $this->load->library('sso');   
        $this->isLogined = $this->sso->isLogined();
        //登录的时候都要处理的操作
        if( $this->isLogined )
        {
            $ssoUserInfo = $this->sso->getUserInfo();
            //取得通行证ID
            $this->cuid = $ssoUserInfo['uniqueid']; 
            $this->ssoUserInfo = $ssoUserInfo;
        }
        //未登录，需要跳转时跳转
        if( (!$this->isLogined ) && in_array( $initType , array( 'admin')))
        {
            $this->jumplogin();
        }
        //初始web
        if( $initType == 'web' )
        {
            
        }
        //当为管理后台时
        if( $initType == 'admin' && $this->isLogined )
        {
            $this->load->model('Admin_model' , 'admin');
            $adminInfo = $this->admin->getOneByKey( array( 'uid' => $this->cuid ));
            if( empty( $adminInfo ))
            {
                $this->showMsg('没有权限');
            }
        }*/
    }

    protected function _get_ip()
    {
        $cip = (isset($_SERVER['HTTP_CLIENT_IP']) AND $_SERVER['HTTP_CLIENT_IP'] != "") ? $_SERVER['HTTP_CLIENT_IP'] : FALSE;
        $rip = (isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : FALSE;
        $fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'] != "") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : FALSE;

        if ($cip && $rip)   $this->_IP = $cip;
        elseif ($rip)       $this->_IP = $rip;
        elseif ($cip)       $this->_IP = $cip;
        elseif ($fip)       $this->_IP = $fip;

        if (strpos($this->_IP, ',') !== FALSE)
        {
            $x = explode(',', $this->_IP);
            $this->_IP = end($x);
        }

        if ( ! preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $this->_IP))
        {
            $this->_IP = '0.0.0.0';
        }

        unset($cip);
        unset($rip);
        unset($fip);

        return $this->_IP;
    }

    public function addlog($desc,$uid=0,$did=0)
    {
        $para   =   array(
            'uid'   =>  intval($uid),
            'did'   =>  intval($did),
            'desc'   =>  $desc,
            'class' =>  $this->getClass(),
            'method'    =>  $this->getMethod(),
            'ip'=>  $this->_get_ip(),
            );
        $insert_id  =   $this->log->add($para);
        return true;
    }

    /**
     * 未登录跳转
     */
    /*public function jumplogin()
    {
        $self = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $self = urlencode( $self );
        header("Location:http://login.sina.com.cn/signup/signin.php?entry=supports_auto&r={$self}");
        exit;
    }
    /**
     * 初始web 
     */ 
    /*public function initWeb( $uid )
    {
        //当$uid 为空 并且未登录时登录
        if( empty( $uid ) && !$this->isLogined )
        {
            $this->jumplogin();
        }
        
        $this->load->model('User_model');
        if( $this->isLogined  )
        {
            //当为前台时记录用户信息
            $userInfo = $this->User_model->getUser(  $this->ssoUserInfo );
            $this->cUserInfo = $userInfo;
        }
         
        //判断用户身份
        if( empty( $uid ))
        {
            $uid = $this->cuid;
        }
        // 判断是否是自己
        if( $uid == $this->cuid )
        {
            $this->isself = 1;
            $this->visitUserInfo = $this->cUserInfo;
            $this->visitUid = $this->cuid;
        }else
        {
            $this->isself = 0;
            $userInfo = $this->User_model->getUser( $uid );  
            if( empty( $userInfo ))
            {
                header('Location:http://travel.sina.com.cn/');
                exit;
            }
            $this->visitUserInfo = $userInfo;
            $this->visitUid = $uid;  
        }
        $this->view->assign('isself' , $this->isself);
        $this->view->assign('visitUserInfo' , $this->visitUserInfo);       
        //足迹
        $this->load->model('api/App_model' , 'app');
        $aimAum = $this->app->getAimNum( $this->visitUid, FALSE);  
    
        $this->view->assign('aimAum' , $aimAum);
        //是否是旅行家
        $this->load->model('Travellers_model' , 'travellers');

        $traveller = $this->travellers->getTraveller( $this->visitUid );
        $isTravellers =0; 
        if( !empty( $traveller ))
        {
            $isTravellers = 1;
            $this->view->assign('traveller' , $traveller);
        }
         
        $this->view->assign('isTravellers' , $isTravellers);
        //头部 尾部
        $this->load->model('Includedata_model' , 'includedata');
        //头部缓存时间为1分钟
        $header = $this->includedata->getHeadTop(60);
        $footer = $this->includedata->getFooter(60);
        $this->view->assign('headerhtml' , $header);
        $this->view->assign('footerhtml' , $footer);
    }
    /**
     * @author mingyu <mingyu2@staff.sian.com>
     * 通用的信息提示页
     */  
    public function showMsg( $msg = '' , $url = '' , $button='' , $button_url='',$bak='yes',$red=false)
    {
        $this->view->assign('msg' , $msg);
        $this->view->assign('url' , $url);
        $this->view->assign('button' , $button);
        $this->view->assign('button_url' , $button_url);
        $this->view->assign('bak' , $bak);
        $this->view->assign('red' , $red);
        $this->view->display('msgpage.html');
        exit;
    }
    /**
     *@author mingyu <mingyu2@staff.sian.com>
     * ajax 请处理是返回的消息提示
     * @param string $msg 信息提示
     * @param int $ret 为1表示成功
     */  
    public function showJsonMsg(  $msg , $ret = 0)
    {
        $data =array('ret' => $ret , 'data' => $msg);
        die(json_encode( $data ));
    }

    public function getClass()
    {
        return $this->router->class;
    }

    public function getMethod()
    {
        return $this->router->method;
    }

    public function checkPermission()
    {
        //return true;
        if($this->getClass()    ==  'login' ||  $this->getClass()    ==  'api' ||  $this->getClass()    ==  'index') return true;
        $permissionLen  =   strlen($this->cSESSION['permission']);
        //echo $permissionLen;
        if($permissionLen   >   count($this->_global_permission))
        {
            $permissionLen  =   count($this->_global_permission);
        }
        for($i=0;$i<$permissionLen;++$i)
        {
            if($this->cSESSION['permission']{$i}    ==  '1')
            {
                $con_tmp    =   count($this->_global_permission[$i]);
                for($k=2;$k<$con_tmp;++$k)
                {
                    //echo $this->_global_permission[$i][$k][0].' '.$this->_global_permission[$i][$k][1].'    '.$this->getClass().'   '.$this->getMethod()."\n";
                    if($this->_global_permission[$i][$k][0] ==  $this->getClass()   &&  $this->_global_permission[$i][$k][1] ==  $this->getMethod())
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}