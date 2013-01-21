<?php
App::uses('AppModel', 'Model');
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
            'url' => array(
                'notempty' => array(
                    'rule' => array('notempty'),
                    //'message' => 'Your custom message here',
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

}
