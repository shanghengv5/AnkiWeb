<?php
namespace app\user\controller;
use think\Controller;
use app\user\model\User as UserModel;

class User extends Controller
{
    public function registerForm()
    {
        return view('user/register');
    }

    public function register()
    {
        $user = new UserModel;
        if($user->save(input('post.'))) {
            return 'register success';
        } else {
            return $user->geterror();
        }
        
    }


}