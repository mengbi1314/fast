define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'depot/supplier/index' + location.search,
                    add_url: 'depot/supplier/add',
                    edit_url: 'depot/supplier/edit',
                    del_url: 'depot/supplier/del',
                    multi_url: 'depot/supplier/multi',
                    import_url: 'depot/supplier/import',
                    table: 'depot_supplier',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'provinces.name', title: __('Province'), operate: 'LIKE'},
                        {field: 'citys.name', title: __('City'), operate: 'LIKE'},
                        {field: 'districts.name', title: __('District'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {

            $("#c-Region").on("cp:updated", function() {
                var citypicker = $(this).data("citypicker");
                // console.log(citypicker)
                var code = citypicker.getCode("district") || citypicker.getCode("city") || citypicker.getCode("province");
                $("#code").val(code);
            });

            Controller.api.bindevent();
        },
        edit: function () {

            $("#c-Region").on("cp:updated", function() {
                var citypicker = $(this).data("citypicker");
                // console.log(citypicker)
                var code = citypicker.getCode("district") || citypicker.getCode("city") || citypicker.getCode("province");
                $("#code").val(code);
            });
            
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
