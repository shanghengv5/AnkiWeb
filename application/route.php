<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
        'sub_id' => '\d+',
        'course_id' => '\d+',
    ],
    'register' => 'user/user/register',
    'login' => 'user/user/login',
    'logout' => 'user/user/logout',
    'test' => 'user/user/test',
    'addsubject' => 'user/user/addSubject',
    'subject' => 'user/user/subject',
    'updateSubject' => 'user/user/updateSubject',
    'delSubject' => 'user/user/delSubject',
    'addCourse' => 'user/user/addCourse',
    'viewCourse/[:sub_id, :course_id]' => 'user/user/viewCourse',
    'updateCourse' => 'user/user/updateCourse',
];
