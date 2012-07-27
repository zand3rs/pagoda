<?php
App::uses('File', 'Utility');
App::uses('HttpSocket', 'Network/Http');

class Web {

    public function __construct() {
    }

    //--------------------------------------------------------------------------

    static public function get($url) {
        $socket = new HttpSocket();
        $result = $socket->get($url);
        //$response = $socket->response;

        return $result;
    }

    //--------------------------------------------------------------------------

    static public function post($url, $data) {
        $socket = new HttpSocket();
        $result = $socket->post($url, $data);
        //$response = $socket->response;

        return $result;
    }

    //--------------------------------------------------------------------------

    static public function download($url, $dir = '', $file = '', $recursive = false) {
        $pathinfo = pathinfo($url);
        $default_ext = '.html';
        $root_dir = rtrim(WWW_ROOT, DS);
        $upload_dir = Configure::read('UPLOAD_ROOT');
        $dest_dir = rtrim($dir, DS);
        $dest_file = $file;

        CakeLog::write('web', 'url: '.$url);
        CakeLog::write('web', 'dir: '.$dir);
        CakeLog::write('web', 'file: '.$file);
        CakeLog::write('web', 'root_dir: '.$root_dir);
        CakeLog::write('web', 'upload_dir: '.$upload_dir);
        CakeLog::write('web', 'dest_dir: '.$dest_dir);
        CakeLog::write('web', 'dest_file: '.$dest_file);

        if (empty($dest_file)) {
            $dest_file = $pathinfo['filename'];
            $extension = $default_ext;

            if (isset($pathinfo['extension'])) {
                $ext = '.'.$pathinfo['extension'];
                if (! preg_match('/\.(?:com|net|org)/i', $ext)) {
                    $extension = $ext;
                }
            }

            $dest_file .= $extension;
        }
        CakeLog::write('web', 'dest_file: '.$dest_file);

        $local_path = $upload_dir.(!empty($dest_dir) ? DS.$dest_dir : '').DS.$dest_file;
        CakeLog::write('web', 'local_path: '.$local_path);
        $abs_path = $root_dir.$local_path;
        CakeLog::write('web', 'abs_path: '.$abs_path);

        if (preg_match('/\.css.*$/i', $dest_file)) {
            CakeLog::write('web', 'File is a CSS!!!');
        }

        $f = new File($abs_path, true);
        $f->open('wb', true);
        $socket = new HttpSocket();
        $socket->setContentResource($f->handle);
        $socket->get($url);
        $f->close();

        return $dest_file;
    }
}
