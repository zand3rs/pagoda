<?php
App::uses('AppController', 'Controller');
/**
 * Api Controller
 *
 * @property Api $Api
 */
class ApiController extends AppController {

    public $autoRender = false;
    public $uses = array('User', 'Bookmark');

    //--------------------------------------------------------------------------

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('get_bookmarks', 'add_bookmark');
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    public function login() {
        if (!$this->request->is('post')) {
            return $this->response->statusCode(400);
        }

        $data = $this->request->input('json_decode');
        $user = $this->User->findByEmail($data->email);
        if (!$user) {
            return $this->response->statusCode(401);
        }

        $payload = array(
            'id' => $user['User']['id'],
            'email' => $user['User']['email'],
            'access_token' => $user['User']['access_token'],
        );
        return json_encode($payload);
    }

    //--------------------------------------------------------------------------

    public function get_user($access_token = null) {
        $user = $this->User->findByAccessToken($access_token);
        if (!$user) {
            return $this->response->statusCode(401);
        }

        $payload = array(
            'id' => $user['User']['id'],
            'email' => $user['User']['email'],
            'access_token' => $user['User']['access_token'],
        );

        return json_encode($payload);
    }

    //--------------------------------------------------------------------------

    public function get_bookmarks($access_token = null) {
        $user = $this->User->findByAccessToken($access_token);
        if (!$user) {
            return $this->response->statusCode(401);
        }
        $user_id = $user['User']['id'];

        $bookmarks = $this->Bookmark->find('all', array(
                    'fields' => array(
                        'Bookmark.id', 'Bookmark.title',
                        'Bookmark.url', 'Bookmark.local_path', 'Bookmark.archive',
                        'Bookmark.created', 'Bookmark.modified',
                        ),
                    'conditions' => array('Bookmark.user_id' => $user_id),
                    'order' => array(
                        'Bookmark.created' => 'desc'
                        ),
                    'recursive' => -1,
                    ));
        return json_encode($bookmarks);
    }

    //--------------------------------------------------------------------------

    public function add_bookmark($access_token = null) {
        if (!$this->request->is('post')) {
            return $this->response->statusCode(400);
        }

        $user = $this->User->findByAccessToken($access_token);
        if (!$user) {
            return $this->response->statusCode(401);
        }
        $user_id = $user['User']['id'];

        $data = $this->request->input('json_decode', true);

        //-- add user_id
        $data['Bookmark']['user_id'] = $user_id;

        $bookmark = $this->Bookmark->find('first', array(
                    'conditions' => array(
                        'Bookmark.user_id' => $data['Bookmark']['user_id'],
                        'Bookmark.url' => $data['Bookmark']['url']
                        ),
                    'recursive' => -1,
                    ));

        $this->log($data, 'bookmark');
        $this->log($bookmark, 'bookmark');

        $this->Bookmark->create($data);
        if ($bookmark) {
            $this->Bookmark->id = $bookmark['Bookmark']['id'];
        }
        if (!$this->Bookmark->save()) {
            return $this->response->statusCode(500);
        }
    }

    //--------------------------------------------------------------------------

    public function download_bookmark($access_token = null, $id = null) {
        $user = $this->User->findByAccessToken($access_token);
        if (!$user) {
            //-- UnAuthorized...
            return $this->response->statusCode(401);
        }

        if ($user['User']['mobile_status'] !== 'VERIFIED') {
            //-- MethodNotAllowed...
            return $this->response->statusCode(405);
        }

        $user_id = $user['User']['id'];
        $bookmark = $this->Bookmark->find('first', array(
                    'conditions' => array(
                        'Bookmark.id' => $id,
                        'Bookmark.user_id' => $user_id
                        ),
                    'recursive' => -1,
                    ));

        if (!$bookmark || empty($bookmark['Bookmark']['archive'])) {
            //-- NotFound...
            return $this->response->statusCode(404);
        }

        if (!$bookmark['Bookmark']['downloaded']) {
            $this->Bookmark->id = $id;
            $this->Bookmark->set('downloaded', 1);
            $this->Bookmark->set('downloaded_at', date('Y-m-d H:i:s'));
            $this->Bookmark->save();
        }
        $this->redirect($bookmark['Bookmark']['archive']);
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

}
