<?php
namespace app\user\functions;
use app\user\model\Course as CourseModel;
use app\user\model\Subject as SubjectModel;
use app\user\functions\Data as DataFns;
use app\user\functions\Course as CourseFns;

class Subject 
{
    //删除已有的科目
    public function delSubject() 
    {
        //从表单得到id并且传入模型
        $id = input('subject_id');
        $sub = SubjectModel::get($id);
        $course = CourseModel::getBySubjectId($id);
        //如果id存在,则删除
        if($sub) {
            if($course) {
                $course->delete();
            }
            $sub->delete();
        } else {
            return $course->getError();
        }
    }
    //通过用户名获取科目列表
    public function getSubjectList($username) 
    {
        $user_id = DataFns::getIdByUsername($username);
        $sub = new SubjectModel;
        $list = $sub->where('user_id', $user_id)->select();
        return $list;
    }
    //更新科目
    public function updateSubject($username)
    {
        //从表单得到id并且传入模型
        $id = input('subject_id');
        $sub = SubjectModel::get($id);
        if(!DataFns::isExist($sub, 'name', input('name'), 'user_id', DataFns::getIdByUsername($username))) {
            if($sub) {
                $sub->name = input('name');
                $sub->save();
            } else {
                return $sub->getError();
            }
        } else {
            echo "重命名不应该重复!";   
        }
        
    }
    //判断course的状态
    public function checkStatu($cour, $sub, $op='') 
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
            //等待状态
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
    //查询用户科目情况
    public function checkSubject($username) 
    {
        $courfns = new CourseFns;
        $sub = $this->getSubjectList($username);
        if($sub) {
            //获取科目列表,并且检查其中course
            foreach($sub as $subitem) {
                $courfns->checkCourse($subitem['subject_id']);
            }
        }
    }
}
