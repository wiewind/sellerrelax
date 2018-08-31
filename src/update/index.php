<!DOCTYPE html>
<html>
<head>
    <title>System Update</title>
    <link rel="stylesheet" type="text/css" href="css/main.css?_dc=<?= filemtime('css/main.css') ?>" />
    <script type="text/javascript" src="../lib/jquery/jquery-3.1.1.min.js"></script>
</head>
<body>

<div id="main">
    <h2 class="top_title">
        <img src="/resources/images/logo/logo.png" alt="Mextronic">
        项目更新
    </h2>
    <div class="content">
        <div class="demo">
            <div id="drop_area">将项目文件拖拽到此区域</div>
            <div id="preview"></div>
            <div id="result"></div>
        </div>
    </div>
</div>
<div id="modal"></div>

<script type="text/javascript">
    $(function(){
        //阻止浏览器默认行为。
        $(document).on({
            dragleave:function(e){		//拖离
                e.preventDefault();
            },
            drop:function(e){			//拖后放
                e.preventDefault();
            },
            dragenter:function(e){		//拖进
                e.preventDefault();
            },
            dragover:function(e){		//拖来拖去
                e.preventDefault();
            }
        });

        //================上传的实现
        var box = document.getElementById('drop_area'); //拖拽区域


        box.addEventListener("dragenter",function(e){
            box.style.background = '#eeeeee';
        });
        box.addEventListener("dragleave",function(e){
            box.style.background = '#ffffff';
        });

        box.addEventListener("drop",function(e){
            e.preventDefault();
            box.style.background = '#ffffff';

            $("#result").html('');
            var fileList = e.dataTransfer.files; //获取文件对象
            //检测是否是拖拽文件到页面的操作
            if(fileList.length == 0){
                return false;
            }

            var filename = fileList[0].name;
            var filesize = Math.floor((fileList[0].size)/1024);
            var projectname = "SRX";
            var str = "<p>项目名称："+projectname+"</p><p>文件名称："+filename+"</p><p>大小："+filesize+"KB</p>";
            $("#preview").html(str);
            $("#result").html('<img src="images/loading.gif" /> 正在上传中......');
            $('#modal').css({'display':'block'});

            //上传
//            xhr = new XMLHttpRequest();
//            xhr.open("post", "upload.php", true);
//            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

            var fd = new FormData();
            fd.append('fileToUpload', fileList[0]);
            fd.append('project', projectname);

//            xhr.send(fd);


            $.ajax({
                type: "POST",
                url: 'upload.php',
                data: fd,
                xhr: function () {
                    var myXhr = $.ajaxSettings.xhr();
                    return myXhr;
                },
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 60000,
                success: function (data, status, xhr) {
                    var res = JSON.parse(data);
                    if (res.success) {
                        $("#preview").html('');
                        $("#result").html('更新成功！');

                        if (projectname === 'workshop') {
                            // add Version
//                            $.ajax({
//                                type: "POST",
//                                url: '/apps/workshop/api/versions/json/addVersion'
//                            });
                        }
                    } else {
                        $("#result").html('<div class="errMsg">' + res.message + '</div>');
                    }

                    $('#modal').css({'display':'none'});
                },
                error: function (xhr, status, error) {
                    console.log(xhr, status, eror);
                    $("#result").html('<div class="errMsg">发生错误</div>');
                    $('#modal').css({'display':'none'});
                }
            });

        },false);
    });
</script>


</body>
</html>