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
        'Cookie',
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
        $this->Cookie->name = 'popcorn';
        $this->Cookie->time = 0;
    }

    //--------------------------------------------------------------------------

    public function afterFilter() {
        if ($this->Auth->loggedIn()) {
            if (! $this->Cookie->read('user')) {
                $this->Cookie->write('user.email', $this->Auth->user('email'), false);
                $this->Cookie->write('user.access_token', $this->Auth->user('access_token'), false);
            }
        } else {
            $this->Cookie->destroy();
        }
    }

    //==========================================================================

}
