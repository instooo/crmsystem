<?php

/**
 * 删除目录下所有文件
 * @param $R
 * @return bool
 */
function _deleteDir($dir){
    if ($dh = opendir($dir))
    {
        while (($file = readdir($dh)) != false)
        {
            // 当遇到当前目录.与父目录..的时候不执行删除操作,继续循环。
            if (($file == '.') || ($file == '..'))
            {
                continue;
            }
            // 当前文件或目录的绝对路径。如果不是路径的话，会把当前目录的对应的文件或目录删掉。
            $dir_temp = $dir . '/' . $file;
            if (is_dir($dir_temp))
            {
                // 如果是目录，继续递归调用，进入此目录里面把目录里面的文件或目录删掉。
                _deleteDir($dir_temp);
            }
            else
            {
                // 如果是文件则把文件删掉。
                unlink($dir_temp);
            }
        }
        // 关闭目录。
        closedir($dh);
        // 此时传入的目录的文件和子目录都已经被清空，这里可以把目录删掉。
        rmdir($dir);
    }

}