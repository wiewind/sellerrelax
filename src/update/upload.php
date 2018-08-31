<?php
/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 21.02.2018
 * Time: 16:53
 */
function doSubmit () {
    $success = true;
    $message = '';
    $code = 200;
    try {
        $target_file = basename($_FILES["fileToUpload"]["name"]);
        $uz = false;
        if (uploadZip($target_file)) {
            $uz = doUnzip($target_file);
        }
        @unlink($target_file);

        if ($uz) {
            $toCopy = [
                'api',
                'srx',
                'index.php'
            ];

            foreach ($toCopy as $f) {
                if (!delDirAndFile("../".$f)) {
                    throw new Exception("无法删除老文件夹！", 500);
                }
                @ rename("tempZip/".$f, "../".$f);
            }
            $path = '../api/tmp';
            if (!chmodr($path, 0777)) {
                throw new Exception("无法更改/api/tmp文件夹权限！", 500);
            }
        }
    } catch (Exception $e) {
        $success = false;
        $message = $e->getMessage();
        $code = $e->getCode();
    }
    return json_encode([
        'success' => $success,
        'message' => $message,
        'code' => $code
    ]);
}

function uploadZip ($target_file) {
    $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    if ($fileType === 'zip') {
        if (file_exists($target_file)) {
            @ unlink($target_file);
        }
        @ rename($_FILES["fileToUpload"]["tmp_name"], $target_file);
    } else {
        throw new Exception("上传文件不是规定的zip文件！", 400);
    }

    if (!is_file($target_file)) {
        throw new Exception("上传失败！", 500);
    }
    return true;
}

function doUnzip ($file) {
    // get the absolute path to $file
    $path = 'tempZip';
    if (is_dir($path)) {
        delDirAndFile($path);
    }

    $zip = new ZipArchive;
    $res = $zip->open($file);
    if ($res === TRUE) {
        // extract it to the path we determined above
        $zip->extractTo($path);
        $zip->close();
    } else {
        throw new Exception("无法打开zip文件！", 500);
    }

    //验证zip是否合法
    $validateFileName = $path . "/wiewind_com_update_zip_file";
    if (!file_exists($validateFileName) || file_get_contents($validateFileName) != "to check the validity of the zip file") {
        delDirAndFile($path);
        @ unlink($file);
        throw new Exception("上传文件非法！", 500);
    }
    //删除验证文件
    @ unlink($validateFileName);

    return true;
}

function delDirAndFile($path, $delDir = true) {
    $handle = opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..") {
                is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            }
        }
        closedir($handle);
        if ($delDir) {
            return rmdir($path);
        }
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return false;
        }
    }
}

function chmodr($path, $filemode = 0777) {
    if (is_file($path)) {
        return chmod($path, $filemode);
    }

    if (is_dir($path)) {
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if($file != '.' && $file != '..') {
                $fullpath = $path . '/' . $file;
                if(is_link($fullpath)) {
                    closedir($dh);
                    return false;
                }
                if(!chmodr($fullpath, $filemode)) {
                    closedir($dh);
                    return false;
                }
            }
        }
        closedir($dh);
        if(!chmod($path, $filemode)) {
            return false;
        }
    }
    return true;
}

if(isset($_POST["project"])) {
    echo doSubmit();
}