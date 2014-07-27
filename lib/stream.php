<?php
class SandboxStream {
  protected static $data = array();
  protected $position;
  protected $varname;

  function stream_open($path, $mode, $options, &$opened_path) {
    $url = parse_url($path);
    $this->varname = $url["host"];
    $this->position = 0;
    
    // $modeFlags = preg_split('//Us', $mode, -1, PREG_SPLIT_NO_EMPTY);
    
    if(!isset(static::$data[$this->varname])) {
      static::$data[$this->varname] = '';
    }
    return true;
  }

  function stream_read($count) {
    $ret = substr(static::$data[$this->varname], $this->position, $count);
    $this->position += strlen($ret);
    return $ret;
  }

  function stream_write($data) {
    $left = substr(static::$data[$this->varname], 0, $this->position);
    $right = substr(static::$data[$this->varname], $this->position + strlen($data));
    static::$data[$this->varname] = $left . $data . $right;
    $this->position += strlen($data);
    return strlen($data);
  }

  function stream_tell() {
    return $this->position;
  }

  function stream_eof() {
    return $this->position >= strlen(static::$data[$this->varname]);
  }

  function stream_seek($offset, $whence) {
    switch ($whence) {
      case SEEK_SET:
        if ($offset < strlen(static::$data[$this->varname]) && $offset >= 0) {
          $this->position = $offset;
          return true;
        } else {
          return false;
        }
        break;

      case SEEK_CUR:
        if ($offset >= 0) {
          $this->position += $offset;
          return true;
        } else {
          return false;
        }
        break;

      case SEEK_END:
        if (strlen(static::$data[$this->varname]) + $offset >= 0) {
          $this->position = strlen(static::$data[$this->varname]) + $offset;
          return true;
        } else {
          return false;
        }
        break;

      default:
        return false;
    }
  }

  public function stream_metadata($path, $option, $var) {
    if($option == STREAM_META_TOUCH) {
      $url = parse_url($path);
      $varname = $url["host"];
      if(!isset(static::$data[$varname])) {
        static::$data[$varname] = '';
      }
      return true;
    }
    return false;
  }
  
  public function stream_stat() {
    $size = strlen(static::$data[$this->varname]);
    if(defined('SANDBOX_STREAM_BLOCKSIZE')) {
      $blockSize = SANDBOX_STREAM_BLOCKSIZE;
      if($blockSize == 0) {
        $blockSize = 4096;
      } elseif($blockSize % 512 != 0) {
        $blockSize = ceil($blockSize / 512) * 512;
      }
    } else {
      $blockSize = 4096;
    }
    $blocks = (floor($size / $blockSize) + 1) * ($blockSize / 512);
    $time = time();
    $stat = array();
    $stat[0] = 667;
    $stat[1] = 0;
    $stat[2] = 0100644;
    $stat[3] = 1;
    $stat[4] = 0;
    $stat[5] = 0;
    $stat[6] = 0;
    $stat[7] = $size;
    $stat[8] = $time;
    $stat[9] = $time;
    $stat[10] = $time;
    $stat[11] = $blockSize;
    $stat[12] = $blocks;
    $stat['dev'] = 667;
    $stat['ino'] = 0;
    $stat['mode'] = 0100644;
    $stat['nlink'] = 1;
    $stat['uid'] = 0;
    $stat['gid'] = 0;
    $stat['rdev'] = 0;
    $stat['size'] = $size;
    $stat['atime'] = $time;
    $stat['mtime'] = $time;
    $stat['ctime'] = $time;
    $stat['blksize'] = $blockSize;
    $stat['blocks'] = $blocks;
    return $stat;
  }
  
}

stream_wrapper_register("sandbox", "SandboxStream")
    or die("Failed to register protocol");

?>