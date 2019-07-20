<?php
namespace app\user\functions;
use app\user\model\Course as CourseModel;
use app\user\model\Subject as SubjectModel;
use app\user\functions\Subject as SubjectFns;
class Course 
{
    
    //查找状态为expire的course
    public function getExpireCourse($id)
    {
        $cour = CourseModel::getByStatu('expire');
        if($cour && ($cour->subject_id == $id)) {
            return $cour;
        } else {
            return false;
        }
    }
    //查询课程情况
    public function checkCourse($id)
     {
        $subfns = new SubjectFns;
        $cour = $this->getCourseList($id);
        if($cour) {
            //获取这个科目的课程列表
            foreach($cour as $couritem) {
                //获取当前科目的数据以便更新
                $sub = SubjectModel::get($couritem['subject_id']);
                $subfns->checkStatu($couritem, $sub);
            }
        }
    }
    //删除知识点
    public function delCourse() 
    {
        $subfns = new SubjectFns;
        $course = CourseModel::get(input('course_id'));
        $sub = SubjectModel::get($course['subject_id']);
        $subfns->checkStatu($course, $sub, 'delete');
        if($course) {
            $course->delete();
        }
    }

    //更新课程内容
    public function updateCourse()
    {
        $course = CourseModel::get(input('course_id'));
        if($course) {
            $course->name = input('name');
            $course->content = input('content');
            $course->save();
        } else {
            return $course->getError();
        }
    }
    //得到course的list
    public function getCourseList($value)
     {
        $course = new CourseModel;
        $list = $course->where('subject_id', $value)->select();
        return $list;
    }
    //通过course_id得到course的相关数据
    public function getCourse($id) 
    {
        $res = CourseModel::get($id);
        return $res;
    }
}