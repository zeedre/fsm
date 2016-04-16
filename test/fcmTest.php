<?php
require_once("fsm.php");
require_once("test/trackable.php");
require_once("test/tst_state.php");

class FCMtest extends PHPUnit_Framework_TestCase {
	
	function setUp(){
		$fsm = new fsm;
		$fsm->addStateObj(new trackable);
		$fsm->addSetting("effects", false);
		$this->fsm = $fsm;
		tst_state::$calls = array();
		tst_state::$pargs = array();
	}
  
	public function testStartState() {
		$this->assertTrue($this->fsm->getState() == 'start', "must start in state 'start'");
	}
	
	/**
	 * @expectedException fsm_no_states_defined
	 */
  public function testTransitwNoConfigFSMErrror(){
		$this->fsm->transit("undefindedstate");
  }
	
	/**
	 * @expectedException fsm_illegal_transition_attempt
	 */
  public function testTransitNowhereSendsFSMErrror(){
		$this->fsm->addState("start", array());
		$this->fsm->transit("undefindedstate");
  }
	
  public function testTransitWorksNOEFFECT(){
		$this->fsm->addSetting("effects", false);
		$this->fsm->addState("start", array("step1" => array()));
		$this->fsm->addState("step1", array());
	
		$this->fsm->transit("step1");
		
		$this->assertTrue($this->fsm->getState() == "step1");
  }
	
  public function testTransitwautoNextStepWorksNOEFFECT(){
		$this->fsm->addSetting("effects", false);
		$this->fsm->addState("start", array("step1" => array("auto_next_state" => "step2")));
		$this->fsm->addState("step1", array("step2" => array()));
	
		$this->fsm->transit("step1");
		
		$this->assertTrue($this->fsm->getState() == "step2");
  }
	
  public function testTransitWorks(){
		$this->fsm->addSetting("effects", true);
		$this->fsm->addSetting("class_prefix", 'tst_state_');
		$r1 = $this->fsm->addState("start", array("step1" => array()));
		$r2 = $this->fsm->addState("step1", array("step1_false" => array()));
		//$this->fsm->addState("done", array());
	
		$this->fsm->transit("step1");
		
		$this->assertTrue($this->fsm->getState() == "step1");
  }
    
  public function testTransit(){
		$this->fsm->addSetting("effects", true);
		$this->fsm->addSetting("class_prefix", 'tst_state_');
		$this->fsm->addState("start", array("step1" => array()));
		$this->fsm->addState("step1", array("step1_false" => array(), "done"=>array()));
		$this->fsm->addState("done", array());
	
		$r1 = $this->fsm->transit("step1");
		$this->assertTrue($r1, "Result should be true");
		
		$r2 = $this->fsm->transit("step1_false");
		$this->assertFalse($r2, "Result should be false");
		$this->assertTrue($this->fsm->getState() == "step1");
		
		$r3 = $this->fsm->transit("done");
		$this->assertTrue($r3, "Result should be true");
		$this->assertTrue($this->fsm->getState() == "done");
				
		$tst = array(
			"tst_state_start::step1",
			"tst_state_step1::step1_false",
			"tst_state_step1::done"
		);
		//print_r(tst_state_start::$calls);
		$this->assertEquals($tst, tst_state_start::$calls, "history does not match");
  }
	
  public function testLogging(){
		$this->fsm->addSetting("effects", true);
		$this->fsm->addSetting("class_prefix", 'tst_state_');
		$log_obj = new tst_log;
		$this->fsm->addSetting("log", array($log_obj, "class_log"));
		$this->fsm->addState("start", array("step1" => array()));
		$this->fsm->addState("step1", array("step1_false" => array(), "done"=>array()));
		$this->fsm->addState("done", array());
	
		$this->fsm->transit("step1");
		
		$this->fsm->transit("step1_false");
		
		$this->fsm->transit("done");
		
		$tst = array(
			"tst_state_start::step1",
			"tst_log::class_log", 
			"tst_state_step1::step1_false",
			"tst_log::class_log",
			"tst_state_step1::done",
			"tst_log::class_log"
		);
		
		//print_r(tst_state_start::$calls);
		$this->assertEquals($tst, tst_state_start::$calls , "history does not match");
  }
	
	public function testPassRightVarsInAutotransit(){
		$this->fsm->addSetting("effects", true);
		$this->fsm->addSetting("class_prefix", 'tst_state_');
		$log_obj = new tst_log;
		$this->fsm->addSetting("logging", array($log_obj, "class_log"));
		$this->fsm->addState("start", array("step1" => array("auto_next_state" => "done")));
		$this->fsm->addState("step1", array(
			"done"=>array())
		);
		$this->fsm->addState("done", array());
	
		$this->fsm->transit("step1", array("cats"));
		
		$tst = array(
			"tst_state_start::step1",
			"tst_log::class_log", 
			"tst_state_step1::done",
			"tst_log::class_log"
		);
		
		$args = tst_state_start::$pargs;

		$this->assertEquals($tst, tst_state_start::$calls , "history does not match");
		$this->assertEquals($args[0][0][0], 'cats', "call args of 0 not being passed, should be cats");
		$this->assertEquals($args[1][0][0], 'cats', "call args of 1 not being passed, should be cats");
  }
	
}
