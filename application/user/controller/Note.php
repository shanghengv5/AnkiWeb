<?php
namespace app\user\controller;
use think\Controller;
use app\user\functions\Data as DataFns;
use app\user\functions\Subject as SubjectFns;
use app\user\functions\Course as CourseFns;
class Note extends Controller
{
    //笔记首页,用于展示科目.
    public function noted($sub_id='')
    {
        $subfns = new SubjectFns;
        if(($username=DataFns::isLogin())) {
            // 如果传入参数
            if($sub_id) {
                // 获取sub_id参数,展示知识点列表
                $courfns = new CourseFns;
                $list = $courfns->getCourseList($sub_id);
                $this->assign('courselist', $list);
                return $this->fetch('view_note');
            }
            // 当没有传入参数时,显示科目列表.
            $subjectlist = $subfns->getSubjectList($username);
            if($subjectlist) {
                $this->assign('subjectlist', $subjectlist);
                return $this->fetch();
            } else {
                $msg = "你还未有科目!";
                $type = "warning";
                $action = "append";
                return view('Study/add_subject', ['type'=>$type, 'msg'=>$msg, 'action'=>$action]);
            }
        } else {
            return view('User/login');
        }
    }
}
?>