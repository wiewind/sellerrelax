/**
 * Created by benying.zou on 13.02.2018.
 */
Ext.define('SRX.view.import.itemproperties.GridController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.importitempropertiesgrid',

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
        var grid = Ext.ComponentQuery.query('importitempropertiesgrid')[0],
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
            url: Cake.api.path + '/ImportVariationProperties/json/uploadCsv',
            data: fd,
            timeout: 60000,
            success: function (data, status, xhr) {
                Glb.common.unmask();
                data = Ext.decode(data).data;
                Ext.create('SRX.view.import.itemproperties.SettingWindow', {
                    viewModel: {
                        data: {
                            file: data.file,
                            variationCount: data.variationCount,
                            propertyCount: data.propertyCount,
                            properties: data.properties
                        }
                    }
                });
            },
            complete: function () {
                box.style.padding = 0;
            }
        });

        /*
        Glb.jqAjax({
            url: Cake.api.path + '/ImportVariationProperties/json/importItemPropertiesCsv',
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
        */
    },

    onChangeFilter: function () {
        this.getViewModel().getStore('importstore').reload({page: 1});
    },

    onClickToPlenty: function () {
        var me =this;
        Glb.common.mask(T.__('Please wait...'));
        Glb.Ajax({
            url: Cake.api.path + '/ImportVariationProperties/json/itemProperty2Plenty',
            timeout: 60000,
            success: function (response, options) {
                ABox.info(T.__('Finished!'), function () {
                    me.getViewModel().getStore('importstore').reload();
                });
            }
        });
    },

    onClickRenew: function () {
        var view = this.getView(),
            records = view.getSelectionModel().getSelection();
        if (records.length > 0) {
            ABox.confirm(
                T.__('Do you want to renew the selected import?'),
                function () {
                    var ids = [];
                    for (var i=0; i<records.length; i++) {
                        ids.push(records[i].get('id'));
                    }
                    Glb.Ajax({
                        url: Cake.api.path + '/ImportVariationProperties/json/renew',
                        params: {
                            ids: ids.join(',')
                        },
                        success: function () {
                            view.getStore().reload();
                            view.getSelectionModel().deselectAll();
                        }
                    });
                }
            );
        } else {
            ABox.error(T.__('Please select the records!'));
        }
    },

    onClickReject: function () {
        var view = this.getView(),
            records = view.getSelectionModel().getSelection();
        if (records.length > 0) {
            ABox.confirm(
                T.__('Do you want to reject the selected import?'),
                function () {
                    var ids = [];
                    for (var i=0; i<records.length; i++) {
                        ids.push(records[i].get('id'));
                    }
                    Glb.Ajax({
                        url: Cake.api.path + '/ImportVariationProperties/json/reject',
                        params: {
                            ids: ids.join(',')
                        },
                        success: function () {
                            view.getStore().reload();
                            view.getSelectionModel().deselectAll();
                        }
                    });
                }
            );
        } else {
            ABox.error(T.__('Please select the records!'));
        }
    },

    onClickRejectAll: function () {
        var vm = this.getViewModel();
        ABox.confirm(
            T.__('Do you want to reject all the import?'),
            function () {
                var store = vm.getStore('importstore');
                Glb.Ajax({
                    url: Cake.api.path + '/ImportVariationProperties/json/rejectAll',
                    success: function () {
                        ABox.info(T.__('The records are denied!'));
                    }
                });

                store.loadPage(1);
            }
        );
    },

    onClickUpload: function () {
        ABox.alert(T.__('CSV Upload'), T.__('Drag the csv file into the table to upload it.'));
    },

    onClickDownload: function () {
        Wiewind.Action.click({
            url: '/api/index.php/ImportVariationProperties/exportCsv',
            target: '_blank'
        });
    }
});
