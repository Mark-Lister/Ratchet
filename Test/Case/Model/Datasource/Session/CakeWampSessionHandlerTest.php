<?php

/**
 * This file is part of Ratchet for CakePHP.
 *
 ** (c) 2012 - 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

App::uses('CakeWampSessionHandler', 'Ratchet.Model/Datasource/Session');

class CakeWampSessionHandlerTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();
		$_SESSION = array(
			'foo' => 'bar',
		);
		$this->handler = new CakeWampSessionHandler();
	}

	public function tearDown() {
		unset($this->handler);
		session_destroy();
		parent::tearDown();
	}

	public function testOpen() {
		$this->assertTrue($this->handler->open(null, null));
	}

	public function testClose() {
		$this->assertTrue($this->handler->close());
	}

	public function testRead() {
		$this->assertSame('a:1:{s:3:"foo";s:3:"bar";}', $this->handler->read('foo'));
	}

	public function testWrite() {
		$this->assertTrue($this->handler->write('foo', 'bar'));
	}

	public function testDestroy() {
		$this->assertTrue($this->handler->destroy('foo'));
	}

	public function testGc() {
		$this->assertTrue($this->handler->gc(1));
	}

}
