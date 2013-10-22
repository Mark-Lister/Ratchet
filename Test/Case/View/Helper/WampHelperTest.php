<?php

/**
 * This file is part of Ratchet for CakePHP.
 *
 ** (c) 2012 - 2013 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

App::uses('View', 'View');
App::uses('WampHelper', 'Ratchet.View/Helper');
App::uses('HtmlHelper', 'View/Helper');
App::uses('AssetCompressHelper', 'AssetCompress.View/Helper');


class RatchetHelperTest extends CakeTestCase {

/**
 * start a test
 *
 * @return void
 **/
	public function setUp() {
		parent::setUp();

		$controller = null;
		$request = new CakeRequest(null, false);
		$request->webroot = '';
		$this->view = new View($controller);
		$this->view->request = $request;
		$this->Helper = new WampHelper($this->view, array('noconfig' => true));
		$this->Helper->Html = $this->getMock('HtmlHelper', array('scriptBlock'), array($this->view));
		$this->Helper->AssetCompress = $this->getMock('AssetCompressHelper', array('script'), array($this->view, array('noconfig' => true)));

		Router::reload();
	}

/**
 * end a test
 *
 * @return void
 **/
	public function tearDown() {
		parent::tearDown();
		unset($this->Helper);
	}

/**
 * test the script includes generated by init
 *
 * @return void
 */
	public function testInit() {
		$expectedScriptBlock = "WEB_SOCKET_SWF_LOCATION = \"http://localhost/Ratchet/swf/WebSocketMain.swf\";\nvar cakeWamp = window.cakeWamp || {};\ncakeWamp.options = {retryDelay: 5000,maxRetries: 25};\nvar wsuri = \"ws://localhost:80/websocket\";";
		$this->Helper->Html->expects($this->once())
			->method('scriptBlock')
			->with($expectedScriptBlock, $this->equalTo(array(
				'inline' => false,
			)));

		$this->Helper->AssetCompress->expects($this->once())
			->method('script')
			->with($this->equalTo('Ratchet.wamp'), $this->equalTo(array('block' => 'script')));

		$this->Helper->init();
	}

}
