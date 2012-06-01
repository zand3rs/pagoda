<?php

class ExampleController extends AppController {
    public $uses = array();
    //var $helpers = array('Javascript', 'Ajax');
    public $components = array('OauthConsumer');

    public function google() {

        //$scope = "https://www.google.com/m8/feeds/";
        //$REQUEST_TOKEN_URL = 'https://www.google.com/accounts/OAuthGetRequestToken?scope=' . urlencode($scope);
        //$requestToken = $this->OauthConsumer->getRequestToken('Google', $REQUEST_TOKEN_URL, 'http://mydomain.com/example/google_callback');

        $REQUEST_TOKEN_URL = 'https://www.google.com/accounts/OAuthGetRequestToken';
        $requestToken = $this->OauthConsumer->getRequestToken('Google', $REQUEST_TOKEN_URL, 'http://localhost/~zander/popcorn/example/google_callback', 'GET', array('scope' => 'https://www.google.com/m8/feeds'));

        $this->Session->write('google_request_token', $requestToken);
        $this->redirect('https://www.google.com/accounts/OAuthAuthorizeToken?oauth_token=' . $requestToken->key);
    }

    public function google_callback() {
        $requestToken = $this->Session->read('google_request_token'); 
        $accessToken = $this->OauthConsumer->getAccessToken('Google',   'https://www.google.com/accounts/OAuthGetAccessToken', $requestToken); 

    }
}

