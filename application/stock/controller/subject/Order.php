<?php

namespace app\stock\controller\subject;

// 框架基础控制类
use think\Controller;

class Order extends Controller
{
    /**
     * 当前控制器下的一个模型属性
     * 
     */
    // 初始化

    // 管理员模型
    protected $SubjectOrderModel = null;
    public function _initialize()
    {
        parent::_initialize();
        // 引入的模型
        $this->SubjectOrderModel = model('Subject.Order');
    }

    // 课程订单列表
    public function index()
    {
        $result = $this->SubjectOrderModel->with(['subject', 'business'])->select();
        $this->success('', '', $result);
    }

    // 删除
    public function del()
    {
        $id = $this->request->param('id', 0, 'trim');

        $row = $this->SubjectOrderModel->column('id');

        if (!in_array($id, $row)) {
            $this->error(__('没有找到该课程订单'));
            exit;
        }

        $result = $this->SubjectOrderModel->destroy($id);

        if ($result === false) {
            $this->error($this->SubjectOrderModel->getError());
            exit;
        }

        $this->success('课程订单删除成功！');
        exit;
    }
}
