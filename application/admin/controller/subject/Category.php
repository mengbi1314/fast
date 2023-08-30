<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;

class Category extends Backend
{
    // 当前对象的模型
    protected $model = null;

    public function __construct()
    {
        parent::__construct();

        // 加载模型
        $this->model = model('subject.Category');
    }

    public function index()
    {
        // 设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);

        if ($this->request->isAjax()) {
            // 如果发送的来源是 Selectpage，则转发到 Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            [$where, $sort, $order, $offset, $limit] = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            $result = ['total' => $list->total(), 'rows' => $list->items()];
            return json($result);
        }

        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param('row/a');

            $result = $this->model->validate('common/subject/Category')->save($params);

            if ($result === false) {
                $this->error($this->model->getError());
            } else {
                $this->success();
            }
        }

        $MaxWeight = $this->model->max('weight');

        $weight = $MaxWeight ? $MaxWeight + 1 : 1;

        $this->assign([
            'weight' => $weight
        ]);

        return $this->fetch();
    }

    public function edit($ids = null)
    {
        $ids = $ids ?: $this->request->param('ids', 0, 'trim');

        $row = $this->model->find($ids);

        if (!$row) {
            $this->error('课程分类不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->param('row/a');

            $params['id'] = $ids;

            $result = $this->model->validate('common/subject/Category')->isUpdate(true)->save($params);

            if ($result === false) {
                $this->error($this->model->getError());
            } else {
                $this->success();
            }
        }

        $this->assign([
            'row' => $row
        ]);

        return $this->fetch();
    }

    public function del($ids = null)
    {
        $ids = $ids ?: $this->request->param('ids', 0, 'trim');

        $list = $this->model->select($ids);

        if (!$list) {
            $this->error('课程不存在');
        }

        $result = $this->model->destroy($ids);

        if ($result === false) {
            $this->error();
        } else {
            $this->success();
        }
    }
}
