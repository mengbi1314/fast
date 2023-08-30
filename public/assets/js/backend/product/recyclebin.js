define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index:function()
        {
            // 初始化
            Table.api.init();

            // 绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.length > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }

                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });
            
            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        table:{
            product:function()
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

                var table = $("#table1");

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                    pk: 'id',
                    sortName: 'id',
                    toolbar: '#toolbar1',
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
            order:function()
            {
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        recyclebin_url: 'product/order/recyclebin',
                        del_url: 'product/order/destroy',
                        restore_url: 'product/order/restore',
                        table: 'order',
                    }
                });

                var table = $("#table2");

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                    pk: 'id',
                    sortName: 'id',
                    toolbar: '#toolbar2',
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id')},
                            {field: 'code', title: __('Code'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                            {field: 'business.nickname', title: __('Busid'),operate: 'LIKE'},
                            {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                            {field: 'express.name', title: __('Expressid')},
                            {field: 'expresscode', title: __('Expresscode'), operate: 'LIKE', table: table, class: 'autocontent', formatter: function(value){
                                if(!value)
                                {
                                    return '-';
                                }
                                
                                return `<div class="autocontent-item " style="white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:250px;">${value}</div>`
                            }},
                            {field: 'status_text', title: __('Status'), operate: 'LIKE'},
                            {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                            {field: 'deletetime', title: __('Deletetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                            {
                                field: 'operate',
                                width: '140px',
                                title: __('Operate'),
                                table: table,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'Restore',
                                        title: __('Restore'),
                                        classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                        icon: 'fa fa-rotate-left',
                                        url: $.fn.bootstrapTable.defaults.extend.restore_url,
                                        confirm:'确认要还原数据吗？',
                                        success: function (data, ret) {
                                            $(".btn-refresh").trigger("click");
                                        },
                                        refresh: true
                                    }
                                ],
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });

                // 为表格绑定事件
                Table.api.bindevent(table);
            },
            lease:function()
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

                var table = $("#table3");

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                    pk: 'id',
                    sortName: 'id',
                    toolbar: '#toolbar3',
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
                            {field: 'createtime', title: __('租用开始时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
            }
        },
        del: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"))
            },
        },
    }

    return Controller;
})