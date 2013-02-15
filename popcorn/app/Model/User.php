<?php
App::uses('AppModel', 'Model');

/**
 * User Model
 *
 */
class User extends AppModel {
    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
            'email' => array(
                'email' => array(
                    'rule' => array('email'),
                    'message' => 'Invalid email',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                    //'on' => 'create', // Limit validation to 'create' or 'update' operations
                    ),
                ),
            'mobile' => array(
                'unique' => array(
                    'rule' => array('isUnique'),
                    'message' => 'Mobile number has already been used',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                    //'on' => 'create', // Limit validation to 'create' or 'update' operations
                    ),
                //'numeric' => array(
                    //'rule' => array('numeric'),
                    //'message' => 'Mobile number should be numeric',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                    //'on' => 'create', // Limit validation to 'create' or 'update' operations
                    //),
                'valid' => array(
                    'rule' => '/^63\d{10}$/',
                    'message' => 'Please enter a valid mobile number',
                    //'allowEmpty' => false,
                    //'required' => false,
                    //'last' => false, // Stop validation after this rule
                    //'on' => 'create', // Limit validation to 'create' or 'update' operations
                    ),
                ),
            );

    //==========================================================================

    public function afterSave($created) {
        if ($this->data[$this->alias]['pin_code']) {
            $this->log('afterSave: inserting to sms push queue...', 'user');
            Resque::enqueue('default', 'SmsShell', array('mobile_verification', $this->id));
        }
        return true;
    }

    //==========================================================================

}
