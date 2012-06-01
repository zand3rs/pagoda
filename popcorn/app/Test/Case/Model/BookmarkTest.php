<?php
/* Bookmark Test cases generated on: 2012-03-21 05:19:23 : 1332307163*/
App::uses('Bookmark', 'Model');

/**
 * Bookmark Test Case
 *
 */
class BookmarkTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.bookmark', 'app.user');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Bookmark = ClassRegistry::init('Bookmark');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Bookmark);

		parent::tearDown();
	}

}
