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
        'op' => '\w+',
        'id' => '\d+',
    ],
    'register' => 'user/user/register',
    'login' => 'user/user/login',
    'logout' => 'user/user/logout',
    'test' => 'user/user/test',
    'addSubject' => 'user/study/addSubject',
    'subject' => 'user/study/subject',
    'index' => 'user/study/index',
    'addCourse' => 'user/study/addCourse',
    'viewCourse/[:sub_id, :course_id]' => 'user/study/viewCourse',
    'studySubject/[:sub_id, :course_id, :op]' => 'user/study/studySubject',
    'noted/[:sub_id]' => 'user/note/noted',
    
];
