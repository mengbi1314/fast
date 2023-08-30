define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/lease/index' + location.search,
                    add_url: 'product/lease/add',
                    edit_url: 'product/lease/edit',
                    del_url: 'product/lease/del',
                    multi_url: 'product/lease/multi',
                    import_url: 'product/lease/import',
                    table: 'lease',
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
                        {
                            checkbox: true,
                            formatter:function(value, row, index){
                                if (row.status == 5){
                                    return {disabled: true};
                                }else if(row.status == 6)
                                {
                                    return {disabled: true};
                                }
                            }
                        },
                        {field: 'id', title: __('Id')},
                        {field: 'business.nickname', title: __('BusinessNcikName')},
                        {field: 'product.name', title: __('ProductName')},
                        {field: 'rent', title: __('Rent'), operate:'BETWEEN'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'express.name', title: __('ExpName')},
                        {field: 'expcode', title: __('Expcode'), operate: 'LIKE'},
                        {field: 'busexp.name', title: __('BusexpName')},
                        {field: 'busexpcode', title: __('Busexpcode'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"1":__('已下单'),"2":__('已发货'),"3":__('已收货'),"4":__('已归还'),"5":__('已退押金'),"6":__('已完成')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name:'detail',
                                    title:'详情',
                                    classname:'btn btn-xs btn-success btn-dialog',
                                    url:'product/lease/detail',
                                    icon:'fa fa-eye'
                                },
                                {
                                    name:'deliver',
                                    title:'发货',
                                    classname:'btn btn-xs btn-success btn-dialog',
                                    url:'product/lease/deliver',
                                    icon:'fa fa-leaf',
                                    visible:function(row){
                                        
                                        if(row.status == 1 || row.status == 2)
                                        {
                                            return true
                                        }
                                        return false
                                    }
                                },
                                {
                                    name:'receipt',
                                    title:'确认收货',
                                    classname:'btn btn-xs btn-success btn-ajax',
                                    icon:'fa fa-leaf',
                                    confirm:'确认收货吗？',
                                    url:'product/lease/receipt',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible:function(row){
                                        
                                        return row.status == 4 ? true : false
                                    }
                                },
                                {
                                    name:'del',
                                    title:'删除',
                                    classname:'btn btn-xs btn-danger btn-delone',
                                    icon:'fa fa-trash',
                                    visible:function(row){
                                        if(row.status == 5 || row.status == 6)
                                        {
                                            return false
                                        }

                                        return true
                                    }
                                }
                            ]
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
        // 发货
        deliver: function()
        {
            Controller.api.bindevent();
        },
        recyclebin:function()
        {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    recyclebin_url: 'product/lease/recyclebin',
                    del_url: 'product/lease/destroy',
                    restore_url: 'product/lease/restore',
                    table: 'lease',
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
                        {field: 'business.nickname', title: __('BusinessNcikName')},
                        {field: 'product.name', title: __('ProductName')},
                        {field: 'rent', title: __('Rent'), operate:'BETWEEN'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'express.name', title: __('ExpName')},
                        {field: 'expcode', title: __('Expcode'), operate: 'LIKE'},
                        {field: 'busexp.name', title: __('BusexpName')},
                        {field: 'busexpcode', title: __('Busexpcode'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"1":__('已下单'),"2":__('已发货'),"3":__('已收货'),"4":__('已归还'),"5":__('已退押金'),"6":__('已完成')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
                                    confirm: '是否还原退货单',
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
            })

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


            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
