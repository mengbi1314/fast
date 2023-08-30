define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 初始化
            Table.api.init();

            // 绑定事件
            $('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
                var panel = $($(this).attr("href"));
                if (panel.length > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on("click", function (e) {
                        $($(this).attr("href"))
                            .find(".btn-refresh")
                            .trigger("click");
                    });
                }

                //移除绑定的事件
                $(this).unbind("shown.bs.tab");
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger(
                "shown.bs.tab"
            );
        },
        table: {
            storage: function () {
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        recyclebin_url: "depot/storage/recyclebin",
                        del_url: "depot/storage/destroy",
                        restore_url: "depot/storage/restore",
                        table: "storage",
                    },
                });

                var table = $("#table1");

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                    pk: "id",
                    sortName: "id",
                    toolbar: "#toolbar1",
                    columns: [
                        [
                            { checkbox: true },
                            { field: "id", title: __("Id"), operate: "LIKE" },
                            {
                                field: "code",
                                title: __("Code"),
                                operate: "LIKE",
                            },
                            { field: "supplier.name", title: __("Supplierid") },
                            {
                                field: "type",
                                title: __("Type"),
                                searchList: {
                                    1: __("直销入库"),
                                    2: __("退货入库"),
                                },
                                formatter: Table.api.formatter.normal,
                            },
                            {
                                field: "amount",
                                title: __("Amount"),
                                operate: "BETWEEN",
                            },
                            {
                                field: "status",
                                title: __("Status"),
                                searchList: {
                                    0: __("待审批"),
                                    1: __("审批失败"),
                                    2: __("待入库"),
                                    3: __("入库完成"),
                                },
                                formatter: Table.api.formatter.status,
                            },
                            {
                                field: "createtime",
                                title: __("Createtime"),
                                operate: "RANGE",
                                addclass: "datetimerange",
                                autocomplete: false,
                                formatter: Table.api.formatter.datetime,
                            },
                            {
                                field: "deletetime",
                                title: __("Deletetime"),
                                operate: "RANGE",
                                addclass: "datetimerange",
                                autocomplete: false,
                                formatter: Table.api.formatter.datetime,
                            },
                            {
                                field: "operate",
                                title: __("Operate"),
                                table: table,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate,
                                //要在操作这一栏增添自定义的按钮
                                buttons: [
                                    {
                                        name: "restore",
                                        title: "数据恢复",
                                        icon: "fa fa-reply",
                                        classname:
                                            "btn btn-xs btn-success btn-magic btn-ajax",
                                        url: $.fn.bootstrapTable.defaults.extend
                                            .restore_url,
                                        confirm: "是否还原入库单",
                                        extend: "data-toggle='tooltip'",
                                        success: function (data) {
                                            //ajax成功会刷新一下table数据列表
                                            table.bootstrapTable("refresh");
                                        },
                                    },
                                ],
                            },
                        ],
                    ],
                });

                // 为表格绑定事件
                Table.api.bindevent(table);

                //给表格的按钮绑定点击事件
                $(document).on("click", ".btn-restore", function () {
                    // 弹出确认对话框
                    Layer.confirm(
                        __("是否确认还原数据"),
                        { icon: 3, title: __("Warning"), shadeClose: true },
                        function (index) {
                            //获取当前选中id
                            var ids = Table.api.selectedids(table);

                            //发送ajax请求
                            Backend.api.ajax(
                                //请求地址
                                {
                                    url:
                                        $.fn.bootstrapTable.defaults.extend
                                            .restore_url + `?ids=${ids}`,
                                },
                                //回调函数
                                function () {
                                    // 关闭窗口
                                    Layer.close(index);

                                    //刷新数据表格
                                    table.bootstrapTable("refresh");
                                }
                            );
                        }
                    );
                });
            },
            back: function () {
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        recyclebin_url: "depot/back/recyclebin",
                        del_url: "depot/back/destroy",
                        restore_url: "depot/back/restore",
                        table: "back",
                    },
                });

                var table = $("#table2");

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                    pk: "id",
                    sortName: "id",
                    toolbar: "#toolbar2",
                    columns: [
                        [
                            { checkbox: true },
                            { field: "id", title: __("Id") },
                            {
                                field: "code",
                                title: __("Code"),
                                operate: "LIKE",
                            },
                            {
                                field: "ordercode",
                                title: __("Ordercode"),
                                operate: "LIKE",
                            },
                            { field: "business.nickname", title: __("Busid") },
                            {
                                field: "contact",
                                title: __("Contact"),
                                operate: "LIKE",
                            },
                            {
                                field: "phone",
                                title: __("Phone"),
                                operate: "LIKE",
                            },
                            {
                                field: "amount",
                                title: __("Amount"),
                                operate: "BETWEEN",
                            },
                            {
                                field: "createtime",
                                title: __("Createtime"),
                                operate: "RANGE",
                                addclass: "datetimerange",
                                autocomplete: false,
                                formatter: Table.api.formatter.datetime,
                            },
                            {
                                field: "deletetime",
                                title: __("Deletetime"),
                                operate: "RANGE",
                                addclass: "datetimerange",
                                autocomplete: false,
                                formatter: Table.api.formatter.datetime,
                            },
                            {
                                field: "operate",
                                title: __("Operate"),
                                table: table,
                                events: Table.api.events.operate,
                                formatter: Table.api.formatter.operate,
                                //要在操作这一栏增添自定义的按钮
                                buttons: [
                                    {
                                        name: "restore",
                                        title: "数据恢复",
                                        icon: "fa fa-reply",
                                        classname:
                                            "btn btn-xs btn-success btn-magic btn-ajax",
                                        url: $.fn.bootstrapTable.defaults.extend
                                            .restore_url,
                                        confirm: "是否还原退货单",
                                        extend: "data-toggle='tooltip'",
                                        success: function (data) {
                                            //ajax成功会刷新一下table数据列表
                                            table.bootstrapTable("refresh");
                                        },
                                    },
                                ],
                            },
                        ],
                    ],
                });

                //给表格的按钮绑定点击事件
                $(document).on("click", ".btn-restore", function () {
                    // 弹出确认对话框
                    Layer.confirm(
                        __("是否确认还原数据"),
                        { icon: 3, title: __("Warning"), shadeClose: true },
                        function (index) {
                            //获取当前选中id
                            var ids = Table.api.selectedids(table);

                            //发送ajax请求
                            Backend.api.ajax(
                                //请求地址
                                {
                                    url:
                                        $.fn.bootstrapTable.defaults.extend
                                            .restore_url + `?ids=${ids}`,
                                },
                                //回调函数
                                function () {
                                    // 关闭窗口
                                    Layer.close(index);

                                    //刷新数据表格
                                    table.bootstrapTable("refresh");
                                }
                            );
                        }
                    );
                });

                // 为表格绑定事件
                Table.api.bindevent(table);
            },
        },
        del: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        },
    };

    return Controller;
});