<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommonController {
    //主页
    public function index(){
        $this->display();
    }

    //左侧栏
    public function left() {
        $this->display();
    }

    //顶部栏
    public function top() {
        $this->display();
    }

    //首页
    public function main() {
        $this->display();
    }
}