<?php

$state_key = 'fsa_notify.last_weekly';
$time = time();
for ($i = 0 ; $i < 500 ; $i++) {
  $status = fsa_notify_weekly_is_ready_to_send($state_key, $time);
  $status = $status ? "TRUE" : "-";
  printf("%s: %s\n", date('Y.m.d D H:i:s', $time), $status);
  $time += 60 * 60;
}
