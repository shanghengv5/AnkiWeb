<?php
namespace app\user\validate;

use think\Validate;

class AddSubject extends Validate 
{
    protected $rule = [
        ['name' , 'require', '必须填写科目名字'],
        
    ];
}