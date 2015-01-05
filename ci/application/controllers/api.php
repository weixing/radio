<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('pinyin'); 
    }
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

	public function index( $input_str = '')
	{
		//print_r(urldecode($input_str));
		echo json_encode($this->pinyin->to_pinyin(urldecode($input_str),'utf-8'));
	}


	public function ip()
	{
		if(!empty($_SERVER["HTTP_CLIENT_IP"]))
		{
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif(!empty($_SERVER["REMOTE_ADDR"]))
		{
			$cip = $_SERVER["REMOTE_ADDR"];
		}
		else{
			$cip = "无法获取！";
		}
		echo $cip;
	}


	public function showlogo()
	{
		Header("Content-type: image/png");
		//$im =imagecreate(46,16);
		$im 	=	ImageCreateTrueColor(221,40);
		$white	=	ImageColorAllocate($im, 255,255,255);
		$black	=	ImageColorAllocate($im, 0,0,0);

		imagecolortransparent($im,$black);
		imagefilledrectangle($im, 0, 0, 221, 40, $black);


		$check_str = PRO_NAME;
		//echo 'b';
		$font = './static/msyh.ttf';

		$len	=	strlen($check_str);

		imagettftext($im, 16, 0, (123-$len*4), 30,$white,$font,$check_str);

		ImagePng($im);
		ImageDestroy($im);
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */