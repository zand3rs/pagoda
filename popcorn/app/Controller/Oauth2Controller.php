<?php

class Oauth2Controller extends AppController {

    private $host;
    private $controller_uri;
    private $client_id;
    private $client_secret;

    //--------------------------------------------------------------------------

    public function beforeFilter() {
        $this->host = $_SERVER['HTTP_HOST'];
        $this->controller_uri = $this->params->base.'/'.$this->params->params['controller'];
        $this->client_id = Configure::read('OAuth2.clientId');
        $this->client_secret = Configure::read('OAuth2.clientSecret');
    }

    //--------------------------------------------------------------------------

    public function index() {
        $this->set('url', $this->getOAuthRequestUri()); 
    }

    public function callback() {
        $this->set('query', $this->request->query);
        $this->set('code', $this->request->query['code']);
        $this->set('state', $this->request->query['state']);
    }

    private function getOAuthRequestUri() {
        /*
        https://accounts.google.com/o/oauth2/auth?
        scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&
        state=%2Fprofile&
        redirect_uri=https%3A%2F%2Foauth2-login-demo.appspot.com%2Fcode&
        response_type=code&
        client_id=812741506391.apps.googleusercontent.com&approval_prompt=force

        error
        https://oauth2-login-demo.appspot.com/code?error=access_denied&state=/profile

        successful
        https://oauth2-login-demo.appspot.com/code?state=/profile&code=4/P7q7W91a-oMsCeLvIaQm6bTrgtp7

        http://localhost/~zander/popcorn/oauth2/callback?state=/~zander/popcorn/oauth2&code=4/skXB8Fs2uQA2MRevxlml7jPF-9yD
        */

        $oauth2_uri = 'https://accounts.google.com/o/oauth2/auth';
        $scope = rawurlencode('https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile');
        $state = rawurlencode($this->controller_uri);
        $redirect_uri = rawurlencode('http://'.$this->host.$this->controller_uri.'/callback');
        $response_type = 'code';
        $approval_prompt = 'auto';
        $access_type = 'offline';

        $request_uri = $oauth2_uri.'?scope='.$scope.'&state='.$state.'&redirect_uri='.$redirect_uri
            .'&response_type='.$response_type.'&client_id='.$this->client_id.'&approval_prompt='.$approval_prompt;

        return $request_uri;
    }
}
