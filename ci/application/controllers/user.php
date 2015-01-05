<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_info' , 'user_info');
        $this->load->model('remark' , 'remark');
        $this->load->model('pay_info' , 'pay_info');
        $this->load->model('pici_info' , 'pici_info');
        $this->load->model('yanjipici_info' , 'yanjipici_info');
        $this->load->model('yanji_rela' , 'yanji_rela');
        $this->load->model('level_info' , 'level_info');
        $this->load->model('pay_amount' , 'pay_amount');
		$this->load->model('photo' , 'photo');
		$this->load->library('uploadtool');
		//$this->load->library('phpzip');
        $this->load->library('strtoint');
        $this->view->assign('title' , '会员管理');
        $this->profile_show	=	0;//0用户信息 1缴费列表 2缴费


        $this->pici_list	=	$this->pici_info->getAll();
        $this->level_list	=	$this->level_info->getAll();
        $this->view->assign('pici_list' , $this->pici_list);
        $this->view->assign('level_list' , $this->level_list);

        $this->listcustom	=	0;//各种功能性列表
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

	public function index( $search = 'default' , $page = 1)
	{
		//$admin_info	=	$this->session->all_userdata();
		//print_r($admin_info);
		//
		$page = intval( $page ) > 0 ? intval( $page ):1;

		$search_from_post	=	$this->input->post('search');
		if(!empty($search_from_post))
		{
			$search	=	$search_from_post;
			$page	=	1;
		}
		$search	=	urldecode($search);
		if(empty($search))
		{
			$search='default';
		}

		$where_arr	=	array();

		if($search!='default')
		{
			$where	=	"(username like '%".addslashes($search)."%' or userIDcard like '".addslashes($search)."%' or huhao like '%".addslashes(strtoupper($search))."%' or membernum like '%".addslashes($search)."%' or pa like '%".addslashes($search)."%' or cardno like '%".addslashes($search)."%' or pci='".addslashes($search)."')";
			$where_arr[] = $where;
		}
		if($this->listcustom	==	100)	//注册考试
		{
			$where	=	"(test_pass=0 and ((test_charge+1)*".$this->_global_test_times['A'].")>test_times) and type=1";
			$where_arr[] = $where;
		}

		if($this->listcustom	==	104)	//B级考试
		{
			$where	=	"(Oplev='A' and (testb_charge*".$this->_global_test_times['B'].")>testb_times) and type=1";
			$where_arr[] = $where;
		}

		if($this->listcustom	==	105)	//C级考试
		{
			$where	=	"(Oplev='B' and (testc_charge*".$this->_global_test_times['C'].")>testc_times) and type=1";
			$where_arr[] = $where;
		}

		if($this->listcustom	==	101)
		{
			$where	=	"isyj=1";
			$where_arr[] = $where;
		}

		if($this->listcustom	==	102)
		{
			$user_list_yanji	=	$this->yanji_rela->getAllByPid($this->_listyanjipid);
			$user_list_yanji_arr	=	array();
			foreach($user_list_yanji as $value)
			{
				$user_list_yanji_arr[]	=	$value['uid'];
			}
			$where	=	"uid in (".implode(',',$user_list_yanji_arr).")";
			$where_arr[] = $where;
		}

		$para['where']	=	implode(' and ',$where_arr);
		$para['page'] = $page;
		$para['perPage'] = PER_PAGE;
		$para['order'] = ' `uid` DESC ';
		//print_r($para);
		//echo urlencode($search);
		$resData = $this->user_info->queryList( $para  , FALSE );
		//print_r($resData);
		if($this->listcustom	==	102)
		{
			$this->view->assign('pageURL' , '/'.$this->getClass().'/'.$this->getMethod().'/'.$this->_listyanjipid.'/'.urlencode($search).'/');
		}
		else
		{
			$this->view->assign('pageURL' , '/'.$this->getClass().'/'.$this->getMethod().'/'.urlencode($search).'/');
		}
		$this->view->assign('search' , $search	==	'default'?'':$search);
		$this->view->assign('resData' , $resData);
		
		$this->view->display('user_list.html');
		
	}

	public function import()
	{
		$this->view->display('user_import.html');
	}

	public function importrun()
	{
		$this->load->library('pinyin');
		if ($_FILES["importfile"]["error"]	>	0)
		{
			$this->showMsg('文件上传错误');
		}

		if($_FILES["importfile"]["type"]	!=	'application/vnd.ms-excel')
		{
			$this->showMsg('文件类型错误，请上传.csv文件');
		}
		/*if (!((($_FILES["uploadfile"]["type"] == "image/gif")
		|| ($_FILES["uploadfile"]["type"] == "image/jpeg")
		|| ($_FILES["uploadfile"]["type"] == "image/pjpeg"))
		&& ($_FILES["uploadfile"]["size"] < 100001)))*/
		$importstr	=	file_get_contents($_FILES["importfile"]["tmp_name"]);
		$import_arr	=	explode("\n",$importstr);
		$pici_list	=	$this->pici_info->getAll();
		//print_r($pici_list);
		//exit();
		foreach($import_arr as $value)
		{
			
			$e=mb_detect_encoding($value, array('UTF-8', 'GBK','CP936'));
			//echo 'aaaa'.$e;
			if($e	!=	'UTF-8')
			{
				$value	=	iconv('CP936', 'UTF-8', $value);
			}
			$import_one	=	explode(',',$value);
			//print_r($import_one);
			if(count($import_one)	>	10)
			{
				$userIDcard_tmp	=	trim(str_replace("'", '', $import_one[0]));
				$profile_tmp	=	$this->user_info->getUserByCard( $userIDcard_tmp );
				if(intval($profile_tmp['uid'])	>	0)
				{
					//echo $profile_tmp['uid']."\n";
					$para	=	array('crac_id'=>$import_one[10]);
					$this->user_info->update( $para ,  array('uid' => $profile_tmp['uid'] ));
					$this->addlog('无管局添加会员操作证号',$profile_tmp['uid']);
				}
				else
				{
					//fecho $userIDcard_tmp."\n";
					$para	=	array(
						'userIDcard'	=>	$userIDcard_tmp,
						'username'	=>	$import_one[1],
						'userpinyin'	=>	$this->pinyin->to_pinyin($import_one[1],'utf-8'),
						'usersex'	=>	$import_one[2],
						'userbirthday'	=>	$import_one[3],
						//'username'	=>	$import_one[4],
						'Oplev'	=>	substr($import_one[5],0,1),
						//'username'	=>	$import_one[6],
						//'username'	=>	$import_one[7],
						//'username'	=>	$import_one[8],
						//'username'	=>	$import_one[9],
						'crac_id'	=>	$import_one[10],
						//'username'	=>	$import_one[11],
						//'username'	=>	$import_one[12],
						'txAd'	=>	$import_one[13],
						'txpost'	=>	$import_one[14],
						'mobTel'	=>	$import_one[15],
						'mail'	=>	$import_one[16],
						'isyj'	=>	0,
						'zhuceAd'	=>	'北京',
						'pci'	=>	$pici_list[0]['pcname'],

					);
					//print_r($para);
					$uid_insert	=	$this->user_info->add($para);
					$this->addlog('无管局导入新会员',$uid_insert);
				}
			}
		}

		$this->showMsg('数据成功导入');

	}

	public function paydata()
	{
		$this->load->model('admin' , 'admin');
		$admin_arr	=	$this->admin->getAll();
		$year_arr	=	array();
		for($i=date('Y');$i>1990;--$i)
		{
			$year_arr[]	=	$i;
		}
		$this->view->assign('year_arr' , $year_arr);
		$this->view->assign('admin_arr' , $admin_arr);
		$this->view->display('user_paydata.html');
	}

	public function paydatarun()
	{
		$aid		=	intval($this->input->post('aid'));
		$pay_year		=	intval($this->input->post('pay_year'));
		$pay_type	=	$this->input->post('pay_type');
		$inputPayDate_start	=	$this->input->post('inputPayDate_start');
		$inputPayDate_end	=	$this->input->post('inputPayDate_end');
		
		$this->addlog('导出付费数据');

        $this->load->model('admin' , 'admin');
		$admin_arr	=	$this->admin->getAll();
		$admin_list_arr	=	array();
		$admin_list_arr[0]	=	iconv("UTF-8",'GBK//IGNORE','历史数据');
		foreach($admin_arr as $key=>$value)
		{
			$admin_list_arr[$value['aid']]	=	$value['username'];
		}
		
		$where_arr	=	array();
		if($aid	>	0)
		{
			$where_arr[]	=	"pay_info.admin='".$aid."'";
		}

		if($pay_type	!==	'')
		{
			$where_arr[]	=	"pay_info.pay_type='".$pay_type."'";
		}

		if($pay_year	>	0)
		{
			$where_arr[]	=	"pay_info.year='".$pay_year."'";
		}

		if($inputPayDate_start	!=	'')
		{
			$where_arr[]	=	"pay_info.time>='".strtotime($inputPayDate_start." 00:00:00")."'";
		}

		if($inputPayDate_end	!=	'')
		{
			$where_arr[]	=	"pay_info.time<='".strtotime($inputPayDate_end." 23:59:59")."'";
		}

		$where_arr[]	=	"pay_info.uid>0";
		$where	=	implode(" and ", $where_arr);

		$sql	=	"select sum(amount) as sum_amount from pay_info where ".$where;

		$where_arr[]	=	"pay_info.uid=user_info.uid";	//补充条件，导出具体数据
		$where	=	implode(" and ", $where_arr);

		$sumDataRes	=	$this->pay_info->rexec($sql);
		$sumData	=	$sumDataRes->result_array();
		//print_r($sumData);
		//echo $sql;
		$sql	=	"select user_info.uid,user_info.username,user_info.crac_id,user_info.huhao,user_info.txAd,user_info.txpost,pay_info.amount,pay_info.pay_type,pay_info.time,pay_info.year,pay_info.admin from pay_info,user_info where ".$where." order by pay_info.id";
		//echo $sql;
		$i	=	0;
		$fp	=	fopen(FILEROOT.'pay_data.csv','w');
		fputs($fp,iconv("UTF-8",'GBK//IGNORE','用户id,姓名,操作证号,呼号,通讯地址,邮编,金额,费用类型,缴纳时间,费用年限,管理员')."\n");
		$exportRes	=	$this->pay_info->importAll($sql,$fp,$this->_global_pay_type_print,$admin_list_arr);
		fputs($fp,iconv("UTF-8",'GBK//IGNORE','合计总金额,	').$sumData[0]['sum_amount']."\n");
		fclose($fp);

		$this->view->display('user_paydatarun.html');
	}

	public function export()
	{
		$resData	=	$this->yanjipici_info->getAll();
		$this->view->assign('resData' , $resData);
		$this->view->display('user_export.html');
	}

	public function exportrun()
	{
		$pici	=	$this->input->post('pici');
		$yanjipici	=	intval($this->input->post('yanjipici'));
		$huhao_change	=	intval($this->input->post('huhao_change'));
		$huhao_old	=	intval($this->input->post('huhao_old'));
		$id_card	=	$this->input->post('id_card');



		$inputDate_start	=	$this->input->post('inputDate_start');
		$inputDate_end	=	$this->input->post('inputDate_end');
		$test_pass	=	$this->input->post('test_pass');
		$cracid	=	$this->input->post('cracid');
		$qiandao	=	$this->input->post('qiandao');
		$crac	=	intval($this->input->post('crac'));
		$huanzheng	=	intval($this->input->post('huanzheng'));
		$user_type	=	intval($this->input->post('user_type'));
		$Oplev	=	$this->input->post('Oplev');

		$pay_type	=	$this->input->post('pay_type');
		$pay	=	$this->input->post('pay');
		$inputPayDate_start	=	$this->input->post('inputPayDate_start');
		$inputPayDate_end	=	$this->input->post('inputPayDate_end');


		if($user_type	==	2)
		{
			$crac	=	0;
		}
		
		$this->addlog('导出数据');

		$where_arr	=	array();
		if($pici!='')
		{
			$where_arr[]	=	"user_info.pci='".$pici."'";
		}
		if($id_card!='')
		{
			$id_card_arr	=	explode("\n",$id_card);
			$id_card_where	=	array();
			foreach($id_card_arr as $one_id_card)
			{
				$one_id_card	=	str_replace('"', '', $one_id_card);
				$one_id_card	=	str_replace("'", '', $one_id_card);
				$id_card_where[]	=	"'".trim($one_id_card)."'";
			}
			$where_arr[]	=	"user_info.userIDcard in (".implode(',',$id_card_where).")";
		}

		if($Oplev!='')
		{
			$where_arr[]	=	"user_info.Oplev='".$Oplev."'";
		}

		if(1)//$user_type>0)
		{
			$where_arr[]	=	"user_info.type=".$user_type;
		}

		if($test_pass!='')
		{
			$where_arr[]	=	"user_info.test_pass='".$test_pass."'";
		}


		if($cracid!='')
		{
			if($cracid	==	'0')
			{
				$where_arr[]	=	"user_info.crac_id is NULL";
			}
			else
			{
				$where_arr[]	=	"user_info.crac_id is not NULL";
			}
		}

		if($qiandao!='')
		{
			$where_arr[]	=	"user_info.qiandao='".$qiandao."'";
		}

		if($crac>0)
		{ 
			//$where_arr[]    =       "user_info.crac_id is NULL";
			$export_arr	=	$this->_user_export_wgj;	//导出无管局格式数据
			//$where_arr[]  =       "huhao like 'B%'";
		}
		else
		{
			$export_arr	=	$this->_user_export;	//导出全部数据
		}


		if($inputDate_start!='')
		{
			$where_arr[]	=	"user_info.createtime>".strtotime($inputDate_start.' 00:00:00');
		}
		if($inputDate_end!='')
		{
			$where_arr[]	=	"user_info.createtime<".strtotime($inputDate_end.' 23:59:59');
		}

		if($pay_type!='')
		{
			$pay_where_arr	=	array();
			//$from	=	"user_info left join pay_info on user_info.uid=pay_info.uid";
			$from	=	"user_info";
			if($inputPayDate_start!='')
			{
				$pay_where_arr[]	=	"`time`>".strtotime($inputPayDate_start.' 00:00:00');
			}
			if($inputPayDate_end!='')
			{
				$pay_where_arr[]	=	"`time`<".strtotime($inputPayDate_end.' 23:59:59');
			}
			$pay_where_arr[]	=	"pay_type=".intval($pay_type);
			if($pay	==	'1')
			{
				$where_arr[]	=	"user_info.uid in (select uid from pay_info where ".implode(" and ",$pay_where_arr).")";
			}
			else
			{
				$where_arr[]	=	"user_info.uid not in (select uid from pay_info where ".implode(" and ",$pay_where_arr).")";
			}
			if($crac>0)
			{
				$from	.=	" left join photo on user_info.uid=photo.uid";
				$where_arr[]	=	"photo.photo_tag=1 and photo.status=1";
			}
		}
		else
		{
			if($crac>0)
			{
				$from	=	"user_info left join photo on user_info.uid=photo.uid";
				$where_arr[]	=	"photo.photo_tag=1 and photo.status=1";
			}
			else
			{
				$from	=	"user_info";
			}
		}

		if($yanjipici	>	0)
		{
			$from	.=	',yanji_rela';
			$where_arr[]	=	"yanji_rela.uid=user_info.uid";
			$where_arr[]	=	"yanji_rela.yanjipid=".$yanjipici;
			if($huhao_change	>	0)
			{
				$yanjipici_det	=	$this->yanjipici_info->getPici($yanjipici);
				$where_arr[]	=	"user_info.huhao_modifytime>".$yanjipici_det['createtime'];
			}
			else
			{
				if($huhao_old	>	0)
				{
					$yanjipici_det	=	$this->yanjipici_info->getPici($yanjipici);
					$where_arr[]	=	"user_info.huhao!='' and user_info.huhao_modifytime<".$yanjipici_det['createtime'];
				}
			}
		}

		$select_arr	=	array();
		foreach($export_arr as $key=>$value)
		{
			if ($value	!=	''	&&	!is_array($value))
			{
				$select_arr[]	=	'user_info.'.$key;
			}
		}
		if($crac>0)
		{
			$select_arr[]	=	'photo.pid';
		}

		$sql	=	"select distinct user_info.uid,user_info.OpzhengBH,".implode(',',$select_arr)." from ".$from;
		if(count($where_arr)	>	0)
		{
			$sql	.=" where ".implode(' and ',$where_arr);
		}
		if($crac>0)
		{ 
			$sql	.=	' limit 2000';
			$this->uploadtool->deletedir($this->_photo_zip.'zip');
			unlink($this->_photo_zip.'zip.zip');
		}
		else
		{
			$sql	.=	' limit 20000';
		}
		//echo $sql;
//exit();
		$exportData	=	$this->user_info->export($sql);
		//print_r($exportData);
		$i	=	0;
		$fp	=	fopen(FILEROOT.'user_info.csv','w');
		$zip_file	=	array();
		//exec('rm -rf '.$this->_photo_zip.'zip/*');
		//exec('rm -rf '.$this->_photo_zip.'zip.zip');
		foreach($exportData as $key=>$value)
		{
			if($i	==	0)
			{
				$str_tmp	=	'';
				if($huanzheng>0)
				{
					$export_arr['no_exp_test']	=	'CRSA信息';
				}
				foreach($export_arr as $key1=>$value1)
				{
					if($value1!=	'')
					{
						if(is_array($value1))
						{
							$str_tmp	.=	iconv("UTF-8",'GBK//IGNORE',$value1[0]).",";
						}
						else
						{
							$str_tmp	.=	iconv("UTF-8",'GBK//IGNORE',$value1).",";
						}
						
					}
				}
				$str_tmp	.=	"\r\n";
				fputs($fp,$str_tmp);
				$i=1;
			}

			$str_tmp	=	'';
			foreach($export_arr as $key1=>$value1)
			{
				if($value1!=	'')
				{
					if($key1	==	'Oplev')
					{
						$value[$key1]	.=	' 类';
					}

					if($key1	==	'no_exp_test'	&&	$huanzheng>0)
					{
						$lv_int	=	$this->_global_lv_to_int[$value['Oplev']]>0?$this->_global_lv_to_int[$value['Oplev']]:1;
						$value[$key1]	=	'CRSA_'.$lv_int.'_'.$value['OpzhengBH'];
					}

					if($key1	==	'userbirthday' || $key1	==	'zhucetime')
					{
						$value[$key1]	=	date('Y.m.d',strtotime($value[$key1]));
					}


					if(is_array($value1))
					{
						$str_tmp	.=	iconv("UTF-8",'GBK//IGNORE',$value1[1]).",";
					}
					else
					{
						$str_tmp	.=	"\t".str_replace(',','，',iconv("UTF-8",'GBK//IGNORE',$value[$key1])).",";
						//$str_tmp	.=	iconv("UTF-8",'GBK//IGNORE',$value1).",";
					}
					
				}
			}

			//foreach($value as $key1=>$value1)
			//{
			//	$str_tmp	.=	"\t".str_replace(',','，',iconv("UTF-8",'GBK//IGNORE',$value1)).",";
			//}
			$str_tmp	.=	"\r\n";
			fputs($fp,$str_tmp);
			if($crac>0)
			{
				$dir	=	$this->uploadtool->build_photo_dir($value['pid']);
				$filename = $this->_photo_folder.$dir.$value['pid'].".jpg";
				copy($filename,$this->_photo_zip.'zip/'.$value['userIDcard'].'.jpg');
				$zip_file[]	=	$this->_photo_zip.'zip/'.$value['userIDcard'].'.jpg';
			}
		}
		fclose($fp);

		//$this->phpzip->createZip($this->_photo_zip.'zip', $this->_photo_zip.'zip.zip');   //只生成不自动下载   
		//$zip -> downloadZip("要压缩的文件夹目录地址", "压缩后的文件名.zip");　　//自动下载  
		if($crac>0)
		{
			$this->uploadtool->create_zip($zip_file, $this->_photo_zip.'zip.zip', true); 
			//exec('cd '.$this->_photo_zip.'; zip zip.zip zip/*');
		}
		$this->view->assign('crac' , $crac);
		$this->view->display('user_exportrun.html');
		//header('location:/static/file/user_info.csv');
		//exit();
	}

	public function budazhizhao( $uid ,$yanjipici_pid=0)
	{
		$uid	=	intval($uid);
		$yanjipici_pid	=	intval($yanjipici_pid);
		if($uid	<=	0)
		{
			$this->showMsg('参数错误');
		}
		$this->yanji_rela->change_pici($uid,$yanjipici_pid);
		if($yanjipici_pid	==	0)
		{
			$this->addlog('在最新的验机批次中补打执照',$uid);
		}
		else
		{
			$this->addlog('在验机批次id:'.$yanjipici_pid.' 中补打执照',$uid);
		}
		$this->showMsg('操作成功');
	}

	public function profession( $uid	=	0)
	{
		//print_r($uid);
		$profile	=	$this->user_info->getUser($uid);
		$yanjipici	=	$this->yanjipici_info->getAll();
		//print_r($yanjipici);
		//print_r($profile);

		$this->view->assign('profile' , $profile);
		$this->view->assign('yanjipici' , $yanjipici);
		$this->view->display('user_profession.html');
		
	}

	public function profession_run()
	{
		//print_r($uid);
		$uid	=	intval($this->input->post('uid'));
		$yanjipici	=	intval($this->input->post('yanjipici'));
		if($yanjipici	>	0	&&	$uid	>	0)
		{
			header('location:/user/budazhizhao/'.$uid.'/'.$yanjipici.'/');
			exit();
		}
		$profession_type	=	intval($this->input->post('profession_type'));

		$profile	=	$this->user_info->getUser($uid);

		$col_name	=	$this->_global_data['profession_edit'][$profession_type][1];
		$col_data	=	$this->input->post($col_name);
		if(!$col_name)
		{
			$this->showMsg('非法操作');
		}
		if($col_name	==	'huhao')
		{
			$col_data	=	strtoupper($col_data);
		}

		if($col_name	==	'huhao'	||	$col_name	==	'crac_id')
		{
			$test_profile	=	$this->user_info->getUserByCus( array($col_name=>$col_data) );
			if($test_profile)
			{
				$this->showMsg($this->_global_data['profession_edit'][$profession_type][0].'重复，请修改后提交');
			}
		}

		$para	=	array($col_name=>$col_data);
		//print_r($para);

		$this->addlog('修改'.$this->_global_data['profession_edit'][$profession_type][0].'('.$col_name.'),为:'.$col_data,$uid);
		//
		if($col_name	==	'huhao')
		{
			$para['huhao_modifytime']	=	time();
			if($profile['huhao_createtime']	<=	0)
			{
				$para['huhao_createtime']	=	time();
			}
		}

		$this->user_info->update( $para ,  array('uid' => $uid ));
		$this->showMsg('操作成功');

		//print_r($profile);

		//$this->view->assign('profile' , $profile);
		//$this->view->display('user_profession.html');
		
	}

	public function photodel( $pid )
	{
		$pid	=	intval($pid);
		$photoData	=	$this->photo->getPhoto($pid);
		if($photoData['photo_tag']	==	1)
		{
			$allPhoto	=	$this->photo->getAllPhoto($photoData['uid']);
			if(count($allPhoto)	>	0)
			{
				$this->photo->update( array('photo_tag'=>1) ,  array('pid' => $allPhoto[0]['pid'] ));
			}
		}
		$this->addlog('删除照片,id为:'.$pid,$photoData['uid']);
		$this->photo->update( array('status'=>0) ,  array('pid' => $pid ));
		header('location:/user/camera/'.$photoData['uid'].'/');
		exit();
	}

	public function camera( $uid	=	0)
	{
		//print_r($uid);
		/*$uid	=	intval($uid);
		$profile	=	$this->user_info->getUser($uid);
		$resData	=	$this->photo->getAllPhoto($uid);
		foreach($resData as $key=>$value)
		{
			$dir	=	$this->uploadtool->build_photo_dir($value['pid']);
			$filename = $this->_photo_url.$dir.$value['pid'].".jpg";
			$resData[$key]['photo_filename']	=	$filename;
		}
		$this->view->assign('resData' , $resData);
		$this->view->assign('profile' , $profile);
		$this->view->display('user_camera.html');*/
		$this->profile_show	=	3;
		$this->profile($uid);
	}

	public function setphoto( $pid	=	0)
	{
		$pid	=	intval($pid);
		$resData	=	$this->photo->getPhoto($pid);
		$this->photo->update( array('photo_tag'=>0) ,  array('uid' => $resData['uid'] ));
		$this->photo->update( array('photo_tag'=>1) ,  array('pid' => $pid ));
		$this->addlog('将图片设置为照片,id为:'.$pid,$resData['uid']);
		$this->showMsg('操作成功','/user/camera/'.$resData['uid'].'/');
	}

	public function upload( $uid	=	0)
	{
		//print_r($uid);
		$uid	=	intval($uid);

		$para	=	array('uid'=>$uid);
		$photoData	=	$this->photo->getAllPhoto($uid);
		if(count($photoData)	==	0)
		{
			$para['photo_tag']	=	1;
		}

		$pid	=	$this->photo->add($para);
		$dir	=	$this->uploadtool->build_photo_dir($pid);
		$this->uploadtool->mmkdir($this->_photo_folder.$dir);

		$filename = $this->_photo_folder.$dir.$pid.".jpg";
		//echo $filename;
		//exit();
		$this->addlog('拍摄照片,id为:'.$pid,$uid);
		$input = file_get_contents('php://input');
		if(md5($input) == '7d4df9cc423720b7f1f3d672b89362be'){
			exit;
		}
		$result = file_put_contents($filename, $input);
		if (!$result) {
			echo '{"error":1,"message":"文件目录不可写";}';
			exit;
		}

		echo '{"status":1,"message":"Success!","filename":"'.$filename.'"}';
	}

	public function fileupload( $uid	=	0)
	{
		//print_r($_FILES);
		if ($_FILES["uploadfile"]["error"]	>	0)
		{
			$this->showMsg('文件上传错误');
		}
		if (!((($_FILES["uploadfile"]["type"] == "image/gif")
		|| ($_FILES["uploadfile"]["type"] == "image/jpeg")
		|| ($_FILES["uploadfile"]["type"] == "image/pjpeg"))
		&& ($_FILES["uploadfile"]["size"] < 100001)))
		//if($_FILES['uploadfile']['size']	>	50001)
		{
			$this->showMsg('文件大小不可超过100k，只支持gif图片和jpg图片');
		}

		//exit();
		$uid	=	intval($uid);

		$para	=	array('uid'=>$uid);
		$photoData	=	$this->photo->getAllPhoto($uid);
		if(count($photoData)	==	0)
		{
			$para['photo_tag']	=	1;
		}

		$pid	=	$this->photo->add($para);

		$dir	=	$this->uploadtool->build_photo_dir($pid);
		$this->uploadtool->mmkdir($this->_photo_folder.$dir);
		$this->addlog('上传照片,id为:'.$pid,$uid);
		$filename = $this->_photo_folder.$dir.$pid.".jpg";
		//echo $filename;
		//exit();
		move_uploaded_file($_FILES["uploadfile"]["tmp_name"],$filename);
		$this->showMsg('文件上传成功','/user/camera/'.$uid.'/');
	}

	public function profile( $uid	=	0)
	{
		//print_r($uid);
		$profile	=	$this->user_info->getUser($uid);
		if($profile['type']	==	2)
		{
			$manager_profile	=	$this->user_info->getUser($profile['manager_uid']);
			$this->view->assign('manager_profile' , $manager_profile);
		}

		$yanji_rela_info	=	$this->yanji_rela->getAllByUid($uid);	//获取执照有效期
		$zhizhao_date	=	false;
		foreach($yanji_rela_info as $value)
		{
			$yanjipici_det	=	$this->yanjipici_info->getPici($value['yanjipid']);
			if($yanjipici_det['sub']	==	1)
			{
				$zhizhao_date	=	
					array('start_date'	=>	date('Y年m月d日',$yanjipici_det['sub_time']),
						'end_date'		=>	date('Y年m月d日',(strtotime((date('Y',$yanjipici_det['sub_time'])+3).date('-m-d',$yanjipici_det['sub_time']))-24*3600)));
				break;
			}
		}

		//print_r($profile);

		$resData	=	array();
        foreach($this->_global_pay_type as $key    =>  $value)
        {
            $resData[]  =   $this->pay_info->getAmountByType($key,$uid);
        }

		$photoData	=	$this->photo->getAllPhoto($uid);
		foreach($photoData as $key=>$value)
		{
			$dir	=	$this->uploadtool->build_photo_dir($value['pid']);
			$filename = $this->_photo_url.$dir.$value['pid'].".jpg";
			$photoData[$key]['photo_filename']	=	$filename;
		}

		$remark_list	=	$this->remark->getUnOpRemarkByUid($uid);
		if($remark_list)
		{
			$remark_list	=	true;
		}


		$this->view->assign('edit' , true);
		$this->view->assign('profile' , $profile);
		$this->view->assign('zhizhao_date' , $zhizhao_date);
		$this->view->assign('photoData' , $photoData);
		$this->view->assign('resData' , $resData);
		$this->view->assign('profile_show' , $this->profile_show);
		$this->view->assign('remark_list' , $remark_list);
		$this->view->display('user_profile.html');
		
	}


	public function cardno()
	{
		$this->view->display('user_cardno.html');
	}

	public function addgroup($uid)
	{
		$uid	=	intval($uid);
		if($uid	<=	0)
		{
			$this->showMsg('集体台负责人不存在','/user/');
		}
		$manager_profile	=	$this->user_info->getUser($uid);
		$this->view->assign('manager_profile' , $manager_profile);
		$this->view->assign('edit' , false);
		$this->view->display('user_addgroup.html');
	}

	public function savegroup()
	{
		$save_data	=	$this->input->post();
		$uid	=	intval($this->input->post('uid'));


		$username	=	$this->input->post('username');
		$userpinyin	=	$this->input->post('userpinyin');
		$manager_uid	=	intval($this->input->post('manager_uid'));
		$mobTel	=	$this->input->post('mobTel');
		$txAd	=	$this->input->post('txAd');
		$xzAd	=	$this->input->post('xzAd');
		$stAd	=	$this->input->post('stAd');
		$txpost	=	$this->input->post('txpost');
		$xzpost	=	$this->input->post('xzpost');
		$stpost	=	$this->input->post('stpost');
		$yyzz	=	$this->input->post('yyzz');
		$zzjg	=	$this->input->post('zzjg');
		
		if(
			$username	==	''	||
			$userpinyin	==	''	||
			$manager_uid	==	''	||
			$mobTel	==	''	||
			$txAd	==	''	||
			$xzAd	==	''	||
			$stAd	==	''	||
			$txpost	==	''	||
			$xzpost	==	''	||
			$stpost	==	''	||
			$yyzz	==	''	||
			$zzjg	==	''
		)
		{
			$this->showMsg('请填写全部必填项');
			exit();
		}

		//print_r($test_profile);


		if($uid	>	0)
		{
			unset($save_data['Oplev']);
			unset($save_data['membernum']);
			unset($save_data['pa']);
			unset($save_data['OpzhengBH']);
			unset($save_data['zhucetime']);
			unset($save_data['huhao']);
			$this->user_info->update( $save_data ,  array('uid' => $uid ));
			$this->addlog('编辑会员信息',$uid);
			$this->showMsg('保存成功');
		}
		else
		{
			$save_data['isyj']	=	0;
			$save_data['Oplev ']	=	'C';
			$uid_insert	=	$this->user_info->add($save_data);
			$this->addlog('会员注册',$uid_insert);
			$this->showMsg('用户创建成功','/user/');
		}
		
		//echo $sql;
	}

	public function add()
	{
		//$this->view->assign('profile' , array('uid'=>'','ID'=>'','username'=>'','userpinyin'=>'','usersex'=>'','userbirthday'=>'','userminzu'=>'','userwenhua'=>'','userIDcard'=>'','workTel'=>'','homeTel'=>'','mobTel'=>'','mail'=>'','txAd'=>'','txpost'=>'','xzAd'=>'','xzpost'=>'','stAd'=>'','stpost'=>'','company'=>'','zhiwu'=>'','workAd'=>'','workAdPost'=>'','zhucetime'=>'','zhuceAd'=>'','opquhao'=>'','membernum'=>'','clubname'=>'','zhuguanname'=>'','taitype'=>'','ziliaoBH'=>'','taiAd'=>'','Oplev'=>'','OpzhengBH'=>'','huhao'=>'','pa'=>'','devcount'=>'','devSCLMcount'=>'','devCZLMcount'=>'','devVcount'=>'','devHcount'=>'','cardno'=>'','IsJF'=>'','IsYJ'=>'','balance'=>'','pci'=>'','levOneTime'=>'','levTwoTime'=>'','levThreeTime'=>'','levFourTime'=>'','huhaoTime'=>'','IsPX'=>''));
		$this->view->assign('edit' , false);
		$this->view->display('user_add.html');
	}

	public function save()
	{
		$save_data	=	$this->input->post();
		$uid	=	intval($this->input->post('uid'));


		$username	=	$this->input->post('username');
		$userpinyin	=	$this->input->post('userpinyin');
		$usersex	=	$this->input->post('usersex');
		$userIDcard	=	$this->input->post('userIDcard');
		$userbirthday	=	$this->input->post('userbirthday');
		$mobTel	=	$this->input->post('mobTel');
		$txAd	=	$this->input->post('txAd');
		$xzAd	=	$this->input->post('xzAd');
		$stAd	=	$this->input->post('stAd');
		$txpost	=	$this->input->post('txpost');
		$xzpost	=	$this->input->post('xzpost');
		$stpost	=	$this->input->post('stpost');
		$userminzu	=	$this->input->post('userminzu');
		$area	=	$this->input->post('area');
		
		if(
			$username	==	''	||
			$userpinyin	==	''	||
			$usersex	==	''	||
			$userIDcard	==	''	||
			$userbirthday	==	''	||
			$mobTel	==	''	||
			$txAd	==	''	||
			$xzAd	==	''	||
			$stAd	==	''	||
			$txpost	==	''	||
			$xzpost	==	''	||
			$stpost	==	''	||
			$userminzu	==	''	||
			$area	==	''
		)
		{
			$this->showMsg('请填写全部必填项','','','','yes',true);
			exit();
		}

		//print_r($test_profile);


		if($uid	>	0)
		{
			$test_profile	=	$this->user_info->getUserByCard($userIDcard);
			if($test_profile	&&	$test_profile['uid']	!=	$uid)
			{
				//print_r($test_profile);
				$this->showMsg('身份证号重复，该会员已经存在');
			}
			unset($save_data['Oplev']);
			unset($save_data['membernum']);
			unset($save_data['pa']);
			unset($save_data['OpzhengBH']);
			unset($save_data['zhucetime']);
			unset($save_data['huhao']);
			$this->user_info->update( $save_data ,  array('uid' => $uid ));
			$this->addlog('编辑会员信息',$uid);
			$this->showMsg('保存成功');
		}
		else
		{
			//$sql	=	"insert into user_info ".$sql;
			$test_profile	=	$this->user_info->getUserByCard($userIDcard);
			if($test_profile)
			{
				$this->showMsg('身份证号重复，该会员已经存在');
			}
			$save_data['isyj']	=	0;
			$uid_insert	=	$this->user_info->add($save_data);
			$this->addlog('会员注册',$uid_insert);
			$this->showMsg('用户创建成功','/user/');
		}
		
		//echo $sql;
	}

	public function pay($uid=0)
	{
		$this->profile_show	=	2;
		$this->profile($uid);
	}

	public function paylist($uid)
	{
		$uid	=	intval($uid);
		$profile	=	$this->user_info->getUser($uid);
		$pay_list  =   $this->pay_info->getPayList($uid);


		$this->view->assign('profile' , $profile);
		$this->view->assign('pay_list' , $pay_list);
		$this->view->display('user_paylist.html');
	}

	public function payprint($payid)
	{
		//$payid	=	intval($payid);
		$payid_arr	=	explode('-',$payid);
		$pay_det_arr	=	array();
		$total_amount	=	0;
		foreach($payid_arr as $value)
		{
			$payid_tmp	=	intval($value);
			$pay_det	=	$this->pay_info->getPay($payid_tmp);
			if($pay_det['uid']	==	0)
			{
				$this->showMsg('付费信息错误');
			}
			$this->addlog('打印收据，金额：'.$pay_det['amount'],$pay_det['uid']);
			$profile	=	$this->user_info->getUser($pay_det['uid']);
			$total_amount	+=	$pay_det['amount'];
			$pay_det_arr[]	=	$pay_det;
		}

		$totla_cny	=	$this->strtoint->cny($total_amount);
		
		//$pay_det	=	$this->pay_info->getPay($payid);


		//print_r($profile);
		//print_r($pay_det);
		$this->view->assign('profile' , $profile);
		$this->view->assign('pay_det_arr' , $pay_det_arr);
		$this->view->assign('totla_cny' , $totla_cny);
		$this->view->assign('total_amount' , $total_amount);

		$this->view->display('user_payprint.html');
	}


	public function paydel($payid)
	{
		$payid	=	intval($payid);
		$pay_det	=	$this->pay_info->getPay($payid);
		$this->addlog('删除缴费数据，id：'.$payid,$pay_det['uid']);
		$this->pay_info->payDelete($payid);
		$this->showMsg('操作成功','/user/paylist/'.$pay_det['uid'].'/');
		//print_r($pay_det);
		//$this->pay_info->update( array('uid'=>(0-$pay_det['uid'])) ,  array('id' => $payid ));
	}

	public function pay_type($uid,$type)
	{
		$profile	=	$this->user_info->getUser($uid);
		if($type	==	'first')
		{
			if($profile['type']	==	2)
			{
				$this->showMsg('集体会员不能使用该功能');
			}
			$amount_info_0  =   $this->pay_amount->getAmountByType(0);
			$amount_info_1  =   $this->pay_amount->getAmountByType(1);
			$amount_info_2  =   $this->pay_amount->getAmountByType(2);
			$amount_info_6  =   $this->pay_amount->getAmountByType(6);
			$amount_info	=	array('amount'=>($amount_info_0['amount']+$amount_info_1['amount']+$amount_info_2['amount']+$amount_info_6['amount']));
			$pay_info  =   $this->pay_info->getAmountByType(0,$uid);
		}
		else
		{
			$amount_info  =   $this->pay_amount->getAmountByType($type);
			$pay_info  =   $this->pay_info->getAmountByType($type,$uid);
		}
		switch ($type) {
			case 0:
				if($pay_info)
				{
					$pay_info['year']	+=	1;
				}
				else
				{
					$pay_info['year']	=	date('Y');
				}
				break;
			case 4:
				if($pay_info)
				{
					$pay_info['year']	+=	1;
				}
				else
				{
					$pay_info['year']	=	date('Y');
				}
				break;
		}

		$this->view->assign('profile' , $profile);
		$this->view->assign('amount_info' , $amount_info);

		$this->view->assign('pay_info' , $pay_info);
		$this->view->assign('uid' , $uid);
		$this->view->assign('type' , $type);
		$this->view->display('user_paytype.html');
	}

	public function payrun()
	{
		$uid	=	intval($this->input->post('uid'));
		$type	=	$this->input->post('type');
		$count	=	intval($this->input->post('count'));
		$profile	=	$this->user_info->getUser($uid);

		if($type	==	'first')
		{
			if($profile['type']	==	2)
			{
				$this->showMsg('集体会员不能使用该功能');
			}
			$first_pay_type	=	array(0,1,2,6);
			$payid_arr	=	array();
			foreach($first_pay_type as $type_one)
			{
				$amount_info  =   $this->pay_amount->getAmountByType($type_one);
				$pay_info  =   $this->pay_info->getAmountByType($type_one,$uid);
				//print_r($pay_info);
				$para	=	array(
					'uid'	=>	$uid,
					'pay_type'	=>	$type_one,
					'amount'	=>	$amount_info['amount'],
					);
				switch ($type_one) {
					case 0:
						if($pay_info)
						{
							$pay_info['year']+=1;
						}
						else
						{
							$pay_info['year']	=	date('Y');
						}
						$para['year']	=	$pay_info['year'];

						break;
					default:
					break;
				}
				//print_r($para);
				$payid_arr[]	=	$this->pay_info->add($para);
				$this->addlog('收取费用，金额：'.$para['amount'],$uid);
			}
			$this->showMsg('缴费成功','/user/pay/'.$uid, '打印' ,'/user/payprint/'.implode('-',$payid_arr).'/','no');
		}
		else
		{
			$type	=	intval($type);
			$amount_info  =   $this->pay_amount->getAmountByType($type);
			$pay_info  =   $this->pay_info->getAmountByType($type,$uid);
			$profile	=	$this->user_info->getUser($uid);

			if($profile['type']	==	1)
			{
				$amount_tmp	=	$amount_info['amount'];
			}
			else
			{
				$amount_tmp	=	$amount_info['group_amount'];
			}

			//print_r($pay_info);
			$para	=	array(
				'uid'	=>	$uid,
				'pay_type'	=>	$type,
				'amount'	=>	$amount_tmp,
				);
			switch ($type) {
				case 0:
					if($pay_info)
					{
						$pay_info['year']+=1;
					}
					else
					{
						$pay_info['year']	=	date('Y');
					}
					$para['year']	=	$pay_info['year'];

					break;

				case 4:
					if($pay_info)
					{
						$pay_info['year']+=1;
					}
					else
					{
						$pay_info['year']	=	date('Y');
					}
					$para['year']	=	$pay_info['year'];

					break;
				case 1:
					$pay_info_tmp	=	$this->pay_info->getAmountByType(1,$uid);
					//print_r($pay_info_tmp);
					//exit();
					if($pay_info_tmp)
					{
						$this->showMsg('该用户已缴纳过培训费','/user/pay/'.$uid.'/');
					}
					else
					{

					}
					//exit();
					break;
				case 3:
					if($count	>	0)
					{
						$para['count']	=	$count;
						$para['amount']	=	$count*$amount_tmp;
						//$this->yanji_rela->change_pici($uid);
					}
					else
					{
						$this->showMsg('设备数量填写错误');
					}
					break;
				case 5:
					$pay_info  =   $this->pay_info->getAmountByType(0,$uid);
					/*if(!$pay_info	||	$pay_info['year']	<	date('Y'))
					{
						$this->showMsg('会员没有缴纳今年的会费');
					}*/
					//取消费用

					$this->yanji_rela->change_pici($uid);
					break;
					//$this->showMsg('换照/补招操作成功');
				case 6:
					$pay_info  =   $this->pay_info->getAmountByType(0,$uid);
					/*if(!$pay_info	||	$pay_info['year']	<	date('Y'))
					{
						$this->showMsg('会员没有缴纳今年的会费');
					}*/
					//取消费用

					$this->yanji_rela->change_pici($uid);
					$pici_all	=	$this->pici_info->getAll();
					$this->user_info->update(array('pci'=>$pici_all[0]['pcname']),array('uid'=>$uid));
					break;

				case 7:
					$this->user_info->add_testcharge($uid);
					break;
				case 8:
					$this->user_info->add_testBcharge($uid);
					break;
				case 9:
					$this->user_info->add_testCcharge($uid);
					break;
				default:
				break;
			}
			//print_r($para);
			$payid	=	$this->pay_info->add($para);
			$this->addlog('收取费用，金额：'.$para['amount'],$uid);
			$this->showMsg('缴费成功','/user/pay/'.$uid, '打印' ,'/user/payprint/'.$payid.'/','no');
		}

	}

	/*public function num()
	{
		//$resData	=	$this->pay_amount->showList();
		//print_r($resData);
		$resData	=	$this->pici_info->getAll();
		foreach($resData as $key=>$value)
		{
			$resData[$key]['pici_count']	=	$this->user_info->getCountByPici($value['pcname']);
			$resData[$key]['new_pici_count']	=	$this->user_info->getNewCountByPici($value['pcname']);
			
		}
		//print_r($resData);

		$this->view->assign('resData' , $resData);
		$this->view->display('user_num.html');
	}

	public function setnum($pid,$type)
	{
		$pid	=	intval($pid);
		$type	=	intval($type);

		$pici_det	=	$this->pici_info->getPici($pid);
		$pici_count	=	$this->user_info->getCountByPici($pici_det['pcname']);
		$new_pici_count	=	$this->user_info->getNewCountByPici($pici_det['pcname']);

		$this->view->assign('pici_det' , $pici_det);
		$this->view->assign('type' , $type);
		$this->view->assign('pici_count' , $pici_count);
		$this->view->assign('new_pici_count' , $new_pici_count);
		$this->view->display('user_setnum.html');
	}


	public function numrun()
	{
		$pid	=	intval($this->input->post('pid'));
		$type	=	intval($this->input->post('type'));
		$id_start	=	intval($this->input->post('id_start'));
		$id_end	=	intval($this->input->post('id_end'));

		$pici_det	=	$this->pici_info->getPici($pid);

		
		$sql	=	"select * from user_info where pci='".$pici_det['pcname']."' and change_pici=0";
		$data	=	$this->user_info->export($sql);

		$id	=	$id_start;
		foreach($data as $key=>$value)
		{
			switch ($type)
			{
				case '1':
					$para['pa']	=	'1100'.substr($pici_det['pcname'],0,4).$id;
					break;
				
				case '2':
					$para['crac_id']	=	$id;
					break;
			}
			$this->user_info->update( $para ,  array('uid' => $value['uid'] ));
			$id+=1;
			if($id	>	$id_end)
			{
				break;
			}
		}
		$this->showMsg('操作完成');
		//print_r($data);
	}*/

	public function zhizhao()
	{
		//$resData	=	$this->pay_amount->showList();
		//print_r($resData);
		$resData	=	$this->yanjipici_info->getAll();
		foreach($resData as $key=>$value)
		{
			$resData[$key]['pici_count']	=	$this->yanji_rela->getCountByPid($value['pid']);
		}
		//print_r($resData);
		$this->view->assign('resData' , $resData);
		$this->view->display('user_zhizhao.html');
	}

	public function zhizhaoprint($pid,$exp_type=1)
	{
		$exp_type	=	intval($exp_type);
		$this->load->library('pinyin');
		$this->load->model('device_info' , 'device_info');
		$pid	=	intval($pid);
		$yanjipici_det	=	$this->yanjipici_info->getPici($pid);

		if($yanjipici_det['sub']	==	0)
		{
			$this->showMsg('这个批次还没有提交');
		}

		//取出全部设备
		$sql	=	"select device_info.devxh,device_info.devchuchanghao,device_info.uid from device_info left join yanji_rela on device_info.uid=yanji_rela.uid where yanji_rela.yanjipid=".$pid." and device_info.isyj=1 and device_info.status=1 order by device_info.uid asc";
		$deviceData	=	$this->device_info->export($sql);
		$deviceList	=	array();
		foreach($deviceData as $key=>$value)
		{
			$deviceList[$value['uid']][]	=	$value;
		}

		//取出全部用户
		$sql	=	"select distinct(user_info.uid),user_info.username,user_info.pa,user_info.userIDcard,user_info.huhao,user_info.Oplev,user_info.type,user_info.manager_uid,user_info.zzjg,user_info.stAd from user_info left join yanji_rela on user_info.uid=yanji_rela.uid where user_info.type=".$exp_type." and yanji_rela.yanjipid=".$pid." order by userpinyin asc";
		//echo $sql;
		$exportData	=	$this->user_info->export($sql);
		foreach($exportData as $key=>$value)
		{
			$exportData[$key]['userpinyin']	=	strtoupper($this->pinyin->to_pinyin($exportData[$key]['username'],'utf-8'));
			//$device_list_tmp	=	$this->device_info->getDeviceByUid($exportData[$key]['uid']);
			if(array_key_exists($exportData[$key]['uid'],$deviceList))
			{
				$device_list_tmp	=	$deviceList[$exportData[$key]['uid']];
				foreach($device_list_tmp as $key1=>$value1)
				{
					$exportData[$key]['device_list'][floor($key1/12)][]	=	$value1;
				}

			}
			else
			{
				$exportData[$key]['device_list']=false;
			}
			if($value['type']	==	2)
			{
				$exportData[$key]['manager_profile']	=	$this->user_info->getUser($value['manager_uid']);
			}
		}

		//print_r($exportData);
		$this->zhizhao_data	=	array(
			'start_date'	=>	date('Y年m月d日',$yanjipici_det['sub_time']),
			'end_date'		=>	date('Y年m月d日',(strtotime((date('Y',$yanjipici_det['sub_time'])+5).date('-m-d',$yanjipici_det['sub_time']))-24*3600)),
			'exportData'	=>	$exportData,
			);
		if($exp_type	==	1)
		{
			$this->addlog($this->_user_type[$exp_type].'打印个人执照，批次：'.$yanjipici_det['pcname']);
			$this->zhizhao2pdf();
			//$this->view->display('user_zhizhaoprint.html');
		}
		else
		{
			$this->addlog($this->_user_type[$exp_type].'打印集体执照，批次：'.$yanjipici_det['pcname']);
			$this->zhizhao2pdfg();
			//$this->view->assign('start_date' , date('Y年m月d日'));
			//$this->view->assign('end_date' , date('Y年m月d日',(strtotime((date('Y')+5).date('-m-d'))-24*3600)));
			//$this->view->assign('exportData' , $exportData);
			//$this->view->display('user_zhizhaoprintg.html');
		}
	}

	public function onepage(&$pdf,$pos,$oneuser,$devicelist=false)
	{
		$cont_pos_x	=	82;	//基本信息左便宜量
		$cont_pos_y	=	5;	//基本信息高偏移量

		$title_w	=	20;	//基本信息标题栏宽度
		$title_h	=	7;	//基本信息高度

		$con_w	=	45;		//基本信息内容栏宽度
		$con_str_w	=	25;	//基本信息栏文字宽度，用户自动换行
		$title_str_w	=	1;	//基本信息标题栏文字宽度，用户自动换行

		$one_h	=	100;	//单个执照高度

		$dev_pos_x	=	157;	//设备栏左便宜量
		$dev_pos_y	=	$cont_pos_y;	//设备栏左便宜量

		$dev_l_w	=	25;		//设备左栏宽度
		$dev_r_w	=	25;		//设备右栏宽度
		$dev_h	=	6;			//设备栏高度

		$pdf->SetXY(10,$pos*$one_h+30);
		$pdf->Cell(5,5,iconv('utf-8', 'GBK', '执照编号：'.$oneuser['pa']));

		$i=0;

		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($title_w,$title_h,iconv('utf-8', 'GBK', '设台人员'),1);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($con_w,$title_h,iconv('utf-8', 'GBK', $oneuser['username']),1);
		++$i;

		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($title_w,$title_h,iconv('utf-8', 'GBK', '证件号码'),1);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->SetFont('Courier','',10); 
		$pdf->Cell($con_w,$title_h,iconv('utf-8', 'GBK', $oneuser['userIDcard']),1);
		$pdf->SetFont('GB','',8);
		++$i;

		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($title_w,$title_h,iconv('utf-8', 'GBK', '电台呼号'),1);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->SetFont('Courier','',10); 
		$pdf->Cell($con_w,$title_h,iconv('utf-8', 'GBK', $oneuser['huhao']),1);
		$pdf->SetFont('GB','',8);
		++$i;

		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($title_w,$title_h,iconv('utf-8', 'GBK', '电台类别'),1);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($con_w,$title_h,iconv('utf-8', 'GBK', $oneuser['Oplev'].'类'),1);
		++$i;

		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($title_w,$title_h,iconv('utf-8', 'GBK', '使用区域'),1);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($con_w,$title_h,iconv('utf-8', 'GBK', '全国'),1);
		++$i;

		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($title_w,$title_h*3,iconv('utf-8', 'GBK', '有效期'),1);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($con_w,$title_h*3,'',1,1);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($con_w,$title_h,iconv('utf-8', 'GBK', $this->zhizhao_data['start_date']),0);
		++$i;
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($con_w,$title_h,iconv('utf-8', 'GBK', "至"),0);
		++$i;
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($con_w,$title_h,iconv('utf-8', 'GBK', $this->zhizhao_data['end_date']),0);
		++$i;

		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($title_w,$title_h*3,'',1);
		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->Cell($title_w,$title_h,iconv('utf-8', 'GBK', '台站地址'),0);
		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*($i+1));
		$pdf->Cell($title_w,$title_h,iconv('utf-8', 'GBK', '或车牌号'),0);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->MultiCell($con_w,$title_h*3,'',1);
		$pdf->setXY($cont_pos_x+$title_w,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->MultiCell($con_str_w,$title_h-2,iconv('utf-8', 'GBK', $oneuser['stAd']),0);
		//$pdf->MultiCell($con_w,$title_h,$oneuser['stAd'],1);
		$i+=3;

		$pdf->setXY($cont_pos_x,$pos*$one_h+$cont_pos_y+$title_h*$i);
		$pdf->MultiCell(100,$title_h,iconv('utf-8', 'GBK', '核发单位：北京市无线电管理局'),0);

		$i=0;
		$pdf->setXY($dev_pos_x,$pos*$one_h+$dev_pos_y+$dev_h*$i);
		$pdf->Cell($dev_l_w,$dev_h,iconv('utf-8', 'GBK', '设备型号'),1);
		$pdf->setXY($dev_pos_x+$dev_l_w,$pos*$one_h+$dev_pos_y+$dev_h*$i);
		$pdf->Cell($dev_r_w,$dev_h,iconv('utf-8', 'GBK', '出厂号'),1);
		++$i;

		if($devicelist)
		{
			$pdf->SetFont('Courier','',10); 
			foreach($devicelist as $key=>$onedev)
			{
				if($onedev['devxh']	==	'自制')
				{
					$pdf->SetFont('GB','',8);
				}
				$pdf->setXY($dev_pos_x,$pos*$one_h+$dev_pos_y+$dev_h*$i);
				$pdf->Cell($dev_l_w,$dev_h,iconv('utf-8', 'GBK', $onedev['devxh']),1);
				$pdf->setXY($dev_pos_x+$dev_l_w,$pos*$one_h+$dev_pos_y+$dev_h*$i);
				$pdf->Cell($dev_r_w,$dev_h,iconv('utf-8', 'GBK', $onedev['devchuchanghao']),1);
				if($onedev['devxh']	==	'自制')
				{
					$pdf->SetFont('Courier','',10); 
				}
				++$i;
			}
			$pdf->SetFont('GB','',8);

		}
		else
		{
			$pdf->setXY($dev_pos_x,$pos*$one_h+$dev_pos_y+$dev_h*$i);
			$pdf->Cell($dev_l_w,$dev_h,' ',1);
			$pdf->setXY($dev_pos_x+$dev_l_w,$pos*$one_h+$dev_pos_y+$dev_h*$i);
			$pdf->Cell($dev_r_w,$dev_h,' ',1);
			++$i;
		}
		
	}

	public function zhizhao2pdf()
	{
		define('FPDF_FONTPATH','libs/fpdf/font/');
		require './libs/fpdf/chinese.php';
		//require './libs/fpdf/fpdf.php';

		//Instanciation of inherited class 
		$pdf=new PDF_Chinese('P', 'mm', 'A4');
		//$pdf=new FPDF('P', 'mm', 'A4');
		$pdf->SetMargins(0,0);
		$pdf->SetAutoPageBreak(false, 0);
		$pdf->AddGBFont();
		$pdf->open();
		$pdf->SetFont('GB','',8);
		$pdf->SetTextColor(0,0,0);

		$zhizhao_count	=	0;
		foreach($this->zhizhao_data['exportData'] as $key=>$oneuser)
		{
			if(is_array($oneuser['device_list']))
			{
				foreach($oneuser['device_list'] as $key1=>$devicelist)
				{
					if($zhizhao_count	==	0)
					{
						$pdf->AddPage();
						/*$pdf->SetXY(0,0);
						$pdf->Cell(153,297,' ',1);
						$pdf->SetXY(0,0);
						$pdf->Cell(153,100,' ',1);
						$pdf->SetXY(0,100);
						$pdf->Cell(153,100,' ',1);*/
						$pdf->SetXY(200,292);
						$pdf->Cell(5,5,$pdf->PageNo(),0);

					}
					$this->onepage($pdf,$zhizhao_count,$oneuser,$devicelist);
					$zhizhao_count++;
					if($zhizhao_count	==	3)	$zhizhao_count=0;
				}
			}
			else
			{
				if($zhizhao_count	==	0)
				{
					$pdf->AddPage();
					$pdf->SetXY(200,292);
					$pdf->Cell(5,5,$pdf->PageNo(),0);
				}
				$this->onepage($pdf,$zhizhao_count,$oneuser);
				$zhizhao_count++;
				if($zhizhao_count	==	3)	$zhizhao_count=0;
			}

		}
		$pdf->Output();
	}

	public function zhizhao2pdfg()
	{
		define('FPDF_FONTPATH','libs/fpdf/font/');
		require './libs/fpdf/chinese.php';
		//require './libs/fpdf/fpdf.php';

		//Instanciation of inherited class 
		$pdf=new PDF_Chinese('P', 'mm', 'A4');
		//$pdf=new FPDF('P', 'mm', 'A4');
		$pdf->SetMargins(0,0);
		$pdf->SetAutoPageBreak(false, 0);
		$pdf->AddGBFont();
		$pdf->open();
		$pdf->SetFont('GB','',8);
		$pdf->SetTextColor(0,0,0);

		$a4width	=	210;
		$cont_pos_x	=	10;	//基本信息左便宜量
		$cont_pos_y	=	35;	//基本信息高偏移量

		$general_h	=	7;	//每行高度
		$title_l_w	=	20;	//左侧标题
		$title_r_w	=	30;	//右侧标题
		$con_l_w	=	80;		//左侧内容
		$con_r_w	=	60;		//右侧内容

		$dev_l_w	=	65;	//设备左宽
		$dev_c_w	=	75;	//设备中宽
		$dev_r_w	=	50;		//设备右宽

		foreach($this->zhizhao_data['exportData'] as $key=>$oneuser)
		{
			$pdf->AddPage();
			$pdf->SetXY(200,292);
			$pdf->Cell(5,5,$pdf->PageNo(),0);
			$i	=	0;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell(($a4width-$cont_pos_x*3),$general_h,iconv('utf-8', 'GBK', '根据《中华人民共和国无线电管理条例》和国家相关规定，办法本执照，准予安装和使用下述无线电设备'),0,'C');
			++$i;

			$pdf->setXY($cont_pos_x+10,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell(($a4width-$cont_pos_x*3),$general_h,iconv('utf-8', 'GBK', '执照编号：'.$oneuser['pa'].'　　　　　　　　　　　　有效期：'.$this->zhizhao_data['start_date'].'　至　'.$this->zhizhao_data['end_date']),0);
			++$i;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_l_w,$general_h,iconv('utf-8', 'GBK', '设台单位'),1);
			$pdf->setXY($cont_pos_x+$title_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($con_l_w,$general_h,iconv('utf-8', 'GBK', $oneuser['username']),1);	
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_r_w,$general_h,iconv('utf-8', 'GBK', '单位机构代码'),1);	
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w+$title_r_w,$cont_pos_y+$general_h*$i);
			$pdf->SetFont('Courier','',10); 
			$pdf->MultiCell($con_r_w,$general_h,iconv('utf-8', 'GBK', $oneuser['zzjg']),1);
			$pdf->SetFont('GB','',8);
			++$i;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_l_w,$general_h,iconv('utf-8', 'GBK', '电台负责人'),1);
			$pdf->setXY($cont_pos_x+$title_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($con_l_w,$general_h,iconv('utf-8', 'GBK', $oneuser['manager_profile']['username']),1);
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_r_w,$general_h,iconv('utf-8', 'GBK', '身份证件号码'),1);
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w+$title_r_w,$cont_pos_y+$general_h*$i);
			$pdf->SetFont('Courier','',10); 
			$pdf->MultiCell($con_r_w,$general_h,iconv('utf-8', 'GBK', $oneuser['manager_profile']['userIDcard']),1);
			$pdf->SetFont('GB','',8);
			++$i;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_l_w,$general_h,iconv('utf-8', 'GBK', '技术负责人'),1);
			$pdf->setXY($cont_pos_x+$title_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($con_l_w,$general_h,iconv('utf-8', 'GBK', $oneuser['manager_profile']['username']),1);			
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_r_w,$general_h,iconv('utf-8', 'GBK', '身份证件号码'),1);			
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w+$title_r_w,$cont_pos_y+$general_h*$i);
			$pdf->SetFont('Courier','',10); 
			$pdf->MultiCell($con_r_w,$general_h,iconv('utf-8', 'GBK', $oneuser['manager_profile']['userIDcard']),1);			
			$pdf->SetFont('GB','',8);
			++$i;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_l_w,$general_h,iconv('utf-8', 'GBK', '电台呼号'),1);
			$pdf->setXY($cont_pos_x+$title_l_w,$cont_pos_y+$general_h*$i);
			$pdf->SetFont('Courier','',10); 
			$pdf->MultiCell($con_l_w,$general_h,iconv('utf-8', 'GBK', $oneuser['huhao']),1);			
			$pdf->SetFont('GB','',8);
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_r_w,$general_h,iconv('utf-8', 'GBK', '使用区域'),1);			
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w+$title_r_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($con_r_w,$general_h,iconv('utf-8', 'GBK', '全国'),1);			
			++$i;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_l_w,$general_h,iconv('utf-8', 'GBK', '电台类别'),1);
			$pdf->setXY($cont_pos_x+$title_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($con_l_w,$general_h,iconv('utf-8', 'GBK', $oneuser['Oplev']),1);			
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_r_w,$general_h,'',1);			
			$pdf->setXY($cont_pos_x+$title_l_w+$con_l_w+$title_r_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($con_r_w,$general_h,'',1);			
			++$i;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_l_w,$general_h*3,iconv('utf-8', 'GBK', ''),1);
			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_l_w-5,$general_h,iconv('utf-8', 'GBK', '台站地址或车牌号'));
			$pdf->setXY($cont_pos_x+$title_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($con_l_w+$title_r_w+$con_r_w,$general_h*3,'',1);
			$pdf->setXY($cont_pos_x+$title_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($con_l_w+$title_r_w+$con_r_w,$general_h,iconv('utf-8', 'GBK', $oneuser['manager_profile']['stAd']));
			$i+=3;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($title_l_w+$con_l_w+$title_r_w+$con_r_w,$general_h,iconv('utf-8', 'GBK', '业余电台无线电发射设备'),'1','C');
			++$i;

			$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($dev_l_w,$general_h,iconv('utf-8', 'GBK', '发信设备型号'),1);
			$pdf->setXY($cont_pos_x+$dev_l_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($dev_c_w,$general_h,iconv('utf-8', 'GBK', '设备出厂号'),1);			
			$pdf->setXY($cont_pos_x+$dev_l_w+$dev_c_w,$cont_pos_y+$general_h*$i);
			$pdf->MultiCell($dev_r_w,$general_h,iconv('utf-8', 'GBK', '备注'),1);				
			++$i;

			if($oneuser['device_list'])
			{
				$pdf->SetFont('Courier','',10); 
				foreach($oneuser['device_list'] as $key1=>$dev_list)
				{
					foreach($dev_list as $key2=>$onedev)
					{
						$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
						$pdf->MultiCell($dev_l_w,$general_h,iconv('utf-8', 'GBK', $onedev['devxh']),1);
						$pdf->setXY($cont_pos_x+$dev_l_w,$cont_pos_y+$general_h*$i);
						$pdf->MultiCell($dev_c_w,$general_h,iconv('utf-8', 'GBK', $onedev['devchuchanghao']),1);			
						$pdf->setXY($cont_pos_x+$dev_l_w+$dev_c_w,$cont_pos_y+$general_h*$i);
						$pdf->MultiCell($dev_r_w,$general_h,'',1);				
						++$i;
					}
				}
				$pdf->SetFont('GB','',8);
			}
			else
			{
				$pdf->setXY($cont_pos_x,$cont_pos_y+$general_h*$i);
				$pdf->MultiCell($dev_l_w,$general_h,'',1);
				$pdf->setXY($cont_pos_x+$dev_l_w,$cont_pos_y+$general_h*$i);
				$pdf->MultiCell($dev_c_w,$general_h,'',1);			
				$pdf->setXY($cont_pos_x+$dev_l_w+$dev_c_w,$cont_pos_y+$general_h*$i);
				$pdf->MultiCell($dev_r_w,$general_h,'',1);				
				++$i;
			}
		}




/*



	<li style="width:190px;">发信设备型号</li>
	<li style="width:320px;">设备出厂号</li>
	<li style="width:190px;">备注</li>

		{{foreach from=$value.device_list key=key1 item=value1}}
		{{foreach from=$value1 key=key2 item=value2}}
		<li style="width:190px;">{{$value2.devxh}}</li>
		<li style="width:320px;">{{$value2.devchuchanghao}}</li>
		<li style="width:190px;"></li>
		{{/foreach}}
		{{/foreach}}
</div>
{{/foreach}}*/

		$pdf->Output();
	}

	public function huhao()
	{
		//$resData	=	$this->pay_amount->showList();
		//print_r($resData);
		$resData	=	$this->yanjipici_info->getAll();
		foreach($resData as $key=>$value)
		{
			$resData[$key]['pici_count']	=	$this->yanji_rela->getCountByPid($value['pid']);
		}
		//print_r($resData);

		$this->view->assign('resData' , $resData);
		$this->view->display('user_huhao.html');
	}

	public function sethuhao($pid)
	{
		$pid	=	intval($pid);

		$this->view->assign('pid' , $pid);
		$this->view->display('user_sethuhao.html');
	}

	public function huhaorun()
	{
		$pid	=	intval($this->input->post('pid'));
		$start_tag	=	$this->input->post('start_tag');
		$start_cont	=	$this->input->post('start_cont');

		if(strlen($start_tag)	!=	1	||	strlen($start_cont)	!=	3)
		{
			$this->showMsg('呼号启示位输入错误');
		}

		$yanjipici_det	=	$this->yanjipici_info->getPici($pid);
		if($yanjipici_det['done']	==	1)
		{
			$this->showMsg('这个批次已经生成过呼号了');
		}

		$para	=	array('done'=>1);
		$this->yanjipici_info->update( $para ,  array('pid' => $pid ));

		$sql	=	"select distinct(user_info.uid) from user_info left join yanji_rela on user_info.uid=yanji_rela.uid where yanji_rela.yanjipid=".$pid." and user_info.huhao='' order by uid asc";
		$exportData	=	$this->user_info->export($sql);
		//print_r($exportData);
		$cHuhaoInt	=	$this->strtoint->toint($start_tag.$start_cont);
		$now_time	=	time();
		foreach($exportData as $value)
		{
			$cHuhao	=	$this->strtoint->tostr($cHuhaoInt);
			$cHuhao	=	'B'.substr($cHuhao,0,1).'1'.substr($cHuhao,-3);
			$this->user_info->update( array('huhao'=>strtoupper($cHuhao),'huhao_createtime'=>$now_time,'huhao_modifytime'=>$now_time) ,  array('uid' => $value['uid'] ));
			$cHuhaoInt++;
		}
		//print_r($exportData);
		$this->addlog('批量分配呼号，批次：'.$yanjipici_det['pcname']);

		//echo $cHuhaoInt;
		//$cHuhaoInt	=	$this->strtoint->toint('Azzz');
		//echo $cHuhaoInt;
		//echo $this->strtoint->tostr($cHuhaoInt);
		$this->showMsg('操作完成');
		//$this->view->assign('pid' , $pid);
		//$this->view->display('user_sethuhao.html');
	}

	public function list_custom($type,$search,$page)
	{
		$this->listcustom	=	$type;
		$this->view->assign('custom' , $type);
		$this->index($search,$page);
	}

	/*public function buzhao($uid)
	{
		$uid	=	intval($uid);
		$pay_info  =   $this->pay_info->getAmountByType(0,$uid);
		if(!$pay_info	||	$pay_info['year']	<	date('Y'))
		{
			$this->showMsg('会员没有缴纳今年的会费');
		}
		//print_r($pay_info);
		$profile	=	$this->user_info->getUser($uid);
		$this->view->assign('profile' , $profile);
		$this->view->display('user_buzhao.html');
	}

	public function buzhaorun($uid)
	{
		$uid	=	intval($uid);
		$pay_info  =   $this->pay_info->getAmountByType(0,$uid);
		if(!$pay_info	||	$pay_info['year']	<	date('Y'))
		{
			$this->showMsg('会员没有缴纳今年的会费');
		}

		$this->yanji_rela->change_pici($uid);
		$this->showMsg('换照/补招操作成功');
	}*/

	public function cardpass()
	{
		$cardno	=	$this->input->post('cardno');
		if($cardno	!=	'')
		{
			$profile	=	$this->user_info->getUserByCus( array('cardno'=>$cardno) );
			if($profile['uid']	>	0)
			{
				$this->testpass($profile['uid'],1);
			}
			else
			{
				$this->showMsg('卡号错误');
			}
			$this->view->assign('profile' , $profile);
		}
		$this->view->assign('cardno' , $cardno);
		$this->view->display('user_cardpass.html');
	}

	public function pass($uid,$pass)
	{
		$this->testpass($uid,$pass);
		//$this->showMsg('操作成功');
		header('location:/user/list100/');
	}

	public function passb($uid,$pass)
	{
		$this->testpassb($uid,$pass);
		//$this->showMsg('操作成功');
		header('location:/user/list104/');
	}

	public function passc($uid,$pass)
	{
		$this->testpassc($uid,$pass);
		//$this->showMsg('操作成功');
		header('location:/user/list105/');
	}

	public function testpass($uid,$pass)
	{
		$uid	=	intval($uid);
		$pass	=	intval($pass);
		if($pass	!=	1)
		{
			$pass	=	0;
		}
		$profile	=	$this->user_info->getUser($uid);

		/*if((($profile['test_charge']+1)*$this->_global_test_times['A'])	<=	$profile['test_times'])
		{
			$this->showMsg('会员没有缴纳考试/补考费');
			exit();
		}*/
		//取消考试费

		if($profile['test_pass']	==	1)
		{
			$this->showMsg('会员已经通过考试');
			exit();
		}

		$para	=	array('test_pass'=>$pass,'pass_time'=>time());
		if($pass	==	1)
		{
			$pass_str	=	'通过';
			$pici_all	=	$this->pici_info->getAll();
			$para['pci']	=	$pici_all[0]['pcname'];
		}
		else
		{
			$pass_str	=	'不通过';
		}
		$this->addlog('会员考试'.$pass_str,$uid);

		$this->user_info->update( $para ,  array('uid' => $uid ));	
		$this->user_info->add_test($uid);	//增加一次考试次数
	}

	public function testpassb($uid,$pass)
	{
		$uid	=	intval($uid);
		$pass	=	intval($pass);
		if($pass	!=	1)
		{
			$pass	=	0;
		}
		$profile	=	$this->user_info->getUser($uid);

		/*if((($profile['testb_charge'])*$this->_global_test_times['B'])	<=	$profile['testb_times'])
		{
			$this->showMsg('会员没有缴纳考试/补考费');
			exit();
		}*/
		//取消考试费

		if($profile['Oplev']	==	'B')
		{
			$this->showMsg('会员已经通过考试');
			exit();
		}
		
		if($pass	==	1)
		{
			$pass_str	=	'通过';
			$para	=	array('Oplev'=>'B','levelbtime'=>time());
			$pici_all	=	$this->pici_info->getAll();
			$para['pci']	=	$pici_all[0]['pcname'];
			$this->user_info->update( $para ,  array('uid' => $uid ));
		}
		else
		{
			$pass_str	=	'不通过';
		}
		$this->addlog('B级考试'.$pass_str,$uid);

		$this->user_info->add_testb($uid);	//增加一次考试次数
	}

	public function testpassc($uid,$pass)
	{
		$uid	=	intval($uid);
		$pass	=	intval($pass);
		if($pass	!=	1)
		{
			$pass	=	0;
		}
		$profile	=	$this->user_info->getUser($uid);

		/*if((($profile['testc_charge'])*$this->_global_test_times['C'])	<=	$profile['testc_times'])
		{
			$this->showMsg('会员没有缴纳考试/补考费');
			exit();
		}*/
		//取消考试费

		if($profile['Oplev']	==	'C')
		{
			$this->showMsg('会员已经通过考试');
			exit();
		}
		
		if($pass	==	1)
		{
			$pass_str	=	'通过';
			$para	=	array('Oplev'=>'C','levelctime'=>time());
			$pici_all	=	$this->pici_info->getAll();
			$para['pci']	=	$pici_all[0]['pcname'];
			$this->user_info->update( $para ,  array('uid' => $uid ));
		}
		else
		{
			$pass_str	=	'不通过';
		}
		$this->addlog('C级考试'.$pass_str,$uid);

		$this->user_info->add_testc($uid);	//增加一次考试次数
	}

	public function newpici( $uid )
	{
		$pici_all	=	$this->pici_info->getAll();
		$para	=	array();
		$para['pci']	=	$pici_all[0]['pcname'];
		$this->addlog('修改批次为：'.$pici_all[0]['pcname'],$uid);
		$this->user_info->update( $para ,  array('uid' => $uid ));
		$this->showMsg('批次修改完毕','/user/profile/'.$uid.'/');
	}

	public function huanzheng( $uid )
	{
		$pici_info	=	$this->pici_info->getNewHZPici();
		//print_r($pici_info);
		$para	=	array();
		$para['pci']	=	$pici_info['pcname'];
		$this->addlog('修改批次为：'.$pici_info['pcname'],$uid);
		$this->user_info->update( $para ,  array('uid' => $uid ));
		$this->showMsg('批次修改完毕','/user/profile/'.$uid.'/');
	}


	public function addremark($uid)
	{
		$uid	=	intval($uid);
		$profile	=	$this->user_info->getUser($uid);

		$remark_list	=	$this->remark->getRemarkByUid($uid);

		$this->view->assign('profile' , $profile);
		$this->view->assign('remark_list' , $remark_list);
		$this->view->display('user_addremark.html');
	}

	public function saveremark()
	{
		$uid	=	$this->input->post('uid');
		$remark_text	=	$this->input->post('remark_text');
		$this->remark->add(array('uid'=>$uid,'remark_text'=>$remark_text,'operate_text'=>''));
		$this->addlog('添加待处理问题',$uid);
		$this->showMsg('添加成功','/user/profile/'.$uid.'/');
	}

	public function remarklist($status = 0 , $page = 1)
	{
		//$admin_info	=	$this->session->all_userdata();
		//print_r($admin_info);
		//
		$page = intval( $page ) > 0 ? intval( $page ):1;
		$status = intval( $status );

		$where_arr	=	array();
		$where_arr[]	=	"`status`=".$status;

		$para['where']	=	implode(' and ',$where_arr);
		$para['page'] = $page;
		$para['perPage'] = PER_PAGE;
		$para['order'] = ' `id` DESC ';
		//print_r($para);
		//echo urlencode($search);
		$resData = $this->remark->queryList( $para  , FALSE );
		//print_r($resData);
		foreach($resData['list'] as $key=>$value)
		{
			$resData['list'][$key]['profile']	=	$this->user_info->getUser($value['uid']);
		}

		//print_r($resData);
		$this->view->assign('pageURL' , '/'.$this->getClass().'/'.$this->getMethod().'/'.$status.'/');
		$this->view->assign('status' , $status);
		$this->view->assign('resData' , $resData);
		
		$this->view->display('user_remarklist.html');
	}

	public function remark($id)
	{
		$id	=	intval($id);
		$remark_info	=	$this->remark->getRemark($id);
		$profile	=	$this->user_info->getUser($remark_info['uid']);

		$this->view->assign('profile' , $profile);
		$this->view->assign('remark_info' , $remark_info);
		$this->view->display('user_remark.html');
	}

	public function operateremark()
	{
		$id	=	$this->input->post('id');
		$operate_text	=	$this->input->post('operate_text');

		$remark_info	=	$this->remark->getRemark($id);

		$this->remark->update(array('operate_text'=>$operate_text,'status'=>1),array('id'=>$id));
		$this->addlog('解决待处理问题',$remark_info['uid']);
		$this->showMsg('处理成功','/user/remarklist/');
	}

	public function yanjideluser($pid,$uid)
	{
		$this->yanji_rela->delete(array('yanjipid'=>$pid,'uid'=>$uid));
		$this->addlog('从id为:'.$pid.' 的验机批次中删除用户',$uid);
		$this->showMsg('操作成功','/user/list102/'.$pid.'/');
	}

	public function qiandao($uid)
	{
		$this->user_info->update( array('qiandao'=>date('Y-m-d')) ,  array('uid' => $uid ));
		$this->addlog('用户签到，日期为：'.date('Y-m-d'),$uid);
		$this->showMsg('操作成功','/user/list103/');
	}

	public function list0( $search = 'default' , $page = 1)
	{
		$this->list_custom(0,$search,$page);
	}

	public function list1( $search = 'default' , $page = 1)
	{
		$this->list_custom(1,$search,$page);
	}

	public function list2( $search = 'default' , $page = 1)
	{
		$this->list_custom(2,$search,$page);
	}

	public function list3( $search = 'default' , $page = 1)
	{
		$this->list_custom(3,$search,$page);
	}

	public function list4( $search = 'default' , $page = 1)
	{
		$this->list_custom(4,$search,$page);
	}

	public function list5( $search = 'default' , $page = 1)
	{
		$this->list_custom(5,$search,$page);
	}

	public function list6( $search = 'default' , $page = 1)
	{
		$this->list_custom(6,$search,$page);
	}

	public function list7( $search = 'default' , $page = 1)
	{
		$this->list_custom(7,$search,$page);
	}

	public function list8( $search = 'default' , $page = 1)
	{
		$this->list_custom(8,$search,$page);
	}

	public function list9( $search = 'default' , $page = 1)
	{
		$this->list_custom(9,$search,$page);
	}

	public function list100( $search = 'default' , $page = 1)
	{
		$this->list_custom(100,$search,$page);
	}

	public function list101( $search = 'default' , $page = 1)
	{
		$this->list_custom(101,$search,$page);
	}

	public function list102( $pid , $search = 'default' , $page = 1)
	{
		$type	=	102;
		$this->_listyanjipid	=	$pid;
		$this->listcustom	=	$type;
		$this->view->assign('custom' , $type);
		$this->view->assign('_listyanjipid' , $this->_listyanjipid);
		$this->index($search,$page);
	}

	public function list103( $search = 'default' , $page = 1)
	{
		$this->list_custom(103,$search,$page);
	}

	public function list104( $search = 'default' , $page = 1)
	{
		$this->list_custom(104,$search,$page);
	}

	public function list105( $search = 'default' , $page = 1)
	{
		$this->list_custom(105,$search,$page);
	}


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */