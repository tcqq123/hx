<?php
namespace app\huaxialianmeng2022\controller;

use app\huaxialianmeng2022\controller\Permissions;
use \think\Db;
use \think\Cookie;
use \think\Session;
use \think\Cache;
class Img extends Permissions
{
    public function index(){
        $p= $this->request->param('id');
        if($p!=""){
            $list=db('imgs')->where('sjh',$p)->order('time desc')->select();
        }else{
        $list=db('imgs')->select();
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    
    public function del(){
        $id = input('get.id');
        $f=db('imgs')->where('id',$id)->select();
        if(count($f)==1){
            db('imgs')->where('id',$id)->delete();
            if(file_exists("./imgs/".$f[0]['name'].".png")){
                unlink("./imgs/".$f[0]['name'].".png");
                echo 'okk';
            }
            
        }
        echo 'ok';
    }
}