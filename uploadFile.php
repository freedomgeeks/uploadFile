<?php
/**
 * @author freegeek
 * 图片、文件上传类，支持同时上传多个多文件和多个单文件
 */
class uploadFile{
     public $path='upload';
     public $flag=true;
     public $maxSize=9048576;
     public $allowExt=['jpeg','jpg','png','gif'];
     public $filename='timestamp'; 
     
     function __construct(){
         
     }
     
      
    /**
     * 构建上传文件信息
     * @return unknown
     */
    function getfiles(){
 
        $i=0;
        foreach($_FILES as $file){
            if(is_string($file['name'])){
                $files[$i]=$file;
                $i++;
            }elseif(is_array($file['name'])){
                foreach($file['name'] as $key=>$val){
                    $files[$i]['name']=$file['name'][$key];
                    $files[$i]['type']=$file['type'][$key];
                    $files[$i]['tmp_name']=$file['tmp_name'][$key];
                    $files[$i]['error']=$file['error'][$key];
                    $files[$i]['size']=$file['size'][$key];
                    $i++;
                }
            }
        }
        return $files;
    
    }
    
    
    /**
     * 针对于单文件、多个单文件、多文件的上传
     * @param array $fileInfo
     
     * @return string
     */
    private function uploadfile($fileInfo){
        
        //判断错误号
        if($fileInfo['error']===UPLOAD_ERR_OK){
            //检测上传文件的大小
            if($fileInfo['size']>$this->maxSize){
                $res['mes']=$fileInfo['name'].' 上传失败，上传文件过大';
            }
            $ext=$this->getExt($fileInfo['name']);
            //检测上传文件类型
            if(!in_array($ext,$this->allowExt)){
                $res['mes']=$fileInfo['name'].' 上传失败，非法文件类型';
            }
            //检测是否是真实图片类型
               if($this->flag){
                    if(!getimagesize($fileInfo['tmp_name'])){
                       $res['mes']=$fileInfo['name'].' 上传失败，不是真实图片类型';
                    }
                }
            //检测文件是否是通过HTTP POST上传上来的
            if(!is_uploaded_file($fileInfo['tmp_name'])){
                $res['mes']=$fileInfo['name'].' 上传失败，文件不是通过HTTP POST上传上来的';
            }
            if(@$res) return $res;
            //$path='./uploads';
            if(!file_exists($this->path)){
                mkdir($this->path,0777,true);
                chmod($this->path,0777);
            }
            $uniName=$this->generateFileName();
            $destination=$this->path.'/'.$uniName.'.'.$ext;
            if(!move_uploaded_file($fileInfo['tmp_name'],$destination)){
                $res['mes']=$fileInfo['name'].'文件移动失败';
            }
            $res['mes']=$fileInfo['name'].'上传成功';
            $res['dest']=$destination;
            $res['status']=1;
            return $res;
    
        }else{
            //匹配错误信息
            switch ($fileInfo['error']){
                case 1:
                    $res['mes']= '上传文件超过了PHP配置文件upload_max_filesize选项的值';
                    break;
                case 2:
                    $res['mes']= '超过了表单MAX_FILE_SIZE限制';
                    break;
                case 3:
                    $res['mes']= '文件部分被上传';
                    break;
                case 4:
                    $res['mes']= '没有选择上传文件';
                    break;
                case 6:
                    $res['mes']= '没有找到临时目录';
                    break;
                case 7:
                case 8:
                    $res['mes']= '系统错误';
                    break;
            }
            return $res;
    
        }
    }
    
    
    /**
     * 得到文件的扩展名
     * @param resources $filename
     */
     function getExt($filename){
        return strtolower(pathinfo($filename,PATHINFO_EXTENSION));
    }
    
    /**
     * 支持两种文件命名规则
     **/
    function generateFileName(){
        if($this->filename=='md5'){
            return md5(uniqid(microtime(true),true));
        }else{
            list($usec, $sec) = explode(" ", microtime());
            $msec=round($usec*1000);
            return date("YmdHis").$msec;
        }
        
    }
    
    /**
     * 上传文件入口
     */
    function doAction(){
      
        $files=$this->getfiles();
         foreach($files as $fileInfo){
             $res=$this->uploadfile($fileInfo);
             if(isset($res['status'])){
               echo $res['mes'],'<br/>';//可关闭上传成功提示
              $uploadfiles[]=$res['dest'];
            }else{
                $this->p($res);
            }
        
        }
        $uploadfiles=array_values(array_filter($uploadfiles));
        // $this->p($uploadfiles).'<br />';
        
    }
     
    
    /**
     * 格式化数组输出
     * @param array $arr 
     */
    function p(array $arr){
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
}
