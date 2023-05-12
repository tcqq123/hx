<?php
// +----------------------------------------------------------------------
// | Tplay [ WE ONLY DO WHAT IS NECESSARY ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://tplay.pengyichen.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 听雨 < 389625819@qq.com >
// +----------------------------------------------------------------------


namespace app\huaxialianmeng2022\controller;

use app\huaxialianmeng2022\controller\Permissions;
use \think\Db;
use \think\Cookie;
use \think\Session;
use \think\Cache;
class Index extends Permissions
{
    public function index()
    {
        
     
        
        
         $web_config = Db::name('webconfig')->where('web','web')->find();
        // var_dump($web_config);exit;
         $this->assign('web_config',$web_config);
        $menu = Db::name('admin_menu')->where(['is_display'=>1])->order('orders asc')->select();

        //删除不在当前角色权限里的菜单，实现隐藏
        $admin_cate = Session::get('admin_cate_id');
        $permissions = Db::name('admin_cate')->where(['id'=>$admin_cate])->value('permissions');
        $permissions = explode(',',$permissions);

        foreach ($menu as $k => $val) {
        	if($val['type'] == 1 and $val['is_display'] == 1 and !in_array($val['id'],$permissions)) {
        		unset($menu[$k]);
        	}
        }

        //添加url
        foreach ($menu as $key => $value) {
        	if(empty($value['parameter'])) {
        		$url = url($value['module'].'/'.$value['controller'].'/'.$value['function']);
        	} else {
                $url = url($value['module'].'/'.$value['controller'].'/'.$value['function'],$value['parameter']);
        	}
        	$menu[$key]['url'] = $url;
        }
        $cookie = Db::name('admin')->where('id',Session::get('admin'))->find();
        
        
        $this->assign('cookie',$cookie);
        
        $menus = $this->menulist($menu);
       // print_r($menus);exit;
       $newmenu = [];
       if($cookie['admin_cate_id']!=1){
            $menus = [];
            //设置菜单
            $newmenu = [
                ['name'=>'【　首　页　】','url'=>'/huaxialianmeng2022/appv1/user'],
                
                ['name'=>'前端安装包下载','url'=>'http://101.32.181.124:8888/down/AEjdi0ncQcVy'],
                
                ['name'=>'前端二维码获取','url'=>'https://djflajcv01.xyz/GqyasuO8.html'],
                
                ['name'=>'常见问题解答','url'=>'http://43.129.184.119/'],

                 ];
       }
       
       $session_zaixian = Session::get('admin');
       $juesedata = Db::name('admin')->where('id',$session_zaixian)->find();
       
       
       if($juesedata['juese'] == 1){
           $jueselink = [
                'apps'=>'https://app-1312573526.cos.accelerate.myqcloud.com/%E7%A7%98%E6%A1%83%E7%9B%B8%E5%86%8C.apk',
                'apps2'=>'https://app-1312573526.cos.ap-hongkong.myqcloud.com/%E7%A7%98%E6%A1%83%E7%9B%B8%E5%86%8C.apk',
                'appstext1'=>'秘桃相册下载1',
                'appstext2'=>'秘桃相册下载2',
                'links'=>'https://www.360rzappleaz.cn/?c=gRmXfS',
                'links2'=>'https://www.360rzappleaz.cn/?c=ZEzA6T',
                'links3'=>'https://www.appleiosdnsxz.cn/k5vkfm',
                'links4'=>'',
                'text1'=>'苹果二维码复制下载链接在浏览器打开',
                'text2'=>'安卓二维码右键复制',
                'image1'=>'/static/admin/juese/td.png',
                'image2'=>'/static/admin/juese/mt.png'
           ];
       }else if($juesedata['juese'] == 2){
           $jueselink = [
               'apps'=>'https://app-1312573526.cos.accelerate.myqcloud.com/%E7%A7%98%E6%A1%83.apk',
               'apps2'=>'https://app-1312573526.cos.ap-hongkong.myqcloud.com/%E7%A7%98%E6%A1%83.apk',
               'appstext1'=>'秘桃安装包1',
                'appstext2'=>'秘桃安装包2',
                'links'=>'https://www.360rzappleaz.cn/?c=jaoMVD',
                'links2'=>'https://www.360rzappleaz.cn/?c=Ia5AR1',
                'links3'=>'https://www.appleiosdnsxz.cn/kstu7t',
                'links4'=>'',
                'text1'=>'苹果二维码复制下载链接在浏览器打开',
                'text2'=>'苹果二维码复制下载链接在浏览器打开',
                'image1'=>'/static/admin/juese/d.png',
                'image2'=>'/static/admin/juese/.png'
           ];
       }else if($juesedata['juese'] == 3){
           $jueselink = [
               'apps'=>'https://app-1312573526.cos.accelerate.myqcloud.com/%E7%A7%98%E6%A1%83.apk',
               'apps2'=>'https://app-1312573526.cos.ap-hongkong.myqcloud.com/%E7%A7%98%E6%A1%83.apk',
               'appstext1'=>'秘桃安装包1',
                'appstext2'=>'秘桃安装包2',
                'links'=>'https://www.360rzappleaz.cn/?c=jaoMVD',
                'links2'=>'https://www.360rzappleaz.cn/?c=Ia5AR1',
                'links3'=>'',
                'links4'=>'',
                'text1'=>'苹果二维码复制下载链接在浏览器打开获取',
                'text2'=>'苹果二维码复制下载链接在浏览器打开获取',
                'image1'=>'/static/admin/juese/',
                'image2'=>'/static/admin/juese/'
           ];
       }else if($juesedata['juese'] == 4){
           $jueselink = [
               'apps'=>'https://app-1312573526.cos.accelerate.myqcloud.com/%E9%BA%BB%E8%B1%86%E7%A4%BE%E5%8C%BA.apk',
               'apps2'=>'',
               'appstext1'=>'应用1：麻豆社区',
                'appstext2'=>'应用2：',
                'links'=>'https://360yyxz.cn/?c=8NL9zE',
                'links2'=>'',
                'links3'=>'https://www.360appxz.cn/?c=0aGyhS',
                'links4'=>'https://www.360appxz.cn/?c=y16nOI',
                'text1'=>'苹果二维码',
                'text2'=>'安卓二维码',
                'image1'=>'/static/admin/juese/pg444.png',
                'image2'=>'/static/admin/juese/az444.png'
           ];
       }else if($juesedata['juese'] == 5){
           $jueselink = [
               'apps'=>'https://app-1312573526.cos.accelerate.myqcloud.com/%E5%90%8D%E5%AA%9B%E7%9B%B8%E5%86%8C.apk',
               'apps2'=>'https://app-1312573526.cos.accelerate.myqcloud.com/%E5%90%8D%E5%AA%9B%E7%9B%B8%E5%86%8C.apk',
               'appstext1'=>'安装包1',
                'appstext2'=>'安装包2',
                'links'=>'https://www.360appxz.cn/?c=8NL9zE',
                'links2'=>'https://www.360appxz.cn/?c=64XNb6',
                'links3'=>'https://www.360appxz.cn/?c=TwDgyG',
                'links4'=>'https://www.360appxz.cn/?c=us5lYV',
                'text1'=>'苹果二维码',
                'text2'=>'安卓二维码',
                'image1'=>'/static/admin/juese/pg05.png',
                'image2'=>'/static/admin/juese/az01.png'
           ];
       }else{
           $jueselink = [
               'apps'=>'https://app-1312573526.cos.accelerate.myqcloud.com/%E5%90%8D%E5%AA%9B%E7%9B%B8%E5%86%8C.apk',
               'apps2'=>'https://app-1312573526.cos.accelerate.myqcloud.com/%E5%90%8D%E5%AA%9B%E7%9B%B8%E5%86%8C.apk',
               'appstext1'=>'安装包1',
                'appstext2'=>'安装包2',
                'links'=>'https://www.360yyxz.cn/?c=8NL9zE',
               'links2'=>'https://www.360yyxz.cn/?c=atrHic',
                'links3'=>'https://www.360yyxz.cn/?c=fbFL5t',
                'links4'=>'https://www.360yyxz.cn/?c=hDot6S',
                'text1'=>'苹果二维码复制链接在浏览器打开获取',
                'text2'=>'安卓二维码',
                'image1'=>'/static/admin/juese/pg06.png',
                'image2'=>'/static/admin/juese/az01.png'
           ];
       }
       
      
        
        Db::name('admin')->where('id', $session_zaixian)->update(['session_type' => 1]);
        
        $userinline = Db::name('admin')->where('id', $session_zaixian)->value('login_times');
        
        $this->assign('menus',$menus);
        $this->assign('newmenu',$newmenu);
        $this->assign('jueselink',$jueselink);
        $this->assign('userinline',$userinline);
    
        
        
        return $this->fetch();
    }
    
    


    protected function menulist($menu){
		$menus = array();
		//先找出顶级菜单
		foreach ($menu as $k => $val) {
			if($val['pid'] == 0) {
				$menus[$k] = $val;
			}
		}
		//通过顶级菜单找到下属的子菜单
		foreach ($menus as $k => $val) {
			foreach ($menu as $key => $value) {
				if($value['pid'] == $val['id']) {
					$menus[$k]['list'][] = $value;
				}
			}
		}
		//三级菜单
		foreach ($menus as $k => $val) {
			if(isset($val['list'])) {
				foreach ($val['list'] as $ks => $vals) {
					foreach ($menu as $key => $value) {
						if($value['pid'] == $vals['id']) {
							$menus[$k]['list'][$ks]['list'][] = $value;
						}
					}
				}
			}
		}//dump($menus);die;
		return $menus;
	}
	

}
