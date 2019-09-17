/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.variationsuppliers.GridController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.importvariationsuppliersgrid',

    afterRender: function () {
        $(document).on({
            dragleave:function(e){	//拖离
                e.preventDefault();
            },
            drop:function(e){  //拖后放
                e.preventDefault();
            },
            dragenter:function(e){	//拖进
                e.preventDefault();
            },
            dragover:function(e){	//拖来拖去
                e.preventDefault();
            }
        });

        var box = this.getView().getEl().dom;
        box.style.background = 'red';
        box.addEventListener("dragenter",function(e){
            box.style.padding = '10px';
        });
        box.addEventListener("dragleave",function(e){
            box.style.padding = 0;
        });

        box.addEventListener("drop", this.onDrop, false);
    },

    onDrop: function (e) {
        e.preventDefault();
        var grid = Ext.ComponentQuery.query('importvariationsuppliersgrid')[0],
            box = grid.getEl().dom,
            files = e.dataTransfer.files;

        if(files.length != 1) {
            ABox.error(T.__('You may upload only one file.'));
            return false;
        }

        var filename = files[0].name,
            size = files[0].size,
            message = '',
            suffix = Wiewind.File.getFileSuffix(filename);

        if (suffix !== 'csv') {
            ABox.error(T.__('You may upload only csv file.'));
            return false;
        }

        Glb.common.mask(Wiewind.String.sprintf(T.__('Upload file: %s <br /> filesize: %d'), filename, size));

        var fd = new FormData();
        fd.append('fileToUpload', files[0]);

        Glb.jqAjax({
            url: Cake.api.path + '/ImportVariationSuppliers/json/importCsv',
            data: fd,
            timeout: 60000,
            success: function (data, status, xhr) {
                Glb.common.unmask();
                var res = Ext.decode(data);
                ABox.info(Wiewind.String.sprintf(T.__('%s record(s) are imported!'), res.data));
                grid.getStore().loadPage(1);
            },
            complete: function () {
                box.style.padding = 0;
            }
        });
    },

    onChangeFilter: function () {
        this.getViewModel().getStore('importstore').reload({page: 1});
    },

    onClickUpload: function () {
        ABox.alert(T.__('CSV Upload'), T.__('Drag the csv file into the table to upload it.'));
    },

    onClickToPlenty: function () {
        var me =this;
        Glb.common.mask(T.__('Please wait...'));
        Glb.Ajax({
            url: Cake.api.path + '/ImportVariationSuppliers/json/import2Plenty',
            timeout: 60000,
            success: function (response, options) {
                ABox.info(T.__('Finished!'), function () {
                    me.getViewModel().getStore('importstore').reload();
                });
            }
        });
    }
});