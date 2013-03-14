<?php
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
App::uses('HttpSocket', 'Network/Http');

class Web {

    public function __construct() {
    }

    //--------------------------------------------------------------------------

    static public function get($url) {
        $request = array('header' => array(
                            'Accept' => '*/*',
                            'Pragma' => 'no-cache',
                            'Cache-Control' => 'no-cache'
                        ));
        $socket = new HttpSocket();
        $result = $socket->get($url, array(), $request);
        //$response = $socket->response;

        return $result;
    }

    //--------------------------------------------------------------------------

    static public function post($url, $data, $options = null) {
        $socket = new HttpSocket();
        $result = $socket->post($url, $data, $options);
        //$response = $socket->response;
        CakeLog::write('web', 'url: '.$url);
        CakeLog::write('web', 'data: '.$data);
        CakeLog::write('web', 'options: '.print_r($options, true));
        CakeLog::write('web', 'request'.print_r($socket->request, true));

        return $result;
    }

    //--------------------------------------------------------------------------

    static public function download($url, $dir = '', $file = '', $recursive = false) {
        //$pathinfo = pathinfo($url);
        $pathinfo = parse_url($url);
        $default_ext = '.html';
        $root_dir = rtrim(WWW_ROOT, DS);
        $upload_dir = Configure::read('UPLOAD_ROOT');
        $dest_dir = rtrim($dir, DS);
        $dest_file = !empty($file) ? $file : $pathinfo['path'];

        /*
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
        */

        //-- clean dest_file
        $dest_file = ltrim($dest_file, DS);
        $dest_file = preg_replace('/\?.*$/', '', $dest_file);
        $dest_file = preg_replace('/\.php.?$/', '.html', $dest_file);
        if (empty($dest_file)) {
            $dest_file = md5($url);
        }

        CakeLog::write('web', 'url: '.$url);
        CakeLog::write('web', 'pathinfo: '.print_r($pathinfo, true));
        CakeLog::write('web', 'dir: '.$dir);
        CakeLog::write('web', 'file: '.$file);
        CakeLog::write('web', 'root_dir: '.$root_dir);
        CakeLog::write('web', 'upload_dir: '.$upload_dir);
        CakeLog::write('web', 'dest_dir: '.$dest_dir);
        CakeLog::write('web', 'dest_file: '.$dest_file);

        $local_path = $upload_dir.(!empty($dest_dir) ? DS.$dest_dir : '').DS.$dest_file;
        CakeLog::write('web', 'local_path: '.$local_path);
        $abs_path = $root_dir.$local_path;
        CakeLog::write('web', 'abs_path: '.$abs_path);

        if (preg_match('/\.css.*$/i', $dest_file)) {
            CakeLog::write('web', 'File is a CSS!!!');
        }

        $f = new File($abs_path, true, 0777);
        $f->open('wb', true);
        $socket = new HttpSocket();
        $socket->setContentResource($f->handle);
        $request = array('header' => array(
                            'Accept' => '*/*',
                            'Pragma' => 'no-cache',
                            'Cache-Control' => 'no-cache'
                        ));
        $socket->get($url, array(), $request);
        $f->close();

        return $dest_file;
    }
}
