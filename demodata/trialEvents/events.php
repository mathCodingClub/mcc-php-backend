<?php

require '../../../vendor/autoload.php';

\mcc\obj\sql::init('treTrial', 'root', 'temp');

//\mcc\obj\trialEvents\services\events::sortCompetition(162);

//die();

$db = \mcc\obj\sql::get();
$db->execute('DELETE FROM events WHERE id > 0');
$db->execute('DELETE FROM events_riders WHERE id > 0');

// Särkänniemen kilpailu
$start = '2015-07-19 09:00:00';
$data = array('name' => 'Särkänniemi 2015',
    'isPublic' => false,
    'startTime' => $start,
    'signupEnd' => '2015-07-07 21:00:00',
    'description' => 'BikeTrial Suomenmestaruussarjan osakilpailu 2/4');

$event = new \mcc\obj\trialEvents\repositories\event($data);
// print_r($event->get());

$categories = array(array('cat' => 'Elite', 'sect' => array(1, 2, 3, 6, 7, 8)),
    array('cat' => 'A', 'sect' => array(1, 2, 3, 6, 7, 8)),
    array('cat' => 'B', 'sect' => array(1, 2, 3, 6, 7, 8)),
    array('cat' => 'C-Super', 'sect' => array(1, 2, 3, 4, 5, 6)),
    array('cat' => 'C-Yleinen', 'sect' => array(1, 2, 3, 4, 5, 6)),
    array('cat' => 'C-Vapaa', 'sect' => array(1, 2, 3, 4, 5, 6)));

$riders = array(array('cat' => 'Elite', 'gn' => 'Aleksi', 'fn' => 'Sulkanen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkaa', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkad', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkaf', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkag', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkaas', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkads', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkafs', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkags', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkaad', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkadd', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkafd', 'fn' => 'Nikunen'),
    array('cat' => 'Elite', 'gn' => 'Ilkkagd', 'fn' => 'Nikunen'),
    array('cat' => 'B', 'gn' => 'Niilo', 'fn' => 'Stenvall'),
    array('cat' => 'A', 'gn' => 'Markus', 'fn' => 'Peura')
);

foreach ($categories as $cat) {
  $data = array('category' => $cat['cat'], 'event_id' => $event->getid());
  $c = new \mcc\obj\trialEvents\repositories\category($data);
  $data = array('category_id' => $c->getid(),
      'startTime' => $start,
      'ridingTime' => 5 * 3600,
      'sections' => implode(',', $cat['sect']),
      'laps' => 3,
      'competition' => 'Finaali');
  $comp = new \mcc\obj\trialEvents\repositories\competition($data);
  foreach ($riders as $rider) {
    if ($rider['cat'] == $cat['cat']) {
      $signup = \mcc\obj\trialEvents\services\events::signUp($rider['gn'], $rider['fn'], $c);
      $rider = \mcc\obj\trialEvents\services\events::getRiderForSignUp($signup);
      $participation = \mcc\obj\trialEvents\services\events::setRiderToCompetition($rider, $comp);
      for ($ind = 0; $ind < 200; $ind++) {
        try {
          \mcc\obj\trialEvents\services\events::setPenaltyPoints($participation->getid(), rand(1, 8), rand(1, 3), rand(0, 5));
        } catch (\mcc\obj\mccException $e) {
          //print $e->getMessage() . PHP_EOL;
        }
      }
    }
  }
  $comp->sortParticipants();
}

//print_r(\mcc\obj\trialEvents\services\events::getFullEvent($event->getid()));
//print_r(\mcc\obj\trialEvents\services\events::getEvents());

print "\n\n===\n EVENTID:" . $event->getid() . PHP_EOL;
