/**
 * Created by benying.zou on 05.09.2018.
 */
Ext.define('SRX.view.export.skuarticles.UploadPanelController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.exportskuarticlesuploadpanel',

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

        var box = document.getElementById('drop_area');
        box.addEventListener("dragenter",function(e){
            box.style.background = '#eeeeee';
        });
        box.addEventListener("dragleave",function(e){
            box.style.background = 'transparent';
        });

        box.addEventListener("drop", this.onDrop, false);
    },

    onDrop: function (e) {
        e.preventDefault();
        var window = Ext.ComponentQuery.query('exportskuarticleswindow')[0],
            box = document.getElementById('drop_area'),
            files = e.dataTransfer.files,
            removeOldData = window.down('checkbox[name="removeOldData"]').getValue();


        box.style.background = 'transparent';

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

        Glb.common.mask(Wiewind.String.sprintf(T.__('Upload file: %s <br /> filesize: %d'), filename, size), window);

        var fd = new FormData();
        fd.append('fileToUpload', files[0]);
        fd.append('removeOldData', removeOldData ? 1 : 0);

        Glb.jqAjax({
            url: Cake.api.path + '/ExportSettings/json/importSkuArticles',
            data: fd,
            timeout: 60000,
            success: function (data, status, xhr) {
                Glb.common.unmask(window);
                var res = Ext.decode(data);
                ABox.info(Wiewind.String.sprintf(T.__('%s record(s) are imported!'), res.data));
                window.down('grid').getStore().loadPage(1);
            }
        });
    }
});