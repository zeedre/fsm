<?php


	class fsm_illegal_transition_attempt extends RuntimeException {}
	class fsm_state_class_invalid extends RuntimeException {}
	class fsm_state_class_has_no_transit_method extends RuntimeException {}
	class fsm_no_states_defined extends RuntimeException {}
	class fsm_invalid_state extends RuntimeException {}

	class fsm {
		
		private $settings;
		private $states = array();
		private $stateObj;
		
		function __construct($more = array("effects"=> true) ){
			$this->settings = $more;
		}
		
		public function addSetting($name, $setting){
			$this->settings[$name] = $setting;
		}
		
		public function addStateObj($obj){
			$this->stateObj = $obj;
			
			//TODO check curent state is in states list
		}
		
		public function getStateObj(){
			return $this->stateObj;
		}
		
		public function addState($name, $paths){
			$this->states[$name] = $paths;
		}
		
		public function transit($newState, $transitionData = array()){
			
			if (empty($this->states)) throw new fsm_no_states_defined;
			
			$currentState = $this->getState();
			
			if (! isset($this->states[$currentState])){
				throw new fsm_invalid_state("currently in $currentState which does not exist");
			}

			if (! array_key_exists($newState, $this->states[$currentState])){
				throw new fsm_illegal_transition_attempt("\"" . $newState . "\" is not an option for \"" . $currentState . "\"");
			}
			
			$transitionInfo = $this->states[$currentState][$newState];
			
			$result = $this->doTransit($currentState, $newState, $transitionData, $transitionInfo);

			if ( is_bool($result) && $result == True){
				$this->saveState($newState);
	
				if (isset($transitionInfo['auto_next_state'])){
					$result = $this->transit($transitionInfo['auto_next_state'], $transitionData);
				}
			} 
			
			if (is_string($result)) {
				$this->saveState($newState);
				$result = $this->transit($result, $transitionData);
			}

			return $result;
		}
		
		private function doTransit($currentState, $newState, $transitionData, $transitionInfo){
	
			$transitResult = true;
			
			if (!empty($this->settings['effects'])){
				$className = isset($this->settings['class_prefix']) ? $this->settings['class_prefix'] : "";
				$className .= $currentState;
				
				$cls = $this->generateStateClass($className);
				
				if (! is_callable(array($cls, $newState))){
					throw new fsm_state_class_has_no_transit_method("\"" . $className. "\" does not have method \"" . $newState . "\"");
				}
				
				$transitResult = $cls->$newState($this->stateObj, $transitionData, $transitionInfo);
			}
			
			if (isset($this->settings['log'])){
				$log = $this->settings['log'];
				call_user_func ( $log, "did " . (!$transitResult ? "NOT" : "") . " transit from $currentState to $newState");		
			}
			
			return $transitResult;
		}
		
		public function getState(){
			//TODO add settings-eable functionality for getting state
			return $this->stateObj->getState();
		}
		
		public function generateStateClass($className){
				if (isset($this->settings["state_class_factory"])){
					$clsFactory = $this->settings["state_class_factory"];
					$cls = $clsFactory($className);
				} else {
					if (!class_exists($className)){
						throw new fsm_state_class_invalid("\"" . $className. "\" does not exist");
					}
					$cls = new $className();
				}
				return $cls;
		}
		
		private function saveState($newState){
			$this->stateObj->saveState($newState);
		}
		
		public function testConfig(){
			$declared = array();
			print "<h1>States</h1>";
			foreach ($this->states as $stateName => $state){
				print "<h2>state : $stateName</h2>";
				if (in_array($stateName, $declared)) {
					print "Already declared!";
					continue;
				} else $declared[] = $stateName;
				$clsName = isset($this->settings['class_prefix']) ? $this->settings['class_prefix'] : "";
				$clsName .= $stateName;
				if (!class_exists($clsName)) print "has no class";
				foreach ($state as $stepName => $stepinfo){
					print "<h3>mthd : $stepName</h3>";
					if (!is_callable(array($clsName, $stepName))){
						print "<h6>has no method $stepName</h6>";
					}
					$cclsName = isset($this->settings['class_prefix']) ? $this->settings['class_prefix'] : "";
					$cclsName .= $stepName;
					if (!class_exists($cclsName)) print "step $stepName has no class";
				}
				
			}
		}
	}