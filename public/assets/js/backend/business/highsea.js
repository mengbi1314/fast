define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/highsea/index',
                    aapply_url: 'business/highsea/apply',
                    recovery_url: 'business/highsea/recovery',
                    del_url: 'business/highsea/del',
                    multi_url: 'business/highsea/multi',
                    table: 'business',
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
                        { field: 'id', title: __('ID'), sortable: true },
                        { field: 'nickname', title: __('Bnickname'), operate: 'LIKE' },
                        { field: 'source.name', title: __('SName'), operate: 'LIKE' },
                        { field: 'sex_text', title: __('BsexText'), sortable: false, searchable: false },
                        { field: 'deal', title: __('BdealText'), searchList: { "0": __('未成交'), "1": __('已成交') }, formatter: Table.api.formatter.normal },
                        { field: 'auth', title: __('AuthStatus'), searchList: { "0": __('未认证'), "1": __('已认证') }, formatter: Table.api.formatter.normal }, 
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'apply', 
                                    icon: 'fa fa-arrow-down', 
                                    confirm: '确定要领取吗', 
                                    title: '领取',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    url: 'business/highsea/apply?ids={id}',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    }
                                },
                                {
                                    name: 'recovery',
                                    title: '分配',
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-success btn-xs btn-dialog',
                                    icon: 'fa fa-arrows-h',
                                    url: 'business/highsea/recovery?ids={id}',
                                }
                            ]
                        }
                    ]
                ]
            });

            // 领取
            $('.btn-process-1').on('click', function () {
                let ids = Table.api.selectedids(table);
                ids = ids.toString()
                layer.confirm('确定要领取吗?', { title: '领取', btn: ['是', '否'] },
                    function (index) {
                        $.post("business/highsea/apply", { ids: ids}, function (response) {
                            if (response.code == 1) {
                                Toastr.success(response.msg)
                                $(".btn-refresh").trigger('click');
                            } else {
                                Toastr.error(response.msg)
                            }
                        }, 'json');

                        layer.close(index)
                    }
                );

            });

            // 分配
            $('.btn-process-2').on('click', function () {
                let ids = Table.api.selectedids(table);
                ids = ids.toString()
                Fast.api.open($.fn.bootstrapTable.defaults.extend.recovery_url + "?ids=" + ids, '分配')
            });

        
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        
        apply: function () {
            Controller.api.bindevent();
        },
        recovery: function () {
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