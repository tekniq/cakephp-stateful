<?php
class StatefulComponent extends Object {
	var $components = array('Session');
	
	function initialize(&$controller, $settings = array()) {
		$this->controller =& $controller;		
		$this->_set($settings);
	}
	
	function startup() {
		$key = $this->controller->name . '.' . $this->controller->action;

		# Store current filter or load previous
		if (empty($this->controller->data['Filter'])) {
			if ($this->Session->check("Filter.$key")) {
				$this->controller->data['Filter'] = $this->Session->read("Filter.$key");
			}
		} else {
			if ($this->controller->data['Filter'] != (array)$this->Session->read("Filter.$key")) { 
				$this->Session->write("Filter.$key", $this->controller->data['Filter']);
				$this->controller->passedArgs['page'] = 1;
			}
		} 
		
		# Extract pagination variables from url parameters
		$pagination = array_intersect_key($this->controller->passedArgs, array_flip(array('page', 'sort', 'direction', 'limit')));
		
		# Merge pagination with stored session
		if ($this->Session->check("Pagination.$key")) {
			$pagination = array_merge($this->Session->read("Pagination.$key"), $pagination);
		}
		
		if (!empty($pagination)) {
			# Store pagination variables in session
			$this->Session->write("Pagination.$key", $pagination);
			
			# Merge pagination variables back into url params
			$this->controller->passedArgs = array_merge($this->controller->passedArgs, $pagination);
		}
	}
}