<?php
	class trackable {
		
		private $state = 'start';
	
		function saveState($state){
			$this->state = $state;
		}
	
		function getState(){
			return $this->state;
		}
		
	}