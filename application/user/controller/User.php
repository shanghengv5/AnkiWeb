<?php
namespace app\user\controller;
use think\Controller;
use app\user\model\User as UserModel;
use app\user\model\Subject as SubjectModel;
use think\Session;
use think\Request;
use app\user\model\Course as CourseModel;

class User extends Controller
{
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
            return view('register');
        }
        //将合法内容保存到数据库,并且自动登录
        if($user->allowField(true)->save($arr)) {
            return $this->fetch('subject');      
        } else {
            return $user->geterror();
        }
    }
    //这是一个登录页面
    public function login() 
    {
        $request = Request::instance();
        //判断是否已经登录,如果登录,跳转到首页
        if(($username=$this->isLogin())) {
            $list = $this->getSubjectList($username);
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
            $list = $this->getSubjectList($valid);
            return view('subject', ['list'=>$list]);            
        } else {
            return view('index');
        }
    }
    //这是一个登出页面
    public function logout(Request $request) 
    {
        //如果会话不存在有效用户,则需要登录才能登出
        if(!$this->isLogin()) {
            return view('login');
        }
        //清空当前域的valid_user内容.并且跳转到登录界面.
        session('valid_user', null);
        return view('login');
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
        if($res == 0) {
            return false;
        } else {
            return true;
        }
    }
    //添加学习科目
    public function addSubject(Request $request) 
    {
        //如果没有提交表单内容,显示表单.
        if(empty(input('post.'))) {
            return view();
        } 
        //判断是否已经登录
        if(($username=$this->isLogin())) {
            //将表单内容插入到xtie_subject表
            $subject = new SubjectModel;
            $subject->name = input('name');
            $subject->user_id = $this->getIdByUsername($username);
            $subject->expire = 0;
            $subject->new = 0;
            //判断科目名字是否被填入
            $res = $this->validate(input('post.'), 'Subject');
            if($res !== true) {
                return $res;
            }
            //判斷是否已經有這個科目名了
            if($this->isExist($subject ,'name', input('name'))) {
                return view();
            }
            if($subject->allowField(true)->save()) {
                $list = $this->getSubjectList($username);
                return view('subject', ['list'=>$list,]);
            } else {
                return view('index');
            }
            $list = $this->getSubjectList($username);
            return view('subject', ['list'=>$list]);
        } else {
            return view('login');
        }
    }
    //更新科目
    private function updateSubject()
    {
        //从表单得到id并且传入模型
        $id = input('subject_id');
        $sub = SubjectModel::get($id);
        if($sub) {
            $sub->name = input('name');
            $sub->save();
        } else {
            return $sub->geterror();
        }
    }
    //展示已經有的科目
    public function subject()
    {
        if('delete' == input('anchor')) {
            $this->delSubject();
        }
        if('rename' == input('anchor')) {
            $this->updateSubject();
        }
        //得到subject表中的所有数据然后传入视图中.
        if(($username = $this->isLogin())){
            $this->checkSubject($username);
            $list = $this->getSubjectList($username);
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
    //删除已有的科目
    private function delSubject() 
    {
        //从表单得到id并且传入模型
        $id = input('subject_id');
        $sub = SubjectModel::get($id);
        $course = CourseModel::getBySubjectId($id);
        //如果id存在,则删除
        if($sub) {
            $sub->delete();
            if($course) {
                $course->delete();
            }
        } else {
            return $course->geterror();
        }
    }
    //通过用户名获取科目列表
    private function getSubjectList($username) 
    {
        $user_id = $this->getIdByUsername($username);
        $sub = new SubjectModel;
        $list = $sub->where('user_id', $user_id)->select();
        return $list;
    }
    //增加课程
    public function addCourse() 
    {   
        //判断用户是否登录
            if(($username=$this->isLogin())) {
            //将subjectlist传递给add_course页面
            $subjectlist = $this->getSubjectList($username);
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
                    if($this->isExist($cour, 'name', input('name'))) {
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
    //判断是否登录,如果登录则返回用户名.
    private function isLogin() 
    {
        if(($username = session('valid_user'))) {
            return $username;
        } else {
            return false;
        }
    }
    //得到course的list
    private function getCourseList($value)
     {
        $course = new CourseModel;
        $list = $course->where('subject_id', $value)->select();
        return $list;
    }
    //通过course_id得到course的相关数据
    private function getCourse($id) 
    {
        $res = CourseModel::get($id);
        return $res;
    }
    //浏览知识点,需要显示科目与知识点的关系
    public function viewCourse($sub_id='', $course_id='')
    {
        if('update' == input('anchor')) {
            $this->updateCourse();
        }
        if('delete' == input('anchor')) {
            $this->delCourse();
        }
        //判断用户是否已经登录.
        if(($username=$this->isLogin())){
            //得到该用户的科目列表;
            $subjectlist = $this->getSubjectList($username);
            //如果没有科目,需要先创建科目
            if(!$subjectlist) {
                return view('add_subject');
            }
            //如果没有传入sub_id,则默认显示排名排名靠前的科目
            if(!input('sub_id')) {
                $sub_id = $subjectlist[0]['subject_id'];
            }
            $list =$this->getCourseList($sub_id);
            $this->assign('subjectlist', $subjectlist);
            //如果没有知识点,需要先创建知识点. 
            if(!$list) {
                return view('add_course');
            } 
            //得到相应的course
            $course = $this->getCourse($course_id);
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
    //更新课程内容
    private function updateCourse()
    {
        $course = CourseModel::get(input('course_id'));
        if($course) {
            $course->name = input('name');
            $course->content = input('content');
            $course->save();
        } else {
            return $course->geterror();
        }
    }
    //删除知识点
    private function delCourse() 
    {
        $course = CourseModel::get(input('course_id'));
        $sub = SubjectModel::get($course['subject_id']);
        $this->checkStatu($course, $sub, 'delete');
        if($course) {
            $course->delete();
        }
    }
    //查询用户科目情况
    private function checkSubject($username) 
    {
        $sub = $this->getSubjectList($username);
        if($sub) {
            //获取科目列表,并且检查其中course
            foreach($sub as $subitem) {
                $this->checkCourse($subitem['subject_id']);
            }
        }
    }
    //查询课程情况
    private function checkCourse($id) {
        $cour = $this->getCourseList($id);
        if($cour) {
            //获取这个科目的课程列表
            foreach($cour as $couritem) {
                //获取当前科目的数据以便更新
                $sub = SubjectModel::get($couritem['subject_id']);
                $this->checkStatu($couritem, $sub);
            }
        }
    }
    //开始学习,通过选项判断复习时间
    public function studySubject() 
    {
        if($this->isLogin()){
            if(($sub_id = input('subject_id'))) {
                $sub = SubjectModel::get($sub_id);
                $cour = CourseModel::getByStatu('expire');
                dump($cour);
            } else {
                echo "fail";
            }
        } else {
            return view('login');
        }
        return $this->fetch();
    }
    //判断course的状态
    private function checkStatu($cour, $sub, $op='') 
    {   
        switch($cour->statu) {
            //如果该课程的状态为new,则代表为新增课程
            case('new'): {
                if($sub) {
                    $sub->new += 1;
                    $sub->save();
                } 
                $cour->statu = 'expire';
                $cour->rank = 0;
                $cour->save();
                break;
            }
            //作为一个中间状态等待
            case('expire'): {
                //如果没有需要操作,直接退出!
                if(!$op) {break;}
                $cur_time = strtotime('now'); 
                //第一次学习
                if($cour->rank == 0) {
                    $cour->expire_time = date('Y-m-d H:i:s', $cur_time + $this->incTime($op));                
                    $cour->statu = 'wait';
                    $cour->rank = 1;
                    $sub->new -= 1;
                //后面的学习
                } else {
                    //期待时间是现在时间加上等级+1之和乘以学习程度对应的时间!
                    $cour->expire_time = date('Y-m-d H:i:s', $cur_time + ($cour->rank+1) * $this->incTime($op));                
                    $cour->statu = 'wait';
                    $sub->expire -= 1;
                    if($op == 'easy') {
                        $cour->rank += 1;
                    } 
                }
                $sub->save();
                $cour->save(); 
                break;
            }
            //等待状态,当
            case('wait'): {
                if(strtotime($cour->expire_time)<strtotime('now')) {
                    //expire数量增加1
                    $sub->expire += 1;
                    $sub->save();
                    //更新课程状态为first_expire
                    $cour->statu = 'expire';
                    $cour->save();
                } 
                break;  
            }
        }
    }
    //根据op返回不同的数字
    private function incTime($op) 
    {
        if($op == 'difficult') {
            return 60;
        } else if($op == 'normal') {
            return 3600;
        } else if($op == 'easy') {
            return 3600*24;
        } else {
            return 0;
        }
    }
    
    //test
    public function test()
    {
        $cour = CourseModel::getByStatu('expire');
        dump($cour);
    }
}