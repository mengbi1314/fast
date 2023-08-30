define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/ordrecycle/index',
                    del_url: 'subject/ordrecycle/del',
                    red_url: 'subject/ordrecycle/reduction',
                    multi_url: 'subject/ordrecycle/multi',  // 表格的复选框
                    table: 'subject_order',  // 分类表
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'order.createtime',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),searchable: true},
                        {field: 'subject.title', title: __('Stitle'),operate: 'LIKE'},
                        {field: 'user.nickname', title: __('Unickname'),operate: 'LIKE'},
                        {field: 'code', title: __('Ocode'),operate: 'LIKE'},
                        {field: 'total', title: __('Ototal'), operate: 'LIKE'},
                        // 日期搜索格式化
                        {field: 'createtime', title: __('Ocreatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                        buttons: [
                            {
                                name: 'order',
                                title: '还原',
                                icon: 'fa fa-reply',
                                confirm: '确定要还原吗',
                                classname: 'btn btn-xs btn-success btn-ajax',
                                url: 'subject/ordrecycle/reduction?ids={id}',
                                success: function (data, ret) {
                                    $(".btn-refresh").trigger("click");
                                },
                                error: function (err) {
                                    console.log(err);
                                }
                            },
                        ]
                    }
                    ]
                ],
            });

            // 还原，确认框的方法
            $('.btn-reduction').on('click', function () {
                let ids = Table.api.selectedids(table);
                ids = ids.toString()
                layer.confirm('确定要还原吗?', { title: '还原', btn: ['是', '否'] },
                    function (index) {
                        layer.close(index);
                        $.post("subject/ordrecycle/reduction", { ids: ids, action: 'success', reply: '' }, function (response) {
                            if (response.code == 1) {
                                Toastr.success(response.msg)
                                $(".btn-refresh").trigger('click');
                            } else {
                                Toastr.error(response.msg)
                            }
                        }, 'json')
                    },
                    function (index) {
                        layer.close(index);
                    }
                );

            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        del: function () {
            Controller.api.bindevent();
        },
        red: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
