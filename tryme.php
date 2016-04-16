<?php

require_once("fsm.php");
$salesProcess = new fsm();

$salesProcess->addState("start", array(
  "call_customer" => array(),//<-- is a route
  "end" => array() //<-- another route
));

$salesProcess->addState("end", array());

$salesProcess->addState("call_customer", array(
  "sell_to_customer" => array(),
  "end" => array()
));

$salesProcess->addState("sell_to_customer", array(
  "customer_sold" => array(),
  "end" => array()
));

$salesProcess->addState("customer_sold", array(
  "end" => array()
));

class lead {
  private $state = 'start';
  private $log = array();
  function __construct($info){ $this->log("lead : whoa! we got a $info"); }
  function saveState($state){ $this->state = $state;}
  function getState(){ return $this->state;}
  function log($note){ $this->log[] = $note;}
  function getLogs(){ return $this->log;}
}

$salesProcess->addStateObj(new lead("lead from contact page"));

$salesProcess->addSetting("effects", true);

class start {
  function call_customer($lead, $info){
    $lead->log("rep : calling customer : $info");
    return true;
  }
  function end($lead, $info){
    $lead->log("rep : closing lead : $info");
    return true;
  }
}

class call_customer {
  function sell_to_customer($lead, $info) {
    $lead->log("rep : sell to customer : $info");
    return true;
  }
  function end($lead, $info) {
    $lead->log("rep : couldnt call customer : $info");
    return true;
  }
}

class sell_to_customer {
  function customer_sold($lead, $info){
    $lead->log("rep : duuude I sold : $info");
    return true;
  }
  function end($lead, $info){
    $lead->log("rep : cant sell :( : $info");
    return true;
  }
}

class customer_sold {
  function end($lead, $info){
    $lead->log("rep : paaarty : :( : $info");
    return true;
  }
}

class end {
  //no functions needed here as there are no routes out of this state
}

try {
  $salesProcess->transit("ummmm");  //<< error!
} catch (fsm_illegal_transition_attempt $e ){}
$salesProcess->transit("call_customer", "called at 9am"); //<-- they called the customer
$salesProcess->transit("sell_to_customer", "I think he wants widgets!");
$salesProcess->transit("customer_sold", "widgets sold! : qty 10!");
$salesProcess->transit("end", "done"); 
try {
  $salesProcess->transit("start", "Lets see if I can just try again"); //<-- error! there is no route from end to start.  This is also an anomaly. Go investigate.
} catch (fsm_illegal_transition_attempt $e ){}

$lead = $salesProcess->getStateObj();
$whatHappened = $lead->getLogs();

print_r($whatHappened);

$salesProcess->addStateObj(new lead("lead from early morning source"));
$salesProcess->transit("call_customer", "called at 2am");
$salesProcess->transit("sell_to_customer", "Oo, I think its too early, got earful, never call again");
$salesProcess->transit("end", "umm, shouldnt of called at 2am");

$lead = $salesProcess->getStateObj();
$whatHappened = $lead->getLogs();

print_r($whatHappened);
