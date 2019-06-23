<?php
namespace app\index\controller;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
        return 'hello, X-tie!';
    }
    
    public function hello($name = 'thinkphp')
    {
    $this->assign('name', $name);
    return $this->fetch();
    }

    public function say() {
        return 'hello0';
    }
}
