define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/privatesea/index',
                    add_url: 'business/privatesea/add',
                    edit_url: 'business/privatesea/edit',
                    del_url: 'business/privatesea/del',
                    multi_url: 'business/privatesea/multi',
                    table: 'business',
                }
            });

            var table = $("#table");


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'business.createtime',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id'), sortable: true },
                        { field: 'nickname', title: __('Bnickname'), operate: 'LIKE' },
                        { field: 'mobile',title:__('Mobile'),operate: 'LIKE'},
                        { field: 'email',title:__('Email'),operate: 'LIKE'},
                        { field: 'money', title: __('Bmoney'), operate: 'LIKE' },
                        { field: 'source.name', title: __('SName'), operate: 'LIKE' },
                        { field: 'sex_text', title: __('BsexText'), sortable: false, searchable: false },
                        { field: 'deal', title: __('BdealText'), searchList: { "0": __('未成交'), "1": __('已成交') }, formatter: Table.api.formatter.normal },
                        { field: 'auth', title: __('AuthStatus'), searchList: { "0": __('未认证'), "1": __('已认证') }, formatter: Table.api.formatter.normal },
                        { field: 'admin.username', title: __('Ausername'), operate: 'LIKE' },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'business',
                                    title: '客户详情',
                                    extend: 'data-toggle="tooltip"',
                                    classname: "btn btn-xs btn-primary btn-dialog vivst_test",
                                    icon: 'fa fa-eye',
                                    url: 'business/privateseainfo/index?ids={id}',
                                },
                                {
                                    name: 'recovery',  confirm: '确定要回收吗', title: '客户回收',
                                    extend: 'data-toggle="tooltip"',
                                    icon: 'fa fa-recycle',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    url: 'business/privatesea/recovery?ids={id}',
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
                ]
            });

               // 回收，确认框的方法
               $('.btn-reduction').on('click', function () {
                let ids = Table.api.selectedids(table);
                ids = ids.toString()
                layer.confirm('确定要回收吗?', { title: '回收', btn: ['是', '否'] },
                    function (index) {
                        
                        $.post("business/privatesea/recovery", { ids: ids, action: 'success', reply: '' }, function (response) {
                            if (response.code == 1) {
                                Toastr.success(response.msg)
                                $(".btn-refresh").trigger('click');
                            } else {
                                Toastr.error(response.msg)
                            }
                        }, 'json');

                        layer.close(index);
                    }
                );

            });
        
            // 为表格绑定事件
            Table.api.bindevent(table);
            

        },
        add: function () {

            // 选中地区事件处理
            $('#region').on('cp:updated',function(){
                var citypicker = $(this).data("citypicker");
                var code = citypicker.getCode("district") || citypicker.getCode("city") || citypicker.getCode("province");
                $("#region-code").val(code);
            })

            Controller.api.bindevent();
        },
        edit: function () {
            // 选中地区事件处理
            $('#region').on('cp:updated',function(){
                var citypicker = $(this).data("citypicker");
                var code = citypicker.getCode("district") || citypicker.getCode("city") || citypicker.getCode("province");
                $("#region-code").val(code);
            })

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