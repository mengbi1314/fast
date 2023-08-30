define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'depot/back/index' + location.search,
                    add_url: 'depot/back/add',
                    edit_url: 'depot/back/edit',
                    del_url: 'depot/back/del',
                    multi_url: 'depot/back/multi',
                    import_url: 'depot/back/import',
                    table: 'depot_back',
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
                onLoadSuccess:function()
                {
                    $('.btn-editone').data('area',['100%','100%'])

                    // 给添加按钮添加`data-area`属性
                    $(".btn-add").data("area", ["100%", "100%"]);
                },
                columns: [
                    [
                        {
                            checkbox: true,
                            formatter:function(value, row, index){
                                if (row.status==2){
                                    return {disabled: true};
                                }else if(row.status == 3)
                                {
                                    return {disabled: true};
                                }
                            }
                        },
                        {field: 'id', title: __('Id')},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'ordercode', title: __('Ordercode'), operate: 'LIKE'},
                        {field: 'business.nickname', title: __('Busid')},
                        {field: 'contact', title: __('Contact'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"0":__('未审核'),"1":__('已审核，未收货'),"2":__('已收货，未入库'),"3":__('已入库'),"-1":__('审核不通过')}, formatter: Table.api.formatter.status},
                    
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name:'detail',
                                    title:'详情',
                                    classname:'btn btn-xs btn-success btn-dialog',
                                    url:'depot/back/detail',
                                    icon:'fa fa-eye'
                                },
                                {
                                    name:'process',
                                    title:'通过审核',
                                    classname:'btn btn-xs btn-success btn-ajax',
                                    icon:'fa fa-leaf',
                                    confirm:'确认通过审核吗？',
                                    url:'depot/back/process',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible:function(row){
                                        
                                        if(row.status == 0)
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
                                    url:'depot/back/receipt',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible:function(row){
                                        
                                        return row.status == 1 ? true : false
                                    }
                                },
                                {
                                    name:'storage',
                                    title:'确认入库',
                                    classname:'btn btn-xs btn-success btn-ajax',
                                    icon:'fa fa-leaf',
                                    confirm:'确认入库吗？',
                                    url:'depot/back/storage',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible:function(row){
                                        
                                        return row.status == 2 ? true : false
                                    }
                                },
                                {
                                    name:'fail',
                                    title:'未通过审核',
                                    classname:'btn btn-xs btn-info btn-dialog',
                                    icon:'fa fa-exclamation-triangle',
                                    confirm:'确认未通过审核吗？',
                                    url:'depot/back/fail',
                                    visible:function(row){

                                        if(row.status == 0 || row.status == 1)
                                        {
                                            return true
                                        }
                                        return false
                                    }
                                },
                                {
                                    name:'cancel',
                                    title:'撤销审核',
                                    classname:'btn btn-xs btn-danger btn-ajax',
                                    icon:'fa fa-reply',
                                    url:'depot/back/cancel',
                                    confirm:'确认要撤回审核吗？',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    },
                                    visible:function(row){
                                        if(row.status == 1)
                                        {
                                            return true
                                        }

                                        return false
                                    }
                                },
                                {
                                    name:'edit',
                                    title:'编辑',
                                    classname:'btn btn-xs btn-success btn-editone',
                                    icon:'fa fa-pencil',
                                    url:'depot/storage/edit',
                                    visible:function(row){
                                        if(row.status == 2 || row.status == 3)
                                        {
                                            return false
                                        }

                                        return true
                                    }
                                },
                                {
                                    name:'del',
                                    title:'删除',
                                    classname:'btn btn-xs btn-danger btn-delone',
                                    icon:'fa fa-trash',
                                    visible:function(row){
                                        if(row.status == 2 || row.status == 3)
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

            $('#table').bootstrapTable({
                columns:[
                    {
                        field: 'id',
                        title: '主键'
                    },
                    {
                        field: 'name',
                        title: '商品名称',
                    },
                    {
                        field: 'price',
                        title: '商品单价'
                    },
                    {
                        field: 'nums',
                        title: '数量'
                    },
                    {
                        field: 'total',
                        title: '总价'
                    },
                ]
            })

            $('#table').hide()

            $('#busid').click(function(){
                Backend.api.open('depot/back/user','客户',{
                    // area:['80%','80%'],
                    callback:function(data)
                    {

                        $('#busid').val(data.nickname)

                        $('#c-busid').val(data.id)

                        let id = data.id

                        $.ajax({
                            type: "post",
                            url: "depot/back/address",
                            data: {
                                id
                            },
                            dataType: "json",
                            success: function (response) {
                                
                                if(response.code === 1 && response.data)
                                {
                                    let option = ''

                                    for(let item of response.data)
                                    {
                                        option += `<option value="${item.id}">联系人：${item.consignee} 联系方式：${item.mobile} 地址：${item.provinces.name}-${item.citys.name}-${item.districts.name} ${item.address}</option>`
                                    }

                                    $('#addrid').html(option)

                                    // 获取订单号
                                    var code = $('#c-ordercode').val()

                                    if(code)
                                    {
                                        GetOrder(code,id)
                                    }

                                    $('#addrid').selectpicker('refresh');
                                    $('.selectpicker').selectpicker('render');

                                    
                                }
                            }
                        });
                    }
                })
            })

            $('#c-ordercode').change(function(){
                var code = $(this).val()

                var busid = $('#c-busid').val()

                GetOrder(code,busid)
            })

            function GetOrder(code,busid)
            {
                $.ajax({
                    type: "post",
                    url: 'depot/back/order',
                    data: {
                        code,
                        busid
                    },
                    dataType: "json",
                    success: function (res) {

                        if(res.code === 0)
                        {
                            Toastr.error(res.msg)

                            return false
                        }

                        $('#table').show()

                        let tr = ''
                        // SU202211181107373113749

                        for(let item of res.data)
                        {
                            tr += `<tr>`
                            tr += `<td>${item.products.id}</td>`
                            tr += `<td>${item.products.name}</td>`
                            tr += `<td>${item.price}</td>`
                            tr += `<td>${item.pronum}</td>`
                            tr += `<td>${item.total}</td>`
                            tr += `</tr>`
                        }

                        $('#table tbody').html(tr)
                    }
                });
            }

            Controller.api.bindevent();
        },
        edit: function () {

            $('#table').bootstrapTable({
                columns:[
                    {
                        field: 'id',
                        title: '主键'
                    },
                    {
                        field: 'name',
                        title: '商品名称',
                    },
                    {
                        field: 'price',
                        title: '商品单价'
                    },
                    {
                        field: 'nums',
                        title: '数量'
                    },
                    {
                        field: 'total',
                        title: '总价'
                    },
                ]
            })

            var BackProductList = Config.back.BackProductList

            let tr = ''

            for(let item of BackProductList)
            {
                tr += `<tr>`
                tr += `<td>${item.products.id}</td>`
                tr += `<td>${item.products.name}</td>`
                tr += `<td>${item.price}</td>`
                tr += `<td>${item.nums}</td>`
                tr += `<td>${item.total}</td>`
                tr += `</tr>`
            }

            $('#table tbody').html(tr)

            $('#c-ordercode').change(function(){
                var code = $(this).val()

                var busid = $('#c-busid').val()

                GetOrder(code,busid)
            })


            function GetOrder(code,busid)
            {
                $.ajax({
                    type: "post",
                    url: 'depot/back/order',
                    data: {
                        code,
                        busid
                    },
                    dataType: "json",
                    success: function (res) {

                        if(res.code === 0)
                        {
                            Toastr.error(res.msg)

                            return false
                        }

                        $('#table').show()

                        let tr = ''
                        // SU202211181107373113749

                        for(let item of res.data)
                        {
                            tr += `<tr>`
                            tr += `<td>${item.products.id}</td>`
                            tr += `<td>${item.products.name}</td>`
                            tr += `<td>${item.price}</td>`
                            tr += `<td>${item.pronum}</td>`
                            tr += `<td>${item.total}</td>`
                            tr += `</tr>`
                        }

                        $('#table tbody').html(tr)
                    }
                });
            }

            Controller.api.bindevent();
        },
        user:function(){

            // 初始化表格
            Table.api.init({
                extend:{
                    index_url:'depot/back/user',
                }
            })
            
            var userTable = $('#table');

            userTable.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk:'id',
                sortName:'createtime',
                search:false,
                // 关闭导出
                showExport: false,
                // 关闭通用搜索
                commonSearch: false,
                // 关闭显示的列
                showColumns: false,
                // 关闭切换显示
                showToggle: false,
                columns:[
                    [
                        {
                            field:'id',title:__('Id'),
                        },
                        {
                            field:'nickname',title:__('Nickname'),
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: userTable,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'chapter', text: '选择',
                                    title: '选择',
                                    classname: 'btn btn-primary btn-xs user',
                                },
                            ]
                        }
                    ]
                ]
            })

            $('#table').on('click','.user',function(){
                let index = $(this).parents('tr').data('index')

                let data = Table.api.getrowdata(userTable,index)

                Backend.api.close(data)
            })


            // 为表格绑定事件
            Table.api.bindevent(userTable)
        },
        fail:function(){
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
