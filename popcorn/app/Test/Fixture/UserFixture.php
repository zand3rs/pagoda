<?php
/* User Fixture generated on: 2012-03-19 16:39:54 : 1332175194 */

/**
 * UserFixture
 *
 */
class UserFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'collate' => NULL, 'comment' => ''),
		'email' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 128, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'mobile' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'key' => 'unique', 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'pin_code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'pin_expiry' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'date_registered' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'collate' => NULL, 'comment' => ''),
		'auth_code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'access_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'refresh_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'token_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'token_expiry' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 6, 'collate' => NULL, 'comment' => ''),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'email_uk' => array('column' => 'email', 'unique' => 1), 'mobile_uk' => array('column' => 'mobile', 'unique' => 1)),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'email' => 'Lorem ipsum dolor sit amet',
			'mobile' => 'Lorem ipsum dolor sit amet',
			'pin_code' => 'Lorem ipsum do',
			'pin_expiry' => '2012-03-19 16:39:54',
			'date_registered' => 1332175194,
			'auth_code' => 'Lorem ipsum dolor sit amet',
			'access_token' => 'Lorem ipsum dolor sit amet',
			'refresh_token' => 'Lorem ipsum dolor sit amet',
			'token_type' => 'Lorem ipsum dolor sit amet',
			'token_expiry' => 1,
			'created' => '2012-03-19 16:39:54',
			'modified' => '2012-03-19 16:39:54'
		),
	);
}
