<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'Utils/Web');
App::import('Vendor', 'Utils/Secure');

/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController {

    public $autoRender = false;

    public $components = array('Password');

    //--------------------------------------------------------------------------

    public function index() {
        $this->User->id = $this->Auth->user('id');
        if (!$this->User->exists()) {
            $this->Session->setFlash(__('User not found.'));
            $this->redirect($this->Auth->logout());
        }

        $this->set('user', $this->User->read());
        $this->render('index');
    }

    //--------------------------------------------------------------------------

    public function verify() {
        $this->User->id = $this->Auth->user('id');

        if (!$this->User->exists()) {
            $this->Session->setFlash(__('User not found.'));
            $this->redirect($this->Auth->logout());
        }
        
        if ($this->request->is('post') || $this->request->is('put')) {
            $data = $this->User->read();
            $expiry_date = strtotime($data['User']['pin_expiry']);
            $current_date = strtotime(date('Y-m-d H:i:s'));

            if ($data['User']['pin_code'] === $this->request->data['User']['pin_code']) {
                if ($expiry_date > $current_date && 
                        $this->User->updateAll(array(
                                'mobile_status' => "'VERIFIED'"
                                ))) {
                    $this->Session->setFlash(__('Mobile verification successful.'));
                    $this->redirect(array('action' => 'index'));
                } else {
                    $this->Session->setFlash(__('Mobile verification failed.'));
                }
            } else {
                $this->Session->setFlash(__('Pin code mismatch.'));
            }
        }

        $this->render('verify');
    }

    //--------------------------------------------------------------------------

    public function login() {
        $this->Session->destroy();
        $this->set('oauth2_url', $this->getOAuth2RequestUri());
        $this->render('login');
    }

    //--------------------------------------------------------------------------

    public function logout() {
        $this->Session->destroy();
        $this->redirect($this->Auth->logout());
    }

    //--------------------------------------------------------------------------

    public function getActiveUser() {
        $activeUser = null;
        $user = $this->Auth->user();
        if ($user) {
            $activeUser = array(
                'id' => $user['id'],
                'email' => $user['email'],
            );
        }
        return json_encode($activeUser);
    }

    //--------------------------------------------------------------------------

    public function signup() {
        if ($this->Auth->loggedIn()) {
            $this->redirect($this->Auth->redirect());
        }

        if ($this->request->is('post')) {
            $this->User->create();
            $this->request->data['User']['pin_code'] = Secure::randomString();
            $this->request->data['User']['pin_expiry'] = date('Y-m-d H:i:s', strtotime('+1 day'));
            $this->request->data['User']['mobile_status'] = 'UNVERIFIED';
            $this->request->data['User']['date_registered'] = date('Y-m-d H:i:s');

            if (! $this->User->save($this->request->data)) {
                $this->Session->setFlash(__('Unable to register. Please try again.'));
            }
        } else {
            $response = Web::get($this->getOAuth2UserInfoUri());
            $response_body = json_decode($response->body, true);

            if (isset($response_body['error'])) {
                $this->Session->setFlash(__('Session timed out. Please try again.'));
                $this->redirect($this->Auth->logout());
            }

            $this->request->data['User']['email'] = $response_body['email'];
            $this->request->data['User']['auth_code'] = $this->Session->read('OAuth2.code');
            $this->request->data['User']['access_token'] = $this->Session->read('OAuth2.access_token');
            $this->request->data['User']['token_type'] = $this->Session->read('OAuth2.token_type');
            $this->request->data['User']['token_expiry'] = $this->Session->read('OAuth2.expires_in');
        }

        if ($this->Auth->login()) {
            $this->Session->setFlash(__('Successful login.'));
            $this->redirect($this->Auth->redirect());
        }

        $this->render('signup');
    }

    //--------------------------------------------------------------------------

    public function change_mobile() {
        $this->User->id = $this->Auth->user('id');

        if (!$this->User->exists()) {
            $this->Session->setFlash(__('User not found.'));
            $this->redirect($this->Auth->logout());
        }

        $data = $this->User->read();

        if ($this->request->is('post') || $this->request->is('put')) {
            $old_mobile = $data['User']['mobile'];
            $new_mobile = $this->request->data['User']['mobile'];

            $status = true;
            if ($old_mobile != $new_mobile) {
                $this->request->data['User']['pin_code'] = Secure::randomString();
                $this->request->data['User']['pin_expiry'] = date('Y-m-d H:i:s', strtotime('+1 day'));
                $this->request->data['User']['mobile_status'] = 'UNVERIFIED';
                $status = $this->User->save($this->request->data);
            }
            if ($status) {
                $this->Session->setFlash(__('The mobile number has been saved.'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The mobile number could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $data;
        }

        $this->render('change_mobile');
    }

    //--------------------------------------------------------------------------

    public function generate_pin() {
        $this->User->id = $this->Auth->user('id');

        if (!$this->User->exists()) {
            $this->Session->setFlash(__('User not found.'));
            $this->redirect($this->Auth->logout());
        }
        
        $pin_code = Secure::randomString();
        $pin_expiry = date('Y-m-d H:i:s', strtotime('+1 day'));
        $mobile_status = 'UNVERIFIED';

        $this->User->set('pin_code', $pin_code);
        $this->User->set('pin_expiry', $pin_expiry);
        $this->User->set('mobile_status', $mobile_status);

        if ($this->User->save()) {
            $this->Session->setFlash(__('Mobile pin code generated.'));
        } else {
            $this->Session->setFlash(__('Unable to generate mobile pin code.'));
        }

        $this->redirect(array('action' => 'index'));
    }

    //--------------------------------------------------------------------------

    public function callback() {
        if (isset($this->request->query['error'])) {
            $this->Session->setFlash('Login Error: '.$this->request->query['error']);
            $this->redirect($this->Auth->logout());
        }

        if (isset($this->request->query['code'])) {
            $auth_code = $this->request->query['code'];
            $this->Session->write('OAuth2.code', $auth_code);

            $oauth2_uri = 'https://accounts.google.com/o/oauth2/token';
            $token_request = $this->getOAuth2AccessTokenRequest();

            $response = Web::post($oauth2_uri, $token_request);
            $response_body = json_decode($response->body, true);

            if (isset($response_body['error'])) {
                $this->Session->setFlash('Login Error: '.$response_body['error']);
                $this->redirect($this->Auth->logout());
            }

            if (isset($response_body['access_token'])) {
                $this->Session->write('OAuth2.access_token', $response_body['access_token']);
                $this->Session->write('OAuth2.token_type', $response_body['token_type']);
                $this->Session->write('OAuth2.expires_in', $response_body['expires_in']);
                $this->redirect(array('action' => 'signup'));
            }
        }
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    private function getOAuth2RequestUri() {
        $client_id = Configure::read('OAuth2.clientId');
        $client_secret = Configure::read('OAuth2.clientSecret');

        $host = $_SERVER['HTTP_HOST'];
        $controller_uri = $this->params->base.'/'.$this->params->params['controller'];

        $oauth2_uri = 'https://accounts.google.com/o/oauth2/auth';
        $scope = rawurlencode('https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile');
        $state = rawurlencode($controller_uri);
        $redirect_uri = rawurlencode('http://'.$host.$controller_uri.'/callback');
        $response_type = 'code';
        $approval_prompt = 'auto';
        $access_type = 'offline';

        $request_uri = $oauth2_uri.'?scope='.$scope.'&state='.$state.'&redirect_uri='.$redirect_uri
            .'&response_type='.$response_type.'&client_id='.$client_id.'&approval_prompt='.$approval_prompt;

        return $request_uri;
    }

    //--------------------------------------------------------------------------

    private function getOAuth2AccessTokenRequest() {
        $client_id = Configure::read('OAuth2.clientId');
        $client_secret = Configure::read('OAuth2.clientSecret');
        $code = $this->Session->read('OAuth2.code');

        $host = $_SERVER['HTTP_HOST'];
        $controller_uri = $this->params->base.'/'.$this->params->params['controller'];

        $redirect_uri = rawurlencode('http://'.$host.$controller_uri.'/callback');
        $request_uri = 'code='.$code.'&client_id='.$client_id.'&client_secret='.$client_secret
            .'&redirect_uri='.$redirect_uri.'&grant_type=authorization_code';

        return $request_uri;
    }

    //--------------------------------------------------------------------------

    private function getOAuth2UserInfoUri() {
        $access_token = $this->Session->read('OAuth2.access_token');
        return 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$access_token;
    }

}
