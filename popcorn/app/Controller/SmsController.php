<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'Utils/Sms');


/**
 * Sms Controller
 *
 * @property Sms $Sms
 */
class SmsController extends AppController {

    public $autoRender = false;

    //--------------------------------------------------------------------------

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('push');
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    public function push() {
        $response = Sms::send('639209813808', 'test message');
        return $response->body();
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

}
