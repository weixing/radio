<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Set extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_info' , 'user_info');
        $this->load->model('admin' , 'admin');
        $this->load->model('pay_amount' , 'pay_amount');
        $this->load->model('pici_info' , 'pici_info');
        $this->load->model('yanjipici_info' , 'yanjipici_info');
        $this->load->model('general_data' , 'general_data');
        $this->load->model('level_info' , 'level_info');
        $this->view->assign('title' , '系统设置');
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

	public function index()
	{
		$this->view->assign('resData' , $resData);
		$this->view->display('user_list.html');
		
	}

	public function pay()
	{
		//$resData	=	$this->pay_amount->showList();
		//print_r($resData);
		$resData	=	array();
        foreach($this->_global_pay_type as $key    =>  $value)
        {
        	$pay_arr_tmp	=	$this->pay_amount->getAmountByType($key);
        	if(!$pay_arr_tmp)
        	{
        		$pay_arr_tmp	=	array('pay_type'=>$key,'amount'=>0,'group_amount'=>0,'createtime'=>0);
        	}
            $resData[]  =   $pay_arr_tmp;
        }

		$this->view->assign('resData' , $resData);
		$this->view->display('set_pay.html');
	}

	public function payedit($type=0)
	{
		$resData	=	$this->pay_amount->getAmountByType($type);
		if(!$resData)
		{
			$resData	=	array('pay_type'=>$type,'amount'=>0,'group_amount'=>0,'createtime'=>0);
		}
		//print_r($resData);
		$this->view->assign('resData' , $resData);
		$this->view->assign('type' , $type);
		$this->view->display('set_payedit.html');
	}

	public function payrun()
	{
		$pay_type	=	intval($this->input->post('pay_type'));
		$amount	=	intval($this->input->post('amount'));
		$group_amount	=	intval($this->input->post('group_amount'));
		//echo $pay_type;
		//echo $amount;
		$resData	=	$this->pay_amount->getAmountByType($pay_type);
		if(!$resData)
		{
			$resData	=	array('pay_type'=>$type,'amount'=>0,'group_amount'=>0,'createtime'=>0);
		}

		if($resData['amount']	!=	$amount || $resData['group_amount']	!=	$group_amount)
		{
			$this->addlog('修改'.$this->_global_pay_type[$pay_type][0].',费用金额为：'.$amount.','.$group_amount);
			$this->pay_amount->add(array('amount'=>$amount,'group_amount'=>$group_amount,'pay_type'=>$pay_type));
			$this->showMsg('修改成功','/set/pay/');
		}
		else
		{
			$this->showMsg('价格没有修改');
		}
	}

	public function paylist($pay_type=0,$page=1)
	{
		$page = intval( $page ) > 0 ? intval( $page ):1;
		$where	=	"pay_type='".$pay_type."'";
		$para['where'] = $where;

		$para['page'] = $page;
		$para['perPage'] = PER_PAGE;
		$para['order'] = ' `id` DESC ';
		//print_r($para);
		//echo urlencode($search);
		$resData = $this->pay_amount->queryList( $para  , FALSE );
		foreach($resData['list'] as $key=>$value)
		{
			$resData['list'][$key]['admin_info']	=	$this->admin->getAdmin($value['createadmin']);
		}
		//print_r($resData);
		$this->view->assign('pageURL' , '/set/paylist/'.$pay_type.'/');
		$this->view->assign('resData' , $resData);
		$this->view->display('set_paylist.html');

	}


	public function pici()
	{
		//$resData	=	$this->pay_amount->showList();
		//print_r($resData);
		$resData	=	$this->pici_info->getAll();
		//print_r($resData);

		$this->view->assign('resData' , $resData);
		$this->view->display('set_pici.html');
	}

	public function piciadd()
	{
		$this->view->display('set_piciadd.html');
	}

	public function picirun()
	{
		$pcname	=	$this->input->post('pcname');
		$para	=	array('pcname'=>$pcname);
		$pici_id	=	$this->pici_info->add( $para );
		$this->addlog('添加批次:'.$pcname.'，id：'.$pici_id);

		$this->showMsg('批次添加成功','/set/pici/');
	}

	public function picidel($pid=0)
	{
		$pid	=	intval($pid);
		if($pid>0)
		{
			$this->addlog('删除批次，id：'.$pid);
			$this->pici_info->update( array('status'=>0) ,  array('pid' => $pid ));
		}
		
		$this->showMsg('批次删除成功','/set/pici/');
	}

	public function pichuanzheng($pid=0 , $huanzheng=0)
	{
		$pid	=	intval($pid);
		$huanzheng	=	intval($huanzheng);

		$resData	=	$this->pici_info->getAll();
		if($resData[0]['pid']	<=	$pid)
		{
			$this->showMsg('最新批次不能设置为换证批次','/set/pici/');
			exit();
		}

		if($huanzheng	!==	1)
		{
			$huanzheng	=	0;
		}
		if($pid>0)
		{
			if($huanzheng	===	1)
			{
				$this->addlog('设置为换证批次，id：'.$pid);
			}
			else
			{
				$this->addlog('取消换证批次，id：'.$pid);
			}
			$this->pici_info->update( array('huanzheng'=>$huanzheng) ,  array('pid' => $pid ));
		}
		
		$this->showMsg('批次设置成功','/set/pici/');
	}

	public function yanjipici()
	{
		//$resData	=	$this->pay_amount->showList();
		//print_r($resData);
		$resData	=	$this->yanjipici_info->getAll();
		//print_r($resData);

		$this->view->assign('resData' , $resData);
		$this->view->display('set_yanjipici.html');
	}

	public function yanjipiciadd()
	{
		$this->view->display('set_yanjipiciadd.html');
	}

	public function yanjipicirun()
	{
		$pcname	=	$this->input->post('pcname');
		$para	=	array('pcname'=>$pcname);
		$pici_id	=	$this->yanjipici_info->add( $para );
		$this->addlog('添加验机批次:'.$pcname.'，id：'.$pici_id);
		$this->showMsg('批次添加成功','/set/yanjipici/');
	}

	public function yanjipicisub($pid=0)
	{
		$pid	=	intval($pid);
		if($pid>0)
		{
			$this->addlog('提交验机批次，id：'.$pid);
			$this->yanjipici_info->update( array('sub'=>1,'sub_time'=>time()) ,  array('pid' => $pid ));
		}
		
		$this->showMsg('批次提交成功','/set/yanjipici/');
	}

	public function yanjipicidel($pid=0)
	{
		$pid	=	intval($pid);
		if($pid>0)
		{
			$this->addlog('删除验机批次，id：'.$pid);
			$this->yanjipici_info->update( array('status'=>0) ,  array('pid' => $pid ));
		}
		
		$this->showMsg('批次删除成功','/set/yanjipici/');
	}

	public function pwd()
	{
		$this->view->display('set_pwd.html');
	}

	public function pwdrun()
	{
		$cur_pwd	=	$this->input->post('cur_pwd');
		$new_pwd	=	$this->input->post('new_pwd');
		$re_pwd	=	$this->input->post('re_pwd');


		$admin_info	=	$this->admin->getOneByKey( array('username'=>$this->cSESSION['username'],'pwd'=>md5($cur_pwd)) , FALSE);
		if($admin_info	&&	$admin_info['aid']==$this->cSESSION['aid']	&&	$new_pwd==$re_pwd	&&	$re_pwd!='')
		{
			$this->admin->update( array('pwd'=>md5($new_pwd)) ,  array('aid' => $this->cSESSION['aid'] ));
			$this->showMsg('密码修改成功');
		}
		$this->showMsg('密码修改失败');
		
		//$this->view->display('set_pwd.html');
	}

	public function payyear()
	{
		$year_data	=	$this->general_data->getYear();
		$this->view->assign('year_data' , $year_data);
		$this->view->display('set_payyear.html');
	}

	public function payyearrun($method)
	{
		if($method	==	'add')
		{
			$year	=	$this->general_data->addYear();
			$this->addlog('曾加会费年限到：'.$year);
		}
		else
		{
			$year	=	$this->general_data->deductYear();
			$this->addlog('减少会费年限到：'.$year);
		}
		$this->showMsg('会费年限修改成功','/set/payyear/');
	}

	public function level()
	{
		//$resData	=	$this->pay_amount->showList();
		//print_r($resData);
		$resData	=	$this->level_info->getAll();
		//print_r($resData);

		$this->view->assign('resData' , $resData);
		$this->view->display('set_level.html');
	}

	public function leveladd()
	{
		$this->view->display('set_leveladd.html');
	}

	public function levelrun()
	{
		$levelname	=	$this->input->post('levelname');
		$insert_id	=	$para	=	array('levelname'=>$levelname);
		$this->level_info->add( $para );
		$this->addlog('添加级别：'.$levelname.'，id：'.$insert_id);

		$this->showMsg('级别添加成功','/set/level/');
	}


	public function leveldel($lid=0)
	{
		$lid	=	intval($lid);
		if($lid>0)
		{
			$this->addlog('删除级别，id：'.$lid);
			$this->level_info->update( array('status'=>0) ,  array('lid' => $lid ));
		}
		
		$this->showMsg('级别删除成功','/set/level/');
	}

	public function adminlist($page = 1)
	{
		$page = intval( $page ) > 0 ? intval( $page ):1;

		$para['page'] = $page;
		$para['perPage'] = PER_PAGE;
		$para['order'] = ' `aid` DESC ';
		//print_r($para);
		//echo urlencode($search);
		$resData = $this->admin->queryList( $para  , FALSE );
		foreach($resData['list'] as $key=>$value)
		{
			if($value['createadmin']	>	0)
			{
				$resData['list'][$key]['admin_info']	=	$this->admin->getAdmin($value['createadmin']);
			}
			else
			{
				$resData['list'][$key]['admin_info']['username']	=	'-';
			}
			
		}
		//print_r($resData);
		$this->view->assign('pageURL' , '/set/adminlist/');
		$this->view->assign('resData' , $resData);
		$this->view->display('set_adminlist.html');
	}

	public function admin()
	{

		$this->view->display('set_adminlist.html');
	}

	public function freezeadmin($aid=0)
	{
		$aid	=	intval($aid);
		if($aid	<=	0)
		{
			$this->showMsg('参数错误');
		}
		if($aid	==	$this->cSESSION['aid'])
		{
			$this->showMsg('不能冻结自己的账号');
		}

		$this->addlog('冻结/解冻管理员，管理员id：'.$aid);

		$admin_info	=	$this->admin->getAdmin($aid);

		$this->admin->update( array('status'=>($admin_info['status']==1?0:1)) ,  array('aid' => $aid ));
		//$this->view->display('set_adminlist.html');
		$this->showMsg('操作成功','/set/adminlist/');
	}

	public function adminedit($aid=0)
	{
		$aid	=	intval($aid);
		if($aid	==	$this->cSESSION['aid'])
		{
			$this->showMsg('不能修改自己的账号');
		}
		if($aid	==	1)
		{
			$this->showMsg('不能修改超级管理员账号');
		}
		if($aid	>	0)
		{
			$admin_info	=	$this->admin->getAdmin($aid);
			$admin_info['permission']	=	str_pad($admin_info['permission'],count($this->_global_permission)," ",STR_PAD_RIGHT); 
		}
		else
		{
			$admin_info	=	array();
		}


		$this->view->assign('aid' , $aid);
		$this->view->assign('admin_info' , $admin_info);
		$this->view->display('set_adminedit.html');

	}

	public function adminrun()
	{
		$aid	=	intval($this->input->post('aid'));
		if($aid	==	$this->cSESSION['aid'])
		{
			$this->showMsg('不能修改自己的账号');
		}

		$username	=	$this->input->post('username');
		$name	=	$this->input->post('name');
		$pwd	=	$this->input->post('pwd');
		$repwd	=	$this->input->post('repwd');
		if($pwd	!=	$repwd)
		{
			$this->showMsg('两次密码填写不同');
		}

		$para	=	array(
			'username'	=>	$username,
			'name'	=>	$name,
			);
		if($pwd	!=	'')
		{
			$para['pwd']	=	MD5($pwd);
		}

		$para['permission']	=	str_pad('0',count($this->_global_permission),'0',STR_PAD_LEFT);
		//print_r($para);
		foreach($this->_global_permission as $key=>$value)
		{
			$per_flag	=	$this->input->post('permission_'.$key);
			$per_flag	=	($this->cSESSION['permission']{$key}	==	$per_flag)?$per_flag:'0';
			$para['permission']{$key}	=	$per_flag;
		}
		//print_r($para);
		if($aid	>	0)
		{
			$para['modifytime']	=	time();
			$para['modifyadmin']	=	$this->cSESSION['aid'];
			$this->admin->update( $para ,  array('aid' => $aid ));
			//$admin_info	=	$this->admin->getAdmin($aid);
			$this->addlog('修改管理员信息，管理员id：'.$aid);

		}
		else
		{
			$aid_insert	=	$this->admin->add( $para);
			$this->addlog('新建管理员，管理员id：'.$aid_insert);
		}

		$this->showMsg('操作成功','/set/adminlist/');
	}

	public function log($membernum=0,$did=0,$start_date='0000-00-00',$end_date='0000-00-00',$admin='all',$type='default',$page=1)
	{
		//$admin_info	=	$this->session->all_userdata();
		//print_r($admin_info);
		//
		$membernum	=	$this->input->post('membernum')?$this->input->post('membernum'):$membernum;
		$did	=	$this->input->post('did')?$this->input->post('did'):$did;
		$start_date	=	$this->input->post('start_date')?$this->input->post('start_date'):$start_date;
		$end_date	=	$this->input->post('end_date')?$this->input->post('end_date'):$end_date;
		$type	=	$this->input->post('type')?$this->input->post('type'):$type;
		$admin	=	$this->input->post('admin')?$this->input->post('admin'):$admin;

		$page = intval( $page ) > 0 ? intval( $page ):1;

		$where_arr	=	array();
		if($membernum!='')
		{
			$profile	=	$this->user_info->getUserByCus( array('membernum'=>$membernum) );
			if($profile)
			{
				$where_arr[]	=	'uid='.$profile['uid'];
			}
		}
		if($did	>	0)
		{
			$where_arr[]	=	'did='.intval($did);
		}
		if($start_date!=''	&&	$start_date!='0000-00-00')
		{
			$where_arr[]	=	'time>'.intval(strtotime($start_date));
		}
		if($end_date!=''	&&	$end_date!='0000-00-00')
		{
			$where_arr[]	=	'time<'.intval(strtotime($end_date.' 23:59:59'));
		}
		if($admin!='all')
		{
			$admin_info	=	$this->admin->getOneByKey( array('username'=>$admin) , FALSE);
			if($admin_info['aid']	>	0)
			{
				$where_arr[]	=	"admin=".$admin_info['aid'];
			}
		}
		if($type!=''	&&	$type!='default')
		{
			$where_arr[]	=	"class='".$type."'";
		}

		if(count($where_arr)	>	0)
		{
			$para['where']	=	implode(' and ',$where_arr);
		}
		$para['page'] = $page;
		$para['perPage'] = PER_PAGE;
		$para['order'] = ' `id` DESC ';
		//print_r($para);
		//echo urlencode($search);
		$resData = $this->log->queryList( $para  , FALSE );
		$admin_list	=	array();

		foreach($resData['list'] as $key=>$value)
		{
			//print_r($value);
			if(!array_key_exists($value['admin'],$admin_list))
			{
				$admin_list[$value['admin']]	=	$this->admin->getAdmin($value['admin']);
			}
			$resData['list'][$key]['admin_info']	=	$admin_list[$value['admin']];
		}
		//print_r($resData);
		$this->view->assign('pageURL' , '/'.$this->getClass().'/'.$this->getMethod().'/'.$membernum.'/'.$did.'/'.$start_date.'/'.$end_date.'/'.$admin.'/'.$type.'/');
		$this->view->assign('membernum' , $membernum);
		$this->view->assign('did' , $did);
		$this->view->assign('start_date' , $start_date);
		$this->view->assign('end_date' , $end_date);
		$this->view->assign('admin' , $admin);
		$this->view->assign('type' , $type);
		$this->view->assign('resData' , $resData);
		
		$this->view->display('set_log.html');
		
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */