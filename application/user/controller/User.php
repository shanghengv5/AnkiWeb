<?php
namespace app\user\controller;
use think\Controller;
use app\user\model\User as UserModel;
use think\Request;
use app\user\functions\Subject as SubjectFns;
use app\user\functions\Data as DataFns;
class User extends Controller
{
    //这是一个注册页面
    public function register(Request $request)
    {
        $subfns = new SubjectFns;
        if(!DataFns::isLogin()){
            //如果没有表单内容提交,需要返回注册表单.
            if(empty($request->post())) {
                return view('register');
            }
            //使用user模型来对数据表插入数据.
            $user = new UserModel;
            $arr = [
                'username' => $request->post('username'),
                'password' => sha1($request->post('password')),
                'email' => $request->post('email'),
            ];
            //验证注册表单字段是否合法.
            $result = $this->validate($arr, 'Register');
            if(true !== $result) {
                return $result;
            }
            //验证是否已经有了这个用户名.
            if($user->where('username', $request->post('username'))->count() != 0){
                return view('register');
            }
            //将合法内容保存到数据库,并且自动登录
            if($user->allowField(true)->save($arr)) {
                session('valid_user', $request->post('username'));
                $list = $subfns->getSubjectList($request->post('username'));
                $this->assign('list', $list);
                return $this->fetch('study/subject');      
            } else {
                return $user->getError();
            }
        }
        
    }
    //这是一个登录页面
    public function login() 
    {
        $subfns = new SubjectFns;
        $request = Request::instance();
        //判断是否已经登录,如果登录,跳转到首页
        if(($username=DataFns::isLogin())) {
            $list = $subfns->getSubjectList($username);
            return view('subject', ['list'=>$list]);            
        }
        //判断是否有表单内容提交,如果没有,跳转到登录表单.
        if(empty($request->post())) {
            return view('login');
        }
        //验证输入表单内容是否合法.
        $user = new UserModel;
        $res = $this->validate(input('post.'), 'Login');
        //如果不合法,返回不合法的内容.
        if($res !== true) {
            return $res;
        }
        //验证用户和密码是否正确,验证成功返回用户的科目
        if($user->where('username', $request->post('username'))
        ->where('password', sha1($request->post('password')))->count() == 1) { 
            session('valid_user', input('username')); 
            $list = $subfns->getSubjectList(input('username'));
            return view('study/subject', ['list'=>$list]);            
        } else {
            return view('login');
        }
    }
    //这是一个登出页面
    public function logout(Request $request) 
    {
        //如果会话不存在有效用户,则需要登录才能登出
        if(!DataFns::isLogin()) {
            return view('login');
        }
        //清空当前域的valid_user内容.并且跳转到登录界面.
        session('valid_user', null);
        return view('login');
    }
    
    //test
    public function test()
    {
        $t_array = explode(' ', 'wo  wo shi shi da shuai ge');      
        $d_array = explode(' ', 'ni hao ruguo wo geng jia chang ne');
        $res = array_merge($t_array, $d_array);       
        
        return view('test');
    }
}