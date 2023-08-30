define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/subject/index',
                    add_url: 'subject/subject/add',
                    edit_url: 'subject/subject/edit',
                    del_url: 'subject/subject/del',
                    multi_url: 'subject/subject/multi',
                    table: 'subject',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'createtime',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id'), sortable: false },
                        { field: 'category.name', title: __('CateName'), operate: 'LIKE' },
                        {
                            field: 'title', title: __('Titles'), operate: 'LIKE',
                            formatter: function (value) {
                                return "<span style='display: block;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;' title='" + value + "'>" + value + "</span>";

                                /*
                                    // 过滤掉标签
                                    return val.replace(/<.+?>|&.+?;/g, '');
                                */ 
                            },
                            // 固定列最大宽度，超出隐藏
                            cellStyle: function (value, row, index, field) {
                                return {
                                    css: {
                                        "white-space": "nowrap",
                                        "text-overflow": "ellipsis",
                                        "overflow": "hidden",
                                        "max-width": "200px"
                                    }
                                };
                            }
                        },
                        { field: 'thumbs', title: __('Thumbs'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false },
                        { field: 'price', title: __('Price'), operate: 'LIKE' },
                        { field: 'likes_text', title: __('Likes'), searchable: false },
                        { field: 'createtime', title: __('Ceatetime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'chapter', 
                                    title: '课程章节',
                                    classname: 'btn btn-info btn-xs btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'subject/chapter/index?courseid={id}',
                                },

                            ]
                        }
                    ]
                ],
                escape:false,
                // search:false,
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