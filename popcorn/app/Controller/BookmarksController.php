<?php
App::uses('AppController', 'Controller');
/**
 * Bookmarks Controller
 *
 * @property Bookmark $Bookmark
 */
class BookmarksController extends AppController {

    public $autoRender = false;
    public $uses = array('Bookmark', 'User');

    //--------------------------------------------------------------------------

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('download');

        $user = $this->User->read(null, $this->Auth->user('id'));
        if (!$user || $user['User']['mobile_status'] !== 'VERIFIED') {
            $this->redirect(array('controller' => 'users', 'action' => 'index'));
        }
    }

    //--------------------------------------------------------------------------

    /**
     * index method
     *
     * @return void
     */
    public function index() {
        $user_id = $this->Auth->user('id');
        $this->paginate = array(
                'conditions' => array('Bookmark.user_id' => $user_id),
                'order' => array(
                    'Bookmark.id' => 'asc'
                    ),
                'limit' => 10
                );
        $this->set('bookmarks', $this->paginate());
        $this->render('index');
    }

    /**
     * view method
     *
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        $this->Bookmark->id = $id;
        if (!$this->Bookmark->exists()) {
            throw new NotFoundException(__('Invalid bookmark'));
        }
        $this->set('bookmark', $this->Bookmark->read(null, $id));
        $this->render('view');
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            //-- set user_id
            $this->request->data['Bookmark']['user_id'] = $this->Auth->user('id');

            $this->Bookmark->create();
            if ($this->Bookmark->save($this->request->data)) {
                $this->Session->setFlash(__('The bookmark has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The bookmark could not be saved. Please, try again.'));
            }
        }
        $this->render('add');
    }

    /**
     * edit method
     *
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        $this->Bookmark->id = $id;
        if (!$this->Bookmark->exists()) {
            throw new NotFoundException(__('Invalid bookmark'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Bookmark->save($this->request->data)) {
                $this->Session->setFlash(__('The bookmark has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The bookmark could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->Bookmark->read(null, $id);
        }
        $users = $this->Bookmark->User->find('list');
        $this->set(compact('users'));
        $this->render('edit');
    }

    /**
     * delete method
     *
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->Bookmark->id = $id;
        if (!$this->Bookmark->exists()) {
            throw new NotFoundException(__('Invalid bookmark'));
        }
        if ($this->Bookmark->delete()) {
            $this->Session->setFlash(__('Bookmark deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Bookmark was not deleted'));
        $this->redirect(array('action' => 'index'));
    }

    //--------------------------------------------------------------------------

    public function download($id = null) {
        $this->Bookmark->id = $id;
        $this->Bookmark->recursive = -1;
        $bookmark = $this->Bookmark->read();

        if ($bookmark && $bookmark['Bookmark']['archive']) {
            if (!$bookmark['Bookmark']['downloaded']) {
                $this->Bookmark->set('downloaded', 1);
                $this->Bookmark->set('downloaded_at', date('Y-m-d H:i:s'));
                $this->Bookmark->save();
            }
            $this->redirect($bookmark['Bookmark']['archive']);
        } else {
            throw new NotFoundException(__('Invalid bookmark'));
        }
    }
    //==========================================================================
    //-- json output

    public function get_all() {
        $user_id = $this->Auth->user('id');
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

    public function save() {
        if (!$this->request->is('post')) {
            return $this->response->statusCode(400);
        }

        $data = $this->request->input('json_decode', true);

        //-- add user_id
        $user_id = $this->Auth->user('id');
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

}
