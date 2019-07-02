<?php
namespace app\user\validate;
use think\Validate;

class Course extends Validate
{
    protected $rule = [
        ['name', 'require', '必须填写知识点'],
        ['content', 'require', '必须填写答案'],
    ];
}