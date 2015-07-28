<?php

require '../../../vendor/autoload.php';

\mcc\obj\sql::init('treTrial', 'root', 'temp');

die();

$db = \mcc\obj\sql::get();
//$db->execute('DELETE FROM events WHERE id > 0');
//$db->execute('DELETE FROM events_riders WHERE id > 0');

// Särkänniemen kilpailu
$start = '2015-07-19 09:00:00';
$data = array('name' => 'Särkänniemi 2015',
    'isPublic' => false,
    'startTime' => $start,
    'signupEnd' => '2015-07-07 21:00:00',
    'description' => 'BikeTrial Suomenmestaruussarjan osakilpailu 2/4');

$event = new \mcc\obj\trialEvents\repositories\event($data);

$categories = array(array('cat' => 'Elite', 'sect' => array()),
    array('cat' => 'A', 'sect' => array()),
    array('cat' => 'B', 'sect' => array()),
    array('cat' => 'C-Super', 'sect' => array()),
    array('cat' => 'C-Yleinen', 'sect' => array()),
    array('cat' => 'C-Vapaa', 'sect' => array()));

foreach ($categories as $cat) {
  $dataCat = array('category' => $cat['cat'], 'event_id' => $event->getid());
  $objCat = new \mcc\obj\trialEvents\repositories\category($dataCat);
  $dataComp = array('category_id' => $objCat->getid(),
      'startTime' => $start,
      'ridingTime' => 5 * 3600,
      'sections' => implode(',', $cat['sect']),
      'laps' => 3,
      'competition' => 'Finaali');
  $comp = new \mcc\obj\trialEvents\repositories\competition($dataComp);
}

print "\n\n===\n EVENTID:" . $event->getid() . PHP_EOL;
