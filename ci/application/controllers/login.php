<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->view->display('login.html');
	}

	public function run()
	{
		$this->load->model('admin' , 'admin');
		$username = $this->input->post('username');
		$passwd = $this->input->post('passwd');
		$admin_info	=	$this->admin->getOneByKey( array('username'=>$username,'pwd'=>md5($passwd),'status'=>1) , FALSE);
		//print_r($admin_info);
		if($admin_info['status']	!=	1)
		{
			$this->showMsg('用户密码错误，或账号已冻结');
		}
		$sessionlen	=	count($this->_global_permission);
		$admin_info['permission']	=	str_pad($admin_info['permission'],$sessionlen,'0',STR_PAD_RIGHT);
		$admin_info['logintime']	=	time();
		$this->session->set_userdata($admin_info);
		header('location:/index/');
	}

	public function logout()
	{
		$this->session->unset_userdata('aid');
		header('location:/index/');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */