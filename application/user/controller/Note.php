<?php
namespace app\user\controller;
use think\Controller;
use app\user\functions\Data as DataFns;
use app\user\functions\Subject as SubjectFns;
use app\user\functions\Course as CourseFns;


class Note extends Controller
{
    //笔记首页,用于展示科目.
    public function note()
    {
        $subfns = new SubjectFns;
        if(($username=DataFns::isLogin())) {
            $subjectlist = $subfns->getSubjectList($username);
            if($subjectlist) {
                $this->assign('subjectlist', $subjectlist);
                return $this->fetch();
            } else {
                return view('user/add_subject');
            }
        } else {
            return view('user/login');
        }
    }
    //用于观看每个科目内的笔记
    public function viewNote($sub_id='') 
    {

        return $this->fetch();
    }


}
