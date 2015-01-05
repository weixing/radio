<?php 
class Uploadtool{

    public function build_photo_dir($pid)
    {
        $dir_hash   =   md5($pid);
        $dir_tmp    =   substr($dir_hash,0,2).'/'.substr($dir_hash,-2).'/';
        return $dir_tmp;
    }



    public function mmkdir($dir)
    {
        $path = array();
        $dir = preg_replace("/\/*$/", "", $dir);
        while (!is_dir($dir) && strlen(str_replace("/", "", $dir))) {
            $path[] = $dir;
            $dir = preg_replace("/\/\w+$/", "", $dir);
        }
        krsort($path);
        if (sizeof($path)) {
            foreach($path as $key=>$val) {
                @mkdir($val, 0777);
            }
        }
        return true;
    }

    public function deletedir($dir,$del_self=false)
    {
        if(!$handle=@opendir($dir))
        {     //检测要打开目录是否存在
            //die("没有该目录");
            return false;
        }

        while(false !==($file=readdir($handle)))
        {
            if($file!=="."&&$file!=="..")
            {       //排除当前目录与父级目录
                $file=$dir .DIRECTORY_SEPARATOR. $file;
                if(is_dir($file))
                {
                    $this->deletedir($file,true);
                }
                else
                {
                    if(@unlink($file))
                    {
                        //echo "文件<b>$file</b>删除成功1。<br>";
                    }
                    else
                    {
                        //echo  "文件<b>$file</b>删除失败2!<br>";
                    }
                }
            }
        }
        if($del_self)
        {
            if(@rmdir($dir))
            {
                //echo "目录<b>$dir</b>删除成功了3。<br>\n";
            }
            else
            {
                //echo "目录<b>$dir</b>删除失败4！<br>\n";
            }
        }
    }


    /*  @creates a compressed zip file  将多个文件压缩成一个zip文件的函数 
    *   @$files 数组类型  实例array("1.jpg","2.jpg");   
    *   @destination  目标文件的路径  如"c:/androidyue.zip" 
    *   @$overwrite 是否为覆盖与目标文件相同的文件 
    *   @Recorded By Androidyue 
    *   @Blog:http://thinkblog.sinaapp.com 
     */  
    public function create_zip($files = array(),$destination = '',$overwrite = false) {  
        //if the zip file already exists and overwrite is false, return false  
        //如果zip文件已经存在并且设置为不重写返回false  
        if(file_exists($destination) && !$overwrite) { return false; }  
        //vars  
        $valid_files = array();  
        //if files were passed in...  
        //获取到真实有效的文件名  
        if(is_array($files)) {  
            //cycle through each file  
            foreach($files as $file) {  
            //make sure the file exists  
                if(file_exists($file)) {  
                $valid_files[] = $file;  
                }  
            }  
        }  
        //if we have good files...  
        //如果存在真实有效的文件  
        if(count($valid_files)) {  
            //create the archive  
            $zip = new ZipArchive();  
            //打开文件       如果文件已经存在则覆盖，如果没有则创建  
            if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {  
                return false;  
            }  
            //add the files  
            //向压缩文件中添加文件  
            foreach($valid_files as $file) {
                $file_arr_tmp   =   explode('/',$file);
                $file_name_tmp  =   $file_arr_tmp[count($file_arr_tmp)-1];
                $zip->addFile($file,$file_name_tmp);  
            }  
            //debug  
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;  
            //close the zip -- done!  
            //关闭文件  
            $zip->close();  
            //check to make sure the file exists  
            //检测文件是否存在  
            return file_exists($destination);  
        }else{  
            //如果没有真实有效的文件返回false  
            return false;  
        }  
    }  
    /****  
    //测试函数 
    $files=array('temp.php','test.php'); 
    create_zip($files, 'myzipfile.zip', true); 
    ****/  
}
