<?php
require('SystemHealth.Class.php');
$System = new SystemHealth();
echo $System->_uptime;
echo '<br>';
echo $System->_cpuload;
echo '<br>';
echo $System->_freemem;
echo '<br>';
echo $System->_freehdd;
echo '<br>';
echo $System->CheckServiceStatus('freeradius');
