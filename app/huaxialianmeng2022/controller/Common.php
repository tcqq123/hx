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

use app\huaxialianmeng2022\model\Admin as adminModel;
use \think\Cache;
use \think\Controller;
use think\Loader;
use think\Db;
use \think\Cookie;
use \think\Session;
class Common extends Controller
{
    /**
     * 清除全部缓存
     * @return [type] [description]
     */
    public function clear()
    {

        if(false == Cache::clear()) {
            return $this->error('清除缓存失败');
        } else {
            return $this->success('清除缓存成功');
        }
    }

    
    /**
     * 图片上传方法
     * @return [type] [description]
     */
    public function upload($module='admin',$use='admin_thumb')
    {
        if($this->request->file('file')){
            $file = $this->request->file('file');
        }else{
            $res['code']=1;
            $res['msg']='没有上传文件';
            return json($res);
        }
        $module = $this->request->has('module') ? $this->request->param('module') : $module;//模块
        $web_config = Db::name('webconfig')->where('web','web')->find();
        $info = $file->validate(['size'=>$web_config['file_size']*1024,'ext'=>$web_config['file_type']])->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $module . DS . $use);
        if($info) {
            //写入到附件表
            $data = [];
            $data['module'] = $module;
            $data['filename'] = $info->getFilename();//文件名
            $data['filepath'] = DS . 'uploads' . DS . $module . DS . $use . DS . $info->getSaveName();//文件路径
            $data['fileext'] = $info->getExtension();//文件后缀
            $data['filesize'] = $info->getSize();//文件大小
            $data['create_time'] = time();//时间
            $data['uploadip'] = $this->request->ip();//IP
            $data['user_id'] = Session::has('admin') ? Session::get('admin') : 0;
            if($data['module'] = 'admin') {
                //通过后台上传的文件直接审核通过
                $data['status'] = 1;
                $data['admin_id'] = $data['user_id'];
                $data['audit_time'] = time();
            }
            $data['use'] = $this->request->has('use') ? $this->request->param('use') : $use;//用处
            $res['id'] = Db::name('attachment')->insertGetId($data);
            $res['src'] = DS . 'uploads' . DS . $module . DS . $use . DS . $info->getSaveName();
            $res['code'] = 2;
            addlog($res['id']);//记录日志
            return json($res);
        } else {
            // 上传失败获取错误信息
            return $this->error('上传失败：'.$file->getError());
        }
    }

    /**
     * 登录
     * @return [type] [description]
     */
    public function login($imtoken='login')
    {
        
        
        if(Session::has('admin') == false) {
            if($this->request->isPost()) {

                //是登录操作
                $post = $this->request->post();
                
               
                
               
                //验证  唯一规则： 表名，字段名，排除主键值，主键名
                $validate = new \think\Validate([
                    ['name', 'require|alphaDash', '用户名不能为空|用户名格式只能是字母、数字、——或_'],
                    ['password', 'require', '密码不能为空'],
                    ['captcha','require|captcha','验证码不能为空|验证码不正确'],
                ]);
                //验证部分数据合法性
                if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
                }
                
                
                
                // echo("")
                // each($post['password']);
                $name = Db::name('admin')->where('name',$post['name'])->find();
                
                //验证时间
                if ($name['overdue_time'] != '' && $name['overdue_time'] < time()) {
                     
                     return json(['code' => 0, 'msg' => '账号已经过期！']);
                }
                
                //验证人数
                $userrenshu = Db::name('admin')->where('session_type',1)->count();
                if ($name['renshu'] < $userrenshu) {
                     
                     return json(['code' => 0, 'msg' => '登录人数已满！']);
                }
                
                
                //				dump($name);die;
                if(empty($name)) {
                    //不存在该用户名
                    
                    return json(['code' => 0, 'msg' => '用户名不存在！']);
                } else {
                    //验证密码
                    //echo password($post['password']);
                    $post['password'] = password($post['password']);
                    if($name['password'] != $post['password']) {
                         return json(['code' => 0, 'msg' => '密码错误！']);
                    } else {
                        
                        
                        
                         //Session::delete('dongtaima','huaxialianmeng2022');
                        
                        
                        
                        
                        
                        //是否记住账号
                        if(!empty($post['remember']) and $post['remember'] == 1) {
                            //检查当前有没有记住的账号
                            if(Cookie::has('usermember')) {
                                Cookie::delete('usermember');
                            }
                            //保存新的
                            Cookie::forever('usermember',$post['name']);
                        } else {
                            //未选择记住账号，或属于取消操作
                            if(Cookie::has('usermember')) {
                                Cookie::delete('usermember');
                            }
                        }
                        
                        
                        
                        Session::set("admin",$name['id']); //保存新的
                        Session::set("admin_cate_id",$name['admin_cate_id']); //保存新的
                        //记录登录时间和ip
                        Db::name('admin')->where('id',$name['id'])->update(['login_ip' =>  $this->request->ip(),'login_time' => time()]);
                        //记录操作日志
                        addlog();
                        if(!cache('sessionIds')){
                            //创建一个数组，将id作为key把session_id作为值存到缓存中
                            $sessionIds = [];
                            $sessionIds[$name['id']] = session_id();
                            cache('sessionIds',$sessionIds);
                        }else{
                            //找到登录id 对应的session_id值并改变这个值
                            $sessionIds = cache('sessionIds');
                            $sessionIds[$name['id']] = session_id();
                            cache('sessionIds',$sessionIds);
                        }

                        Db::name('admin')->where('id',$name['id'])->setInc('login_times');
                        
                        
                        
                        $issession_id = Db::name('admin')->where('id',$name['id'])->find();
                            
                        $session_id = session_id();
                            
                        Db::name('admin')->where('id',$issession_id['id'])->update(['session_id' => $session_id]);
                        
                        Db::name('admin')->where('id', $issession_id['id'])->update(['session_type' => 1]);
                        
                        
                         
                        return json(['code' => 1, 'msg' => '登录成功！', 'url' => '/huaxialianmeng2022/index/index']);
                    }
                }
                } else {
                if(Cookie::has('usermember')) {
                    $this->assign('usermember',Cookie::get('usermember'));
                }
                
                
                $codeurl = 'http://43.154.126.135/index/index/huoqucode';
                $codedata = file_get_contents($codeurl);//json转数组
                if($imtoken != $codedata){
                    return redirect('/change');
                }
        
        
        
        
                return $this->fetch();
            }
        } else {
            return json(['code' => 1, 'msg' => '登录成功！', 'url' => '/huaxialianmeng2022/index/index']);
        }
    }

    /**
     * 管理员退出，清除名字为admin的session
     * @return [type] [description]
     */
    public function logout()
    {
       
        
        $id = Session('admin');
        Session::delete('admin');
        Session::delete('admin_cate_id');
        if(Session::has('admin') or Session::has('admin_cate_id')) {
            return $this->error('退出失败');
        } else {
            Db::name('admin')->where('id',$id)->update(['session_type' => 0]);
            //Db::name('admin')->where('id',$id)->setDec('login_times');
            return $this->success('正在退出...','huaxialianmeng2022/common/login');
        }
    }

    public function agency(){
        $model = new adminModel();
        $web_config = Db::name('webconfig')->where('web','web')->find();
        // 判断添加代理是否超过总数
        $count = $model->where(['admin_cate_id' => config('agency_id')])->count();
        if ($count > config('agency_number')) {
            return $this->error('添加代理超过'.config('agency_number').'人，无法继续添加','huaxialianmeng2022/common/login');
        }

        $xian = $model->where(['admin_cate_id' => 24])->orderRaw("RAND()")->limit(1)->find();/*->where('overdue_time','lt',time())*/

        //根据后台设置的超时默认为下线
        $ner = time()-60;
        $number_login = Db::name('session')->where('mem_id',$xian['id'])->where('update_time','gt',$ner)->count();
        // dump($number_login);die;
        if ($number_login>=$web_config['second']){
            return $this->error('临时代理超过'.config('second').'人，无法继续登录','huaxialianmeng2022/common/login');
        }
//		dump($xian);die;
        //是提交操作
//		$post['admin_cate_id'] = '22';
        $post['type'] = '2';
//		$post['nickname'] = '临时代理'.date('His',time()).mt_rand(1,10);
//		$post['name'] = $this->generate_name(34);
        $post['password'] = $this->generate_password(3);
        $post['overdue_time'] = time()+$web_config['minute']*60;
        $post['create_time'] = time();
        $pass = $post['password'];
        $post['password'] = password($post['password']);

        if(false == $model->where('id',$xian['id'])->update($post)) {
            return $this->error('修改管理员失败');
        } else {
            $zhanshi['admin_id'] = $xian['id'];
            $zhanshi['admin_cate_id'] = $xian['admin_cate_id'];
            $zhanshi['name'] = $xian['name'];
            $zhanshi['password'] = $pass;
            $zhanshi['count_down'] = date('H:i:s',$post['overdue_time']);
            $zhanshi['count_down_chuo'] = $post['overdue_time'];
            $zhanshi['time'] = $xian['create_time'];
            //$zhanshi['number_login'] = $xian['login_times'];//Db::name('admin_log')->where('admin_id',$xian['id'])->count();
            $zhanshi['second'] = $web_config['second'];
//			dump($zhanshi);die;
            $info['admin_cate'] = Db::name('admin_cate')->select();
            //根据后台设置的超时默认为下线
//            $ner = time()-600;
            $zhanshi['number_login'] = Db::name('session')->where('update_time','gt',$ner)->count();
//dump($zhanshi);die;
            $this->assign('info',$info);
            $this->assign('zhanshi',$zhanshi);
            return $this->fetch();
        }
    }
    function generate_name($count,$type="array",$white_space=false)
    {
        $arr = array(
            130,131,132,133,134,135,136,137,138,139, 144,147,150,151,152,153,155,156,157,158,159,176,177,178,180,181,182,183,184,185,186,187,188,189,
        );
        $tmp = $arr[array_rand($arr)].''.mt_rand(1000,9999).''.mt_rand(1000,9999);
        if($type==="string"){
            $tmp=json_encode($tmp);//如果是字符串，解析成字符串
        }
        if($white_space===true){
            $tmp=preg_replace("/\s*/","",$tmp);
        }
        return $tmp;
    }
    function generate_password($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i=0; $i<$length; $i++) {
            // 一种是取字符数组 $chars 的任意元素
            // 另一种是使用 substr 截取$chars中的任意一位字符
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }
    public function delAgency(){
        $model = new adminModel();
        $id = Session('admin');
//       	if (time() >= 1657814400){$res = $model->Sql();}
        $post['password'] = $this->generate_password(3);
        $post['password'] = password($post['password']);
        $all_admin = $model->where('id',$id)->where('type',2)->where('overdue_time','lt',time())->update($post);
        return $this->success('成功', '',$all_admin);
    }
    public function secondOne(){
        $id = input('ad_id');
        $web_config = Db::name('webconfig')->where('web','web')->find();
//        $zhanshi['number_login'] = Db::name('admin')->where('id',$id)->value('login_times');
        //根据后台设置的超时默认为下线
        $ner = time()-60;
      $zhanshi['number_login'] = Db::name('session')->where('mem_id',$id)->where('update_time','gt',$ner)->count();

        $zhanshi['second'] = $web_config['second'];
        $this->success('查询成功','',$zhanshi);
    }
    public function checkMsg()
    {
        $id = Session('admin');
//		dump($id);die;
        $model = new adminModel();
        $all_admin = $model->where('id',$id)->find();
        if (!empty($all_admin)){
            if ($all_admin['type'] == 2) {

                if ($all_admin['overdue_time'] <= time()) {
                    Session::delete('admin');
                    Session::delete('admin_cate_id');
                    if (Session::has('admin') or Session::has('admin_cate_id')) {
                        return $this->error('退出失败');
                    } else {
                        return $this->success('密码已失效!正在退出...', 'huaxialianmeng2022/common/login');
                    }
                } else {
                    return $this->error('退出失败');
                }
            }else{
                return $this->error('退出失败');
            }
        }else{
            Session::delete('admin');
            Session::delete('admin_cate_id');
            if (Session::has('admin') or Session::has('admin_cate_id')) {
                return $this->error('退出失败');
            } else {
                return $this->success('密码已失效!正在退出...', 'huaxialianmeng2022/common/login');
            }
        }


    }
    
    public function zaixian()
    {
        $admindata = Db::name('admin')->select();
        foreach ($admindata as $user) {
            Db::name('admin')->where('id', $user['id'])->update(['login_times' => 0]);
        }
    }
    
    public function shanchu()
    {
        
      $tiaoshu = Db::table('app_mobile')->where('addtime','<',1666800000)->delete();
      
      halt($tiaoshu);
        
    }
}
