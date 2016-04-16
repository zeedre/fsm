# FSM is for Finite State Machine
Not flying spaghetti monster alas. A very simple state machine tool that can help organize some code.

Heres an example of a basic sales process works
```
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
```  
Ok. Now I have a state machine that has some routes. Typically, setting up the fsm would be in a factory for easy access.

You can think of these routes as something that might happen to a lead, so lets give the fsm a lead object.  The only rules for whatever the state object is are that it must respond to methods getState() and saveState($state).  You'd probably tie these methods to a database object with a column named state or similar.
```

class lead {
  private $state = 'start';
  private $log = array();
  function __construct($info){ $this->log("lead : whoa! we got a $info"); }
  function saveState($state){ $this->state = $state;}
  function getState(){ return $this->state;}
  function log($note){ $this->log[] = $note;}
  function getLogs(){ return $this->log;}
}

$salesProcess->setStateObj(new lead("lead from contact page"));
```
This is all nice but really nothing will happen. Lets turn effects on which means that we'll actually run code during the transitions.
```
$fsm->addSetting("effects", true);
```
Now we need to define the code for when these state transitions occur. Lets create some classes. Note :
- the *name* of the class is the *current state*,
- the *name* of the function is the *state* being transitioned to
- return *true* to complete the state transition or *false* for leaving the tracked object in its current state (ie if something prevented the state change).

```
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

```

Finally, we can use it!
```
$salesProcess->transit("ummmm");  //<< error!
```
There is no route called ummmm. Your sales team may not be very good. An error is good so that you can track this anomaly. Going to an undefined state is something you did not plan for when you populated the state machine with routes.  So, now you have info and can go investigate.
```
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

Array
(
    [0] => lead : whoa! we got a lead from contact page
    [1] => rep : calling customer : called at 9am
    [2] => rep : sell to customer : I think he wants widgets!
    [3] => rep : duuude I sold : widgets sold! : qty 10!
    [4] => rep : paaarty : :( : done
)
```
Nice! the happy path. Always works... Usually.

But what if everything goes wrong...
```
$salesProcess->setStateObj(new lead("lead from early morning source"));

$salesProcess->transit("call_customer", "called at 2am");
$salesProcess->transit("sell_to_customer", "Oo, I think its too early, got earful, never call again");
$salesProcess->transit("done", "umm, shouldnt of called at 2am");

$lead = $salesProcess->getStateObj();

$whatHappened = $lead->getLogs();

print_r($whatHappened);

Array
(
    [0] => lead : whoa! we got a lead from early morning source
    [1] => rep : calling customer : called at 2am
    [2] => rep : sell to customer : Oo, I think its too early, got earful, never call again
    [3] => rep : cant sell :( : umm, shouldnt of called at 2am
)
```
The boss might have something to say . . .

So, the real joy here is that there is not a single if statement to support these complex logical paths.

In this simple example, there are 3 ways to get to the end state. Each way has a different set of code that runs when that transition is called.

Think about how awesome it is to get rid of ifs. Suddenly the code is hugely testeable!  

##Some settings

You can log by adding a log setting :
```
$salesProcess->addSetting("log", $calleable); // can be a lambda, string, or array
```
You may want to have a set naming scheme for your classes. So, instead of having a class named start, you might prefer sales_state_start :
```
$salesProcess->addSetting("class_prefix", 'sales_state_');
```
Currently state classes assume are already loaded or are available via some autoloader. Alternatively, you might want to specify a factory method for your classes via a calleable :
```
$calleable = function($name) use ($db){
  require_once("classes/$name.php");
  return new $name($db);
}

$salesProcess->set("state_class_factory", $calleable);
```

I've got most of the unit tests working. Coming soon! phpunit tests.
Try most of this example php tryme.php.