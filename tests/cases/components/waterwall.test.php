<?php 
/* SVN FILE: $Id$ */
/* WaterwallTest Test cases generated on: 2010-04-22 10:46:25 : 1271925985*/

App::import('Behavior', 'Webservice'); 
App::import('Component', 'Waterwall');
App::import('Core', 'Xml');

/**
 * Base model that to load Webservice behavior on every test model.
 *
 * @package app.tests
 * @subpackage app.tests.cases.behaviors
 */
class WebserviceTestModel extends CakeTestModel
{
    /**
     * Behaviors for this model
     *
     * @var array
     * @access public
     */
    var $actsAs = array('Webservice' => array('defaultUrl' => 'www.google.it'));
    
    var $useTable = false;
} 

/**
 * Model used in test case.
 *
 * @package    app.tests
 * @subpackage app.tests.cases.behaviors
 */
class Service extends WebserviceTestModel {
    /**
     * Name for this model
     *
     * @var string
     * @access public
     */
    var $name = 'Service';
} 

class TestWaterwall extends WaterwallComponent {
}

class WaterwallTestComponent extends CakeTestCase {

	function startTest() {
		$this->Waterwall = new TestWaterwall();
		$this->Service =& new Service(); 
	}

	function testWaterwallInstance() {
		$this->assertTrue(is_a($this->Waterwall, 'WaterwallComponent'));
	}

	function testXssAttackLv1() {		
		$result = $this->Service->request('get', array('url' => 'http://ha.ckers.org/xssAttacks.xml')); 
		$xml = new Xml($result);
		$xmlAsArray = Set::reverse($xml);
		
		$_settings = array(
			'xss' => array(
				'level' => 1, //1,2,3 - 0 to disable
				'source' => 'REQUEST' // POST or GET or REQUEST (FILE coming soon)
			),
			'sql' => array(
				'level' => 0, //1,2,3 - 0 to disable
				'source' => 'REQUEST' // POST or GET or REQUEST (FILE coming soon)
			),
			'autoscan' => false	
		);
		$this->Waterwall->initialize($this, $_settings);
		foreach($xmlAsArray['Xss']['Attack'] as $attack){ 
			if($attack['code'] != "See Below"){     				
				$_REQUEST['test'] = $attack['code'];
				$result = $this->Waterwall->checkXSS();
				$this->assertNotEqual($result, 0);
			}
		}		
	}
	
	function testXssAttackLv2() {		
		$result = $this->Service->request('get', array('url' => 'http://ha.ckers.org/xssAttacks.xml')); 
		$xml = new Xml($result);
		$xmlAsArray = Set::reverse($xml);
		
		$_settings = array(
			'xss' => array(
				'level' => 2, //1,2,3 - 0 to disable
				'source' => 'REQUEST' // POST or GET or REQUEST (FILE coming soon)
			),
			'sql' => array(
				'level' => 0, //1,2,3 - 0 to disable
				'source' => 'REQUEST' // POST or GET or REQUEST (FILE coming soon)
			),
			'autoscan' => false	
		);
		$this->Waterwall->initialize($this, $_settings);
		foreach($xmlAsArray['Xss']['Attack'] as $attack){    
			if($attack['code'] != "See Below"){     				
				$_REQUEST['test'] = $attack['code'];
				$result = $this->Waterwall->checkXSS();
				$this->assertNotEqual($result, 0);	
			}
		}		
	}
	
	function testXssAttackLv3() {		
		$result = $this->Service->request('get', array('url' => 'http://ha.ckers.org/xssAttacks.xml')); 
		$xml = new Xml($result);
		$xmlAsArray = Set::reverse($xml);
		
		$_settings = array(
			'xss' => array(
				'level' => 3, //1,2,3 - 0 to disable
				'source' => 'REQUEST' // POST or GET or REQUEST (FILE coming soon)
			),
			'sql' => array(
				'level' => 0, //1,2,3 - 0 to disable
				'source' => 'REQUEST' // POST or GET or REQUEST (FILE coming soon)
			),
			'autoscan' => false	
		);
		$this->Waterwall->initialize($this, $_settings);
		foreach($xmlAsArray['Xss']['Attack'] as $attack){ 
		if($attack['code'] != "See Below"){        				
			$_REQUEST['test'] = $attack['code'];
			$result = $this->Waterwall->checkXSS();
			$this->assertNotEqual($result, 0);
		}
		}		
	}

	function testSqlAttack() {

	}
	
	function testFileAttack(){				
		// Create a test source image 
		$im = imagecreatetruecolor(300, 50);
		$text_color = imagecolorallocate($im, 233, 14, 91);
		imagestring($im, 1, 5, 5,  'Hacked image', $text_color);
		// output jpeg  format & quality
		imagejpeg($im, ROOT.DS.APP_DIR.DS.'tmp/hackimg.jpg', 85);
		
		// lastly we are writing the string to a file
		$fh = fopen(ROOT.DS.APP_DIR.DS.'tmp/hackimg.jpg', "a+" );
		fwrite( $fh, '<?php echo "hacked" ?>' );
		fclose( $fh );
		
		$_FILES = array('upload' => array('tmp_name' => ROOT.DS.APP_DIR.DS.'tmp/hackimg.jpg','error' => 0));
        
		$result = $this->Waterwall->checkFILE();
		$this->assertNotEqual($result, 0);
		unlink(ROOT.DS.APP_DIR.DS.'tmp/hackimg.jpg');	
	}
}
?>