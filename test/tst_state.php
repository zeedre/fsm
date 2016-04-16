<?php

	class tst_state {
	
		public static $calls = array();
		public static $pargs = array();
		
		function __call($name, $args){

			$args = array_slice($args, 1);
			tst_state::$calls[] = get_class($this) . "::" . $name;
			tst_state::$pargs[] = $args;

			$matches = array();
			
			if (preg_match("/^return_(.*)/", $name, $matches)){
				return $matches[1];
			}
				
			return ! preg_match("/false$/", $name);
		}
	}
	
	class tst_state_start extends tst_state {}
	class tst_state_step1 extends tst_state {}
	class tst_state_step1_false extends tst_state {}
	class tst_state_done extends tst_state {}
		
	class tst_log extends tst_state {
		function __invoke(){
			tst_state::$calls[] = __class__ . "::" . 'class_logging';
		}
	}
		
	function test_logging(){
		$a = func_get_args();
		$f = new tst_log;
		call_func_array(array($f, "log"), $a);
	}