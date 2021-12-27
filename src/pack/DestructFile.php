<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/26 14:09
 */

namespace brown\pack;

use brown\sendfile\FileBase;
use Throwable;

class DestructFile extends FileBase
{
  public function __destruct()
  {
      //销毁时删除临时文件
      try {
          if (file_exists($this->getPathname())) {
              unlink($this->getPathname());
          }
      } catch (Throwable $e) {

      }
  }
}