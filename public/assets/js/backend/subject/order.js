define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/order/index',
                    del_url: 'subject/order/del',
                    multi_url: 'subject/order/multi',
                    table: 'subject_order',
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
                        { checkbox: true },
                        { field: 'id', title: __('Id'), sortable: false },
                        {
                            field: 'subid', title: __('Subid'), visible: false,
                            sortable: false, searchable: false
                        },
                        { field: 'subject.title', title: __('Stitle'), operate: 'LIKE'},
                        {
                            field: 'busid', title: __('Busid'), visible: false,
                            sortable: false, searchable: false
                        },
                        { field: 'business.nickname', title: __('Bnickname'), operate: 'LIKE'},
                        { field: 'code', title: __('OCode'),  operate: 'LIKE'},
                        { field: 'total', title: __('Total'), operate: 'LIKE' },
                        { field: 'createtime', title: __('Ceatetime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        del: function () {
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