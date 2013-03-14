<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class Zipper extends ZipArchive {
    public function addDir($path) { 
        $this->addEmptyDir($path); 
        $nodes = glob($path . '/*');
        foreach ($nodes as $node) {
            if (is_dir($node)) {
                $this->addDir($node);
            } else if (is_file($node)) {
                $this->addFile($node);
            }
        }
    }
}

class Zip {

    public function __construct() {
    }

    //--------------------------------------------------------------------------

    static public function create($destination = '', $files = array(), $overwrite = false) {

        if (file_exists($destination) && !$overwrite) {
            return false;
        }

        $validFiles = array();
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $validFiles[] = $file;
                }
            }
        } else {
            if (file_exists($files)) {
                $validFiles[] = $files;
            }
        }

        if (count($validFiles) < 1) {
            return false;
        }

        $zip = new Zipper();
        $type = $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE;
        if ($zip->open($destination, $type) !== true) {
            return false;
        }

        //$dest = str_replace('.zip', '', basename($destination));
        //$zip->addFile($file, $dest . DS . basename($file));

        foreach ($validFiles as $file) {
            if (is_dir($file)) {
                $zip->addDir($file);
            } else {
                $zip->addFile($file);
            }
        }
        $zip->close();

        return file_exists($destination);
    }
}

