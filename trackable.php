<?php
	class trackable {
		
		private $state = 'start';
	
		function saveState($state){
			print "trackable : saving state to $state\n";
			$this->state = $state;
		}
	
		function getState(){
			return $this->state;
		}
		
	}