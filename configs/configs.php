<?php

// Secret for token generations
define ( 'SECRET', '' );
define( 'PID', getmypid() );
define( 'TMPFILE', "/dev/shm/" . PID . ".json" );

define( 'KNIFE_OPT', '-c ' . PATH_CHEF . 'knife.rb');
