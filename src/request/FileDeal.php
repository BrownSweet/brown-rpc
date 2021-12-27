<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/26 8:36
 */

namespace brown\request;

use brown\exceptions\FileException;
use brown\sendfile\FileBase;
use brown\sendfile\UploadFile;
class FileDeal
{
    protected $file=[];
    private static $instance;
    public function __construct()
    {
        $this->file=$_FILES ?? [];

    }
    public static function instance()
    {
        return new static();
    }
    public function file(string $name = '')
    {
        $files = $this->file;
        if (!empty($files)) {
            if (strpos($name, '.')) {
                [$name, $sub] = explode('.', $name);
            }

            // 处理上传文件
            $array = $this->dealUploadFile($files, $name);

            if ('' === $name) {
                // 获取全部文件
                return $array;
            } elseif (isset($sub) && isset($array[$name][$sub])) {
                return $array[$name][$sub];
            } elseif (isset($array[$name])) {
                return $array[$name];
            }
        }
    }

    protected function dealUploadFile(array $files, string $name): array
    {
        $array = [];
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $item  = [];
                $keys  = array_keys($file);
                $count = count($file['name']);

                for ($i = 0; $i < $count; $i++) {
                    if ($file['error'][$i] > 0) {
                        if ($name == $key) {
                            $this->throwUploadFileError($file['error'][$i]);
                        } else {
                            continue;
                        }
                    }

                    $temp['key'] = $key;

                    foreach ($keys as $_key) {
                        $temp[$_key] = $file[$_key][$i];
                    }

                    $item[] = new UploadFile($temp['tmp_name'], $temp['name'], $temp['type'], $temp['error']);
                }

                $array[$key] = $item;
            } else {
                if ($file instanceof FileBase) {
                    $array[$key] = $file;
                } else {
                    if ($file['error'] > 0) {
                        if ($key == $name) {
                            $this->throwUploadFileError($file['error']);
                        } else {
                            continue;
                        }
                    }

                    $array[$key] = new UploadFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
                }
            }
        }

        return $array;
    }

    protected function throwUploadFileError($error)
    {
        static $fileUploadErrors = [
            1 => 'upload File size exceeds the maximum value',
            2 => 'upload File size exceeds the maximum value',
            3 => 'only the portion of file is uploaded',
            4 => 'no file to uploaded',
            6 => 'upload temp dir not found',
            7 => 'file write error',
        ];

        $msg = $fileUploadErrors[$error];
        throw new FileException($msg, $error);
    }
}