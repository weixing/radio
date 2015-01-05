<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Device extends MY_Controller {

	public $add	=	false;	//是否编辑设备
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_info' , 'user_info');
		$this->load->model('device_info' , 'device_info');
		$this->load->model('pay_info' , 'pay_info');
		$this->load->model('photo' , 'photo');
		$this->load->model('yanji_rela' , 'yanji_rela');
		$this->view->assign('title' , '设备管理');
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

	public function index( $uid = '0' , $device_type	=	'0', $page = 1)
	{
		//$admin_info	=	$this->session->all_userdata();
		//print_r($admin_info);
		//
		$page = intval( $page ) > 0 ? intval( $page ):1;
		$uid  =	intval($uid);

		$device_pay_count	=	$this->pay_info->getDeviceCount($uid);
		$device_count	=	$this->device_info->getDeviceCount($uid);

		if($uid	>	0)
		{
			$profile	=	$this->user_info->getUser($uid);
		}

		if(!(intval($this->input->post('device_type'))	>	0))
		{
			$device_type  =	intval($device_type);
		}
		else
		{
			$device_type	=	intval($this->input->post('device_type'));
			$page=1;
		}

		if($uid	>	0)
		{
			$where_arr[]	=	"uid=".$uid;
		}
		if($device_type	>	0)
		{
			$where_arr[]	=	"device_type=".$device_type;
		}
		if(@is_array($where_arr))
		{
			$para['where'] = implode(' and ',$where_arr);
		}
		$para['page'] = $page;
		$para['perPage'] = PER_PAGE;
		$para['order'] = ' `uid` DESC ';

		$resData = $this->device_info->queryList( $para  , FALSE );
		foreach($resData['list'] as $key=>$value)
		{
			$resData['list'][$key]['user_info']	=	$this->user_info->getUser($value['uid']);
		}
		

		$this->view->assign('device_count' , $device_count);
		$this->view->assign('device_pay_count' , $device_pay_count);
		
		$this->view->assign('profile' , $profile);
		
		$this->view->assign('add' , $this->add);
		$this->view->assign('edit' , false);
		$this->view->assign('pageURL' , '/device/index/'.$uid.'/'.$device_type.'/');
		$this->view->assign('uid' , $uid);
		$this->view->assign('device_type' , $device_type);
		$this->view->assign('resData' , $resData);
		$this->view->display('device_list.html');
		
	}

	public function add($uid=0)
	{
		$uid	=	intval($uid);
		if($uid	==	0)
		{
			$this->showMsg('参数错误');
			exit();
		}
		$profile	=	$this->user_info->getUser($uid);
		$this->add	=	true;
		$this->view->assign('uid' , $uid);
		$this->view->assign('profile' , $profile);
		$this->index($uid);
		//$this->view->display('device_add.html');
	}

	public function detail( $did	=	0)
	{
		//print_r($uid);
		$detail	=	$this->device_info->getDevice($did);
		$profile	=	$this->user_info->getUser($detail['uid']);
		//print_r($profile);
		$this->view->assign('edit' , true);
		$this->view->assign('detail' , $detail);
		$this->view->assign('profile' , $profile);
		$this->view->assign('uid' , $detail['uid']);
		$this->view->display('device_detail.html');
		
	}

	public function save()
	{
		$uid	=	intval($this->input->post('uid'));
		if(!($uid	>	0))
		{
			$this->showMsg('参数错误');
		}
		$profile	=	$this->user_info->getUser($uid);
		$save_data	=	$this->input->post();
		$devxh	=	$this->input->post('devxh');
		$devchuchanghao	=	$this->input->post('devchuchanghao');
		$devcj	=	$this->input->post('devcj');

		$save_data['devcj']	=	strtoupper($save_data['devcj']);
		$save_data['devxh']	=	strtoupper($save_data['devxh']);
		$save_data['devchuchanghao']	=	strtoupper($save_data['devchuchanghao']);
		
		if(	$devxh	==	''	||	$devchuchanghao	==	''	||	$devcj	==	'')
		{
			$this->showMsg('请填写全部必填项');
			exit();
		}

		$did	=	intval($this->input->post('did'));
		$fsgl	=	$this->_global_data['def_power'][$save_data['device_type']];
		if($save_data['device_type']	==	4)
		{
			$fsgl	=	$this->_global_data['power'][$profile['Oplev']];
		}
		$save_data['fsgl']	=	$fsgl;
		if($did	>	0)
		{
			$detail	=	$this->device_info->getDevice($did);
			if($detail['status']==0)
			{
				$this->showMsg('设备已经被删除，信息不能修改','/device/index/'.$detail['uid'].'/');
				exit();
			}
			$this->addlog('修改设备',$uid,$did);
			$this->device_info->update( $save_data ,  array('did' => $did ));
		}
		else
		{
			//$sql	=	"insert into user_info ".$sql;
			$device_pay_count	=	$this->pay_info->getDeviceCount($uid);
			$device_count	=	$this->device_info->getDeviceCount($uid);
			/*if($device_pay_count	<=	$device_count)
			{
				$this->showMsg('所缴纳的组网费不足，请先补缴组网费','/user/pay/'.$uid.'/');
			}*/
			//取消费用
			
			$did_insert	=	$this->device_info->add($save_data);
			$this->user_info->update( array('isyj'=>1) ,  array('uid' => $uid ));
			$this->addlog('添加设备',$uid,$did_insert);

		}
		$this->showMsg('保存成功','/device/index/'.$uid.'/');
		//echo $sql;
	}

	public function yanji($did)
	{
		$did	=	intval($did);
		if(!($did	>	0))
		{
			$this->showMsg('参数错误');
		}
		$detail	=	$this->device_info->getDevice($did);
		$profile	=	$this->user_info->getUser($detail['uid']);
		if($profile['test_pass']	!=	1)
		{
			$this->showMsg('会员考试未通过，不能验机','/device/index/'.$detail['uid'].'/');
			exit();
		}

		$avatarData	=	$this->photo->getAvatar($detail['uid']);
		if(!is_array($avatarData))
		{
			$this->showMsg('会员未提交照片信息，不能验机','/device/index/'.$detail['uid'].'/');
			exit();
		}

		$photoData	=	$this->photo->getAllPhoto($detail['uid']);
		if(count($photoData)	<	3)
		{
			$this->showMsg('会员身份证正反面扫描数据信息不全，不能验机','/device/index/'.$detail['uid'].'/');
			exit();
		}

		

		$this->device_info->update( array('isyj'=>1) ,  array('did' => $did ));
		$this->yanji_rela->change_pici($detail['uid']);
		$device_count	=	$this->device_info->getDeviceCount($detail['uid'],0);
		if($device_count	==	0)
		{
			$this->user_info->update( array('isyj'=>0) ,  array('uid' => $detail['uid'] ));
		}
		$this->addlog('验机通过',$detail['uid'],$did);
		$this->showMsg('验机完成','/device/index/'.$detail['uid'].'/');
		//echo $sql;
	}


	public function status($did,$status)
	{
		$did	=	intval($did);
		$status	=	intval($status);

		if(!($did	>	0))
		{
			$this->showMsg('参数错误');
		}
		$this->device_info->update( array('status'=>$status) ,  array('did' => $did ));
		$detail	=	$this->device_info->getDevice($did);

		if($status	==	1)
		{
			$this->addlog('恢复删除设备',$detail['uid'],$did);
			$this->showMsg('设备恢复成功','/device/index/'.$detail['uid'].'/');
		}
		else
		{
			$this->addlog('删除设备',$detail['uid'],$did);
			$this->showMsg('设备删除成功','/device/index/'.$detail['uid'].'/');
		}

		//echo $sql;
	}

	public function deletedev($did)
	{
		$this->status($did,0);
	}

	public function recoverdev($did)
	{
		$this->status($did,1);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */