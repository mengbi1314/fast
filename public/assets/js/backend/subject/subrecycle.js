define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/subrecycle/index',
                    del_url: 'subject/subrecycle/del',
                    multi_url: 'subject/subrecycle/multi',  // 表格的复选框
                    table: 'subject',  // 课程表
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
                        { field: 'id', title: __('Id'), searchable: true },
                        {
                            field: 'category.name', title: __('Cname'), operate: 'LIKE',
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
                        { field: 'title', title: __('Stitle'), operate: 'LIKE' },
                        { field: 'thumbs_cdn', title: __('SthumbsCdn'), searchable: false, formatter: Table.api.formatter.image },
                        { field: 'price', title: __('Sprice'), operate: 'LIKE' },
                        { field: 'like_count', title: __('SlikeCount'), searchable: false },
                        { field: 'createtime', title: __('Screatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'order',
                                    title: '还原',
                                    icon: 'fa fa-reply',
                                    confirm: '确定要还原吗',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    url: 'subject/subrecycle/reduction?ids={id}',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    }
                                }
                            ]
                        }
                    ]
                ],
            });

            
            // 还原，确认框的方法
            $('.btn-reduction').on('click', function () {
                let ids = Table.api.selectedids(table);
                
                layer.confirm('确定要还原吗?', { title: '还原', btn: ['是', '否'] },
                    function (index) {
                        //发送ajax请求
                        Backend.api.ajax(
                            //请求地址
                            {url:$.fn.bootstrapTable.defaults.extend.restore_url + `?ids=${ids}`},
                            //回调函数
                            function()
                            {
                                // 关闭窗口
                                Layer.close(index)

                                //刷新数据表格
                                table.bootstrapTable('refresh')
                            }
                        );
                    }
                );

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
