<?php
namespace app\user\controller;
use think\Controller;
use app\user\model\User as UserModel;
use think\Db;
class Index extends Controller
{
    public function index()
    {
        
        return $this->fetch();
    }


}
