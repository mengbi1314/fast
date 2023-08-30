define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/chapter/index',
                    add_url: 'subject/chapter/add?courseid=' + Fast.api.query('courseid'),
                    edit_url: 'subject/chapter/edit',
                    del_url: 'subject/chapter/del',
                    multi_url: 'subject/chapter/multi',
                    table: 'subject_chapter',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'createtime',
                queryParams:function (params) {
                    var filter = JSON.parse(params.filter)
                    var courseid = Fast.api.query('courseid')
                    if(courseid) {
                        filter.subid = courseid
                        params.filter = JSON.stringify(filter);
                    }
                    return  params;
                },
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'subid', title: __('ChapterSubid'), visible: false,
                        sortable: false,searchable: false},
                        {field: 'title', title: __('ChapterTitle'), operate: 'LIKE'},
                        {field: 'createtime', title: __('ChapterCeatetime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {
                            field: 'operate', 
                            title: __('Operate'), 
                            table: table, 
                            events: Table.api.events.operate, 
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
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