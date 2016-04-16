<?php
  class start {
    function call_customer($lead, $info){
      print "rep : calling customer : $info";
      return true;
    }
    function end($lead, $info){
      print "rep : closing lead : $info";
      return true;
    }
  }
  
  class call_customer {
    function sell_to_customer($lead, $info) {
      print "rep : sell to customer : $info";
      return true;
    }
    function end($lead, $info) {
      print "rep : couldnt call customer : $info";
      return true;
    }
  }
  
  class sell_to_customer {
    function customer_sold($lead, $info){
      //print "rep : duuude I sold : $info";
      return true;
    }
  }
  
  class end {
    //no functions needed here as there are no routes out of this state
  }
  
  $salesProcess->addState("start", array(
  "call_customer" => array(), //<-- is a route
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
))

  