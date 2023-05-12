<?php
namespace app\change\controller;
use \think\Controller;
use think\Db;
use \think\Session;
class Index
{
    public function index()
    {
	   return view();
    }
    
    public function login()
    {
	   $post = input('post.')['dongtaima'];
	   if($post == '' || empty($post)){
	       return json(['code' => 0, 'msg' => '验证失败！']);
	   }
	   $codeurl = 'http://43.154.126.135/index/index/huoqucode';
	   $timeurl = 'http://43.154.126.135/index/index/huoqutime';
	   $codedata = file_get_contents($codeurl);//json转数组
	   $timedata = file_get_contents($timeurl);//json转数组
	   $timedata = (int)$timedata;
	   $shijian = time()-$timedata;
	   
	   if($shijian>45){
	       return json(['code' => 0, 'msg' => '验证时间过期！']);
	   }
	   
	   if($post === $codedata){

	        return json(['code' => 1, 'msg' => '验证成功！', 'url' => '/huaxialianmeng2022/common/login?imtoken='.$codedata]);

	   }else{
	       return json(['code' => 0, 'msg' => '验证失败！']);
	   }
	  
	   
    }
    
}
