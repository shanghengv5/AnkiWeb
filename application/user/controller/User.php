<?php
namespace app\user\controller;
use think\Controller;
use app\user\model\User as UserModel;
use app\user\model\Subject as SubjectModel;
use think\Session;
use think\Request;
use app\user\model\Course as CourseModel;
use app\user\functions\Course as CourseFns;
use app\user\functions\Subject as SubjectFns;
use app\user\functions\Data as DataFns;


class User extends Controller
{
    //这是一个注册页面
    public function register(Request $request)
    {
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
                return $this->fetch('subject');      
            } else {
                return $user->geterror();
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
            $valid = session('valid_user');
            $list = $subfns->getSubjectList($valid);
            return view('subject', ['list'=>$list]);            
        } else {
            return view('index');
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
    
    //添加学习科目
    public function addSubject(Request $request) 
    {
        $subfns = new SubjectFns;
        //如果没有提交表单内容,显示表单.
        if(empty(input('post.'))) {
            return view();
        } 
        //判断是否已经登录
        if(($username=DataFns::isLogin())) {
            //将表单内容插入到xtie_subject表
            $subject = new SubjectModel;
            $subject->name = input('name');
            $subject->user_id = DataFns::getIdByUsername($username);
            $subject->expire = 0;
            $subject->new = 0;
            //判断科目名字是否被填入
            $res = $this->validate(input('post.'), 'Subject');
            if($res !== true) {
                return $res;
            }
            //判斷是否已經有這個科目名了
            if(DataFns::isExist($subject ,'name', input('name'))) {
                return view();
            }
            if($subject->allowField(true)->save()) {
                $list = $subfns->getSubjectList($username);
                return view('subject', ['list'=>$list,]);
            } else {
                return view('index');
            }
            $list = $subfns->getSubjectList($username);
            return view('subject', ['list'=>$list]);
        } else {
            return view('login');
        }
    }
    //展示已經有的科目
    public function subject()
    {
        $subfns = new SubjectFns;
        if('delete' == input('anchor')) {
            $subfns->delSubject();
        }
        if('rename' == input('anchor')) {
            $subfns->updateSubject();
        }
        //得到subject表中的所有数据然后传入视图中.
        if(($username = DataFns::isLogin())){
            $subfns->checkSubject($username);
            $list = $subfns->getSubjectList($username);
            //通过user_id获取科目列表
            if($list) {       
                $this->assign('list', $list);
                return $this->fetch();
            } else {
                return view('add_subject');
            }   
        } else {
            return view('login');
        }
    }
    //增加课程
    public function addCourse() 
    {   
        $subfns = new SubjectFns;
        //判断用户是否登录
        if(($username=DataFns::isLogin())) {
        //将subjectlist传递给add_course页面
        $subjectlist = $subfns->getSubjectList($username);
        if(!$subjectlist) {
            return view('add_subject');
        }
        $this->assign('subjectlist', $subjectlist);
        if(empty(input('post.'))) {
            return $this->fetch();
        } else {
            //表单不为空,将name,content,suebject_id传入数据库
            $res = $this->validate(input('post.'), 'Course');
            if($res !== true) {
                return $res;
            } 
            $cour = new CourseModel;
            if($cour->allowField(true)->save(input('post.'))) {
                //如果有重复的知识点需要提醒,但不阻止
                if(DataFns::isExist($cour, 'name', input('name'))) {
                    return view();
                }
                return view();
            } else {
                return view();
                }
            }
        } else {
            return view('login');
        }         
    }
    //浏览知识点,需要显示科目与知识点的关系
    public function viewCourse($sub_id='', $course_id='')
    {
        $courfns = new CourseFns;
        $subfns = new SubjectFns;
        if('update' == input('anchor')) {
            $courfns->updateCourse();
        }
        if('delete' == input('anchor')) {
            $courfns->delCourse();
        }
        //判断用户是否已经登录.
        if(($username=DataFns::isLogin())){
            //得到该用户的科目列表;
            $subjectlist = $subfns->getSubjectList($username);
            //如果没有科目,需要先创建科目
            if(!$subjectlist) {
                return view('add_subject');
            }
            //如果没有传入sub_id,则默认显示排名排名靠前的科目
            if(!input('sub_id')) {
                $sub_id = $subjectlist[0]['subject_id'];
            }
            $list = $courfns->getCourseList($sub_id);
            $this->assign('subjectlist', $subjectlist);
            //如果没有知识点,需要先创建知识点. 
            if(!$list) {
                return view('add_course');
            } 
            //得到相应的course
            $course = $courfns->getCourse($course_id);
            //如果没有传入sub_id,默认为显示排序靠前的知识点
            if(!$course_id) {
                $course = $list[0];
            }
            //传递变量到视图中
            
            $this->assign('sub_id', $sub_id);
            $this->assign('courselist', $list);    
            $this->assign('primary_c', $course);
            return $this->fetch();
        } else {
            return view('login');
        }
    }
    //开始学习,通过选项判断复习时间
    public function studySubject() 
    {
        $subfns = new SubjectFns;
        $coursefns = new CourseFns;
        if(($username=DataFns::isLogin())){
            if(($sub_id = input('subject_id'))) {
                $sub = SubjectModel::get($sub_id);
                if(input('op')) {
                    $cour = CourseModel::get(input('course_id'));
                    $subfns->checkStatu($cour, $sub, input('op'));
                } 
                if(($cour = $coursefns->getExpireCourse($sub_id))) {
                    $this->assign('cour', $cour);
                    return $this->fetch();
                } else {
                    echo "你已经全部学习完!";
                    $list = $subfns->getSubjectList($username);
                    return view('add_course', ['subjectlist'=>$list, ]);
                }
            } else {
                echo "错误,请传入id";
            }
        } else {
            return view('login');
        }
    }
    //test
    public function test()
    {
        return view();
    }
}