# FSM is for Finite State Machine
Not flying spaghetti monster alas. A very simple state machine tool that can help organize some code.

Heres an example of how works albeit a bit useless.
```
$fsm = new fsm();
$fsm->addState("start", array(
  "do_something" => array(), //<-- is a route
  "end" => array() //<-- another route
));

$fsm->addState("end", array());

$fsm->addState("do_something", array(
 "end" => array()
));
```  
Ok. Now I have a state machine that has some routes. Typically, this would be in a factory for easy access.  Lets use it.
```
$fsm->transit("nowhere");  //<< error! there is no route called nowhere. 
```
An error is good so that you can track this anomaly. Going to an undefined state is something you did not plan for when you populated the state machine with routes.  So, now you have info and can go investigate.
```
$fsm->transit("do_something"); //<-- returns true! it worked!
$fsm->transit("end"); //<-- also returns true! it worked again!
$fsm->transit("start"); //<-- error! there is no route from end to start.  This is also an anomaly. Go investigate.
```
