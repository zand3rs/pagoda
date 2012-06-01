<?php

class AppController extends Controller {

    //==========================================================================

    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = array('Form', 'Html', 'Js', 'Time', 'Session', 'Cache');

    //--------------------------------------------------------------------------

    public $components = array(
        'Session',
        'Auth' => array(
            'loginRedirect' => array('controller' => 'users', 'action' => 'index'),
            'logoutRedirect' => array('controller' => 'users', 'action' => 'login'),
            'autoRedirect' => false
        ),
        'DebugKit.Toolbar',
        'Resque.Resque'
    );

    //--------------------------------------------------------------------------

    public function beforeFilter() {
        $this->Auth->authenticate = array('Custom');
        $this->Auth->allow('login', 'logout', 'signup', 'callback');
    }


    //==========================================================================

}
