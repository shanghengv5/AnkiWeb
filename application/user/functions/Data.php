<?php
namespace app\user\functions;
use app\user\model\Course as CourseModel;
use app\user\model\Subject as SubjectModel;
use app\user\model\User as UserModel;
class Data
{
    //判断是否登录,如果登录则返回用户名.
    public static function isLogin() 
    {
        if(($username = session('valid_user'))) {
            return $username;
        } else {
            return false;
        }
    }
    //判断表中是否已经存在这条数据
    public static function isExist($instance, $column ,$name, $id, $idname) 
    {
        $res = $instance->where($id, $idname)->where($column, $name)->count();
        if($res >= 1) {
            return $res;
        } else {
            return $res;
        }
    }
    //通过用户名得到用户id
    public static function getIdByUsername($username) 
    {
        $user = UserModel::getByUsername($username);
        return $user['user_id'];
    }
}