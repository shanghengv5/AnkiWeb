<?php
namespace app\user\controller;
use think\Controller;
use app\user\model\User as UserModel;
use app\user\model\Subject as SubjectModel;
use think\Session;
use think\Request;
class User extends Controller
{
    //这是一个首页.
    public function index(Request $request, $username='游客')
    {
        //如果登录了,将用户名赋值
        if(session('?valid_user')) {
            $username = session('valid_user');
        }
        $this->assign('username', $username);
        return $this->fetch();
    }
    //这是一个注册页面
    public function register(Request $request)
    {
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
            echo "该用户名已经注册!";
            return view('register');
        }
        //将合法内容保存到数据库,并且自动登录
        if($user->allowField(true)->save($arr)) {
            echo '注册成功';
            login();          
        } else {
            return $user->geterror();
        }
    }
    //这是一个登录页面
    public function login(Request $request) 
    {
        //判断是否已经登录,如果登录,跳转到首页
        if(session('valid_user')) {
            return view('index'); 
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
        //验证用户和密码是否正确,验证成功返回首页
        if($user->where('username', $request->post('username'))
        ->where('password', sha1($request->post('password')))->count() == 1) { 
            session('valid_user', input('username')); 
            $valid = session('valid_user');
            echo 'hello'.$valid; 
            return view('index');
        } else {
            echo '账号密码错误';
            return view('index');
        }
    }
    //这是一个登出页面
    public function logout(Request $request) 
    {
        //如果会话不存在有效用户,则需要登录才能登出
        if(!session('?valid_user')) {
            echo "you already log out";
            return view('login');
        }
        //清空当前域的valid_user内容.并且跳转到登录界面.
        session('valid_user', null);
        return view('login');
    }

    public function test(Request $request) 
    {
        //这是一个用于测试的方法.
        echo $this->getIdByUsername('xiaotiejiang');
       
    }
    //通过用户名得到用户id
    private function getIdByUsername($username) 
    {
        $user = UserModel::getByUsername($username);
        return $user['user_id'];
    }
    //判断表中是否已经存在这条数据
    private function isExist($instance, $column ,$name) 
    {
        
        $res = $instance->where($column, $name)->count();
        if($res >= 1) {
            return true;
        } else {
            return false;
        }
    }
    //添加学习科目
    public function addSubject(Request $request) 
    {
        //如果没有提交表单内容,显示表单.
        if(empty(input('post.'))) {
            return view('addsubject');
        } 
        //判断是否已经登录
        if(($username=session('valid_user'))) {
            //将表单内容插入到xtie_subject表
            $subject = new SubjectModel;
            $subject->name = input('name');
            $subject->user_id = $this->getIdByUsername($username);
            //判断科目名字是否被填入
            $res = $this->validate(input('post.'), 'AddSubject');
            if($res !== true) {
                return $res;
            }
            //判斷是否已經有這個科目名了
            if($this->isExist($subject ,'name', input('name'))) {
                echo "已經有這個編程名了";
                return view('addsubject');
            }
            if($subject->allowField(true)->save()) {
                echo "插入新科目[".input('name')."]";
                return view('index');
            } else {
                echo '添加失败';
                return view('index');
            }
            return view('index');
        } else {
            echo "请先登录后,再进行操作";
            return view('login');
        }
    }
    //展示已經有的科目
    public function subject()
    {
        return "正在籌劃中";
    }

}