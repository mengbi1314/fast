define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/product/index' + location.search,
                    add_url: 'product/product/add',
                    edit_url: 'product/product/edit',
                    del_url: 'product/product/del',
                    multi_url: 'product/product/multi',
                    import_url: 'product/product/import',
                    table: 'product',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('下架'),"1":__('上架')}, formatter: Table.api.formatter.flag},
                        {field: 'flag', title: __('Flag'), searchList: {"1":__('新品'),"2":__('热销'),"3":__('推荐')}, formatter: Table.api.formatter.flag},
                        {field: 'stock', title: __('Stock')},
                        {field: 'price', title: __('Price')},
                        {field: 'type.name', title: __('Typeid')},
                        {field: 'unit.name', title: __('Unitid')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
        recyclebin: function ()
        {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    recyclebin_url: 'product/product/recyclebin',
                    del_url: 'product/product/destroy',
                    restore_url: 'product/product/restore',
                    table: 'product',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'flag', title: __('Flag'), searchList: {"0":__('下架'),"1":__('上架')}, formatter: Table.api.formatter.flag},
                        {field: 'stock', title: __('Stock')},
                        {field: 'type.name', title: __('Typeid')},
                        {field: 'unit.name', title: __('Unitid')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'deletetime', title: __('Deletetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'), 
                            table: table, 
                            events: Table.api.events.operate, 
                            formatter: Table.api.formatter.operate,
                            //要在操作这一栏增添自定义的按钮
                            buttons:[
                                {
                                    name:'restore',
                                    title:'数据恢复',
                                    icon:'fa fa-reply',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url:$.fn.bootstrapTable.defaults.extend.restore_url,
                                    confirm: '是否还原该商品？',
                                    extend:"data-toggle='tooltip'",
                                    success:function(data)
                                    {
                                        //ajax成功会刷新一下table数据列表
                                        table.bootstrapTable('refresh');
                                    }
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //给表格的按钮绑定点击事件
            $(document).on('click', '.btn-restore', function(){
                // 弹出确认对话框
                Layer.confirm(__('是否确认还原数据'), { icon: 3, title: __('Warning'), shadeClose: true }, function(index){
                    //获取当前选中id
                    var ids = Table.api.selectedids(table)

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
                    )
                })
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
