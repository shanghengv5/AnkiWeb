<?php
namespace app\user\validate;

use think\Validate;

class Login extends Validate 
{
    protected $rule = [
        ['username' , 'require|min:5', '請輸入用戶名|请输入正确的用户名'],
        ['password' , 'require|min:6', '请输入最少6位密码'],
        
    ];
    
}