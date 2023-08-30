define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/order/index' + location.search,
                    del_url: 'product/order/del',
                    multi_url: 'product/order/multi',
                    import_url: 'product/order/import',
                    table: 'order',
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
                            checkbox: true
                        },
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

                        {field: 'status', title: __('Status'),searchList: {"0":__('未支付'),"1":__('已支付'),"2":__('已发货'),"3":__('已收货')}, operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'info',
                                    title: '订单详情',
                                    extend: 'data-toggle="tooltip"',
                                    classname: "btn btn-xs btn-primary btn-dialog",
                                    icon: 'fa fa-eye',
                                    url: 'product/order/info?ids={id}',
                                },
                                {
                                    name:'deliver',
                                    title:'发货',
                                    classname:'btn btn-xs btn-success btn-dialog',
                                    url:'product/order/deliver',
                                    icon:'fa fa-leaf',
                                    visible:function(row){
                                        
                                        if(row.status == 1 || row.status == 2)
                                        {
                                            return true
                                        }
                                        return false
                                    }
                                },
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
        deliver:function()
        {
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
