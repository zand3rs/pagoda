<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::import('Vendor', 'Utils/Zip');

class HelloShell extends Shell {

    public function main() {
        $src = WWW_ROOT . 'files/test';
        $zip =  WWW_ROOT . 'files/test.zip';
        $this->out($src);
        $this->out($zip);

        $upload_root = Configure::read('UPLOAD_ROOT');
        $this->out($upload_root);

        /*
        $dir = new Folder($src);
        $files = $dir->findRecursive();
        $status = Zip::create($zip, $files, true);
        */
        //$status = Zip::create($zip, $src, true);

        //$this->out($status);
    }
}
