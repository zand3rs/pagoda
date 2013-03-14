<?php
App::uses('AppModel', 'Model');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
/**
 * Bookmark Model
 *
 * @property User $User
 */
class Bookmark extends AppModel {

    public $name = 'Bookmark';

    /**
     * Display field
     *
     * @var string
     */
    public $displayField = 'title';
    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
            'user_id' => array(
                'numeric' => array(
                    'rule' => array('numeric'),
                    //'message' => 'Your custom message here',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                    //'on' => 'create', // Limit validation to 'create' or 'update' operations
                    ),
                ),
            'title' => array(
                'notempty' => array(
                    'rule' => array('notempty'),
                    'message' => "Can't be blank.",
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                    //'on' => 'create', // Limit validation to 'create' or 'update' operations
                    ),
                ),
            'url' => array(
                'notempty' => array(
                    'rule' => array('notempty'),
                    'message' => "Can't be blank.",
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                    //'on' => 'create', // Limit validation to 'create' or 'update' operations
                    ),
                ),
            );

    //The Associations below have been created with all possible keys, those that are not needed can be removed

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = array(
            'User' => array(
                'className' => 'User',
                'foreignKey' => 'user_id',
                'conditions' => '',
                'fields' => '',
                'order' => ''
                )
            );

    public function afterSave($created) {
        if ($created) {
            Resque::enqueue('default', 'BookmarkShell', array('download', $this->data[$this->alias]['id']));
        } else if ($this->data[$this->alias]['downloaded']) {
            Resque::enqueue('default', 'SmsShell', array('download_bookmark', $this->id));
        }
        return true;
    }

    /*
    !!!! Warning: the ff. code is very dangerous !!!! 

    public function beforeDelete($cascade = true) {
        $bookmark = $this->find('first', array(
                        'conditions' => array('id' => $this->id),
                        'recursive' => -1
                    ));
        $local_path = $bookmark['Bookmark']['local_path'];
        $archive = $bookmark['Bookmark']['archive'];

        $root_dir = rtrim(WWW_ROOT, DS);
        $abs_path = $root_dir.$local_path;
        $abs_archive = $root_dir.$archive;
        $abs_dir = dirname($abs_path);

        if (is_dir($abs_dir)) {
            $this->delTree($abs_dir);
        }
        if (is_file($abs_archive)) {
            @unlink($abs_archive);
        }

        return true;
    }

    private function delTree($dir) { 
        $dir = rtrim($dir, DS);
        $files = glob($dir.DS.'*', GLOB_MARK); 
        foreach ($files as $file) {
            if (substr($file, -1) == DS) {
                $this->delTree($file);
            } else {
                @unlink($file);
            }
        }
        if (is_dir($dir)) {
            @rmdir($dir);
        }
    }
    */

}
