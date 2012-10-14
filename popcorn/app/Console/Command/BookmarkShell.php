<?php
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
App::import('Vendor', 'Utils/Web');
App::import('Vendor', 'Utils/Zip');

class BookmarkShell extends Shell {
    public $uses = array('Bookmark');

    public function perform() {
        $this->initialize();
        $this->{array_shift($this->args)}();
    }

    public function download() {
        $bookmark_id = $this->args[0];
        $this->log('bookmark_id: '.$bookmark_id, 'console');
        $this->Bookmark->id = $bookmark_id;
        $bookmark = $this->Bookmark->read();

        $this->log($bookmark, 'console');
        $url = $bookmark['Bookmark']['url'];
        $this->log($url, 'console');
        $user_id = $bookmark['Bookmark']['user_id'];

        $base_url = rtrim(preg_replace('/\/[^\/]+\.[^\/]+$/', '', $url), DS).DS;
        $this->log('base_url: '.$base_url, 'console');

        $response = Web::get($url);
        $response_body = $response->body;

        //-- set params...
        $basename = md5($url);
        $pathinfo = pathinfo($url);
        $root_dir = rtrim(WWW_ROOT, DS);
        $user_dir = str_pad($user_id, 10, '0', STR_PAD_LEFT);
        $upload_dir = Configure::read('UPLOAD_ROOT');
        $dest_dir = $user_dir.DS.$basename;
        $dest_file = 'index.html';

        $this->log('dest_dir: '.$dest_dir, 'console');

        //-- get href files...
        $match = preg_match_all('/href *= *[\'"](?!https?:\/\/)([^\'"]+\.[[:alnum:]]+)[^\'"]*["\']/', $response_body, $matches);
        if ($match) {
            foreach($matches[1] as $component) {
                try {
                    $download_component = preg_replace('/^\/\//', 'http://', $component);
                    $this->log('component: '.$download_component, 'console');
                    $component_url = (preg_match('/^https?:\/\//', $download_component)) ? $download_component : $base_url.$download_component;
                    $this->log('component_url: '.$component_url, 'console');
                    $component_uri = Web::download($component_url, $dest_dir);
                    $this->log('component_uri: '.$component_uri, 'console');
                    //-- replace remote paths...
                    $response_body = str_replace($component, $component_uri, $response_body);
                } catch (Exception $e) {
                    $this->log('Got exception: '.$e->getMessage(), 'console');
                }
            }
        }

        //-- get src files...
        $match = preg_match_all('/src *= *[\'"]([^\'"]+\.[[:alnum:]]+)[^\'"]*["\']/', $response_body, $matches);
        if ($match) {
            foreach($matches[1] as $component) {
                try {
                    $download_component = preg_replace('/^\/\//', 'http://', $component);
                    $this->log('component: '.$download_component, 'console');
                    $component_url = (preg_match('/^https?:\/\//', $download_component)) ? $download_component : $base_url.$download_component;
                    $this->log('component_url: '.$component_url, 'console');
                    $component_uri = Web::download($component_url, $dest_dir);
                    $this->log('component_uri: '.$component_uri, 'console');
                    //-- replace remote paths...
                    $response_body = str_replace($component, $component_uri, $response_body);
                } catch (Exception $e) {
                    $this->log('Got exception: '.$e->getMessage(), 'console');
                }
            }
        }

        //-- remove scripts...
        //$response_body = preg_replace('/<script[^>]*>[^<]*<\/script>/is', '', $response_body);

        //$this->log($response_body, 'console');

        $local_path = $upload_dir.DS.$dest_dir.DS.$dest_file;
        $abs_path = $root_dir.$local_path;

        $this->log('local_path: '.$local_path, 'console');
        $this->log('abs_path: '.$abs_path, 'console');

        $file = new File($abs_path, true);
        $file->write($response_body);
        $file->close();

        $archive_path = $root_dir.$upload_dir.DS.$dest_dir;
        $archive_file = $archive_path.'.zip';
        $archive = $upload_dir.DS.$dest_dir.'.zip';

        $this->log('archive_path: '.$archive_path, 'console');
        $this->log('archive_file: '.$archive_file, 'console');
        $this->log('archive: '.$archive, 'console');

        $archive_status = Zip::create($archive_file, $archive_path, true);

        $this->log('archive_status: '. ($archive_status) ? 'success' : 'error', 'console');

        //$this->Bookmark->saveField('local_path', $local_uri);
        $this->Bookmark->updateAll(
                array('Bookmark.local_path' => "'$local_path'", 'Bookmark.archive' => "'$archive'"),
                array('Bookmark.id' => $bookmark_id)
                );
    }
}
