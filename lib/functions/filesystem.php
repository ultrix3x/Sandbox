<?php
class SandboxFunctionsFileSystem extends SandboxCall {
  public static function Init() {
    $class = get_called_class();
    new $class('basename', true);
    new $class('chgrp', true);
    new $class('chmod', true);
    new $class('chown', true);
    new $class('clearstatcache', true);
    new $class('copy', true);
    new $class('delete', true);
    new $class('dirname', true);
    new $class('disk_free_space', true);
    new $class('disk_total_space', true);
    new $class('diskfreespace', true);
    new $class('fclose', true);
    new $class('feof', true);
    new $class('fflush', true);
    new $class('fgetc', true);
    new $class('fgetcsv', true);
    new $class('fgets', true);
    new $class('fgetss', true);
    new $class('file_exists', true);
    new $class('file_get_contents', true);
    new $class('file_put_contents', true);
    new $class('file', true);
    new $class('fileatime', true);
    new $class('filectime', true);
    new $class('filegroup', true);
    new $class('fileinode', true);
    new $class('filemtime', true);
    new $class('fileowner', true);
    new $class('fileperms', true);
    new $class('filesize', true);
    new $class('filetype', true);
    new $class('flock', true);
    new $class('fnmatch', true);
    new $class('fopen');
    new $class('fpassthru', true);
    new $class('fputcsv', true);
    new $class('fputs', true);
    new $class('fread', true);
    new $class('fscanf', true);
    new $class('fseek', true);
    new $class('fstat', true);
    new $class('ftell', true);
    new $class('ftruncate', true);
    new $class('fwrite', true);
    new $class('glob', true);
    new $class('is_dir', true);
    new $class('is_executable', true);
    new $class('is_file', true);
    new $class('is_link', true);
    new $class('is_readable', true);
    new $class('is_uploaded_file', true);
    new $class('is_writable', true);
    new $class('is_writeable', true);
    new $class('lchgrp', true);
    new $class('lchown', true);
    new $class('link', true);
    new $class('linkinfo', true);
    new $class('lstat', true);
    new $class('mkdir', true);
    new $class('move_uploaded_file', true);
    new $class('parse_ini_file', true);
    new $class('parse_ini_string', true);
    new $class('pathinfo', true);
    new $class('pclose', true);
    new $class('popen', true);
    new $class('readfile', true);
    new $class('readlink', true);
    new $class('realpath_cache_get', true);
    new $class('realpath_cache_size', true);
    new $class('realpath');
    new $class('rename', true);
    new $class('rewind', true);
    new $class('rmdir', true);
    new $class('set_file_buffer', true);
    new $class('stat', true);
    new $class('symlink', true);
    new $class('tempnam', true);
    new $class('tmpfile', true);
    new $class('touch', true);
    new $class('umask', true);
    new $class('unlink', true);
  }
  
  public static function ValidateFilename($filename) {
    $scheme = false;
    $host = false;
    $port = false;
    $path = $filename;
    $query = false;
    $fragment = false;
    $adjustPath = false;
    if(strpos($filename, ':') !== false) {
      $url = parse_url($filename);
      $scheme = isset($url['scheme'])?$url['scheme']:false;
      $host = isset($url['host'])?$url['host']:false;
      $port = isset($url['port'])?$url['port']:false;
      $path = isset($url['path'])?$url['path']:false;
      $query = isset($url['query'])?$url['query']:false;
      $fragment = isset($url['fragment'])?$url['fragment']:false;
      switch($url['scheme']) {
        case 'ftp':
        case 'ftps':
        case 'http':
        case 'https':
          break;
        case 'file':
        case 'zip':
          $adjustPath = true;
          break;
      }
    }
    if(defined('SANDBOX_ROOT_PATH')) {
      if($adjustPath) {
        $path = str_replace('\\', '/', $path);
        $path = SANDBOX_ROOT_PATH.'/'.$path;
        $path = str_replace('//', '/', $path);
        $path = str_replace('/./', '/', $path);
        $path = str_replace('//', '/', $path);
        $parts = explode('/', trim($path,'/'));
        $okParts = array();
        foreach($parts as $part) {
          if($part == '..') {
            array_pop($okParts);
          } else {
            $okParts[] = $part;
          }
        }
        $path = '/'.implode('/', $okParts);
      }
    }
    $newFilename = '';
    if($scheme !== false) {
      $newFilename .= $scheme.'://';
    }
    if($host !== false) {
      $newFilename .= $host;
    }
    if($port !== false) {
      $newFilename .= ':'.$port;
    }
    if($path !== false) {
      $newFilename .= $path;
    }
    if($query !== false) {
      $newFilename .= '?'.$query;
    }
    if($fragment !== false) {
      $newFilename .= '#'.$fragment;
    }
    if(isset(static::$events['filename'])) {
      foreach(static::$events['filename'] as $callback) {
        $newFilename = $callback($newFilename);
      }
    }
    return $newFilename;
  }
  
  public function wrap_fopen($filename, $mode, $use_include_path = false, $context = false) {
    $filename = $this->ValidateFilename($filename);
    if($filename === false) {
      return false;
    }
    if($context === false) {
      $context = stream_context_create();
    }
    return fopen($filename, $mode, $use_include_path, $context);
  }
  
  public function wrap_realpath($path) {
    $path = $this->ValidateFilename($path);
    if($path === false) {
      return false;
    }
    return realpath($path);
  }
}
?>