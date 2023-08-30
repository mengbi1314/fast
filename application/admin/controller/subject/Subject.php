<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;


/**
 * 课程管理
 *
 * @icon fa fa-circle-o
 */
class Subject  extends Backend
{
    // 开启关联查询
    protected $relationSearch = true;

    /**
     * Subject模型对象
     * @var \app\admin\model\subject\Subject
     */
    protected $model = null;

    // 课程章节模型
    protected $ChapterModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('subject.Subject');
        $this->ChapterModel = model('subject.Chapter');
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    // 查看
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
                ->with(['category'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            $result = ['total' => $list->total(), 'rows' => $list->items()];
            return json($result);
        }

        return $this->fetch();
    }

    // 软删除
    public function del($ids = null)
    {
        $ids = $ids ?: $this->request->param('ids', '', 'trim');

        // 查询数据
        $list = $this->model->select($ids);

        if (!$list) {
            $this->error('课程不存在');
        }

        $result = $this->model->destroy($ids);

        if ($result === false) {
            $this->error('删除失败');
        } else {
            $this->success('删除成功');
        }
    }

    // 回收站
    public function recyclebin()
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
                ->onlyTrashed()
                ->with(['category'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            $result = ['total' => $list->total(), 'rows' => $list->items()];
            return json($result);
        }
    }

    // 还原
    public function restore($ids = null)
    {
        $ids = $ids ?: $this->request->param('ids', '', 'trim');

        // 查询软删除数据
        $list = $this->model->onlyTrashed()->select($ids);

        if (!$list) {
            $this->error('课程不存在');
        }

        $result = $this->model->onlyTrashed()->where(['id' => ['IN', $ids]])->update(['deletetime' => null]);

        if ($result == true) {
            $this->success('还原成功');
        } else {
            $this->error('还原失败');
        }
    }

    // 真实删除
    public function destroy($ids = null)
    {
        $ids = $ids ?: $this->request->param('ids', '', 'trim');

        // 查询软删除数据
        $list = $this->model->onlyTrashed()->select($ids);

        if (!$list) {
            $this->error('课程不存在');
        }

        // 查询选择真实删除课程的章节数据，因为章节视频它不会自动删除
        $ChapterList = $this->ChapterModel->where(['subid' => ['IN', $ids]])->select();

        // 删除课程
        $result = $this->model->destroy($ids, true);

        if ($result === false) {
            $this->error('删除失败');
        } else {
            // 删除课程封面图
            foreach ($list as $item) {
                @is_file(ltrim($item['thumbs'], '/')) && @unlink(ltrim($item['thumbs'], '/'));
            }

            // 删除章节视频
            foreach ($ChapterList as $item) {
                @is_file(ltrim($item['url'], '/')) && @unlink(ltrim($item['url'], '/'));
            }

            $this->success('删除成功');
        }
    }
}
