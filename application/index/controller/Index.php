<?php
namespace app\index\controller;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
        return 'hello, X-tie!';
    }
    public function hello()
    {
        $name = "Hello, X-tie";
        $this->assign('name', $name);
        return $this.fetch();
    }
}
