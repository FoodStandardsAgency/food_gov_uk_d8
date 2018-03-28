# Notify API and SMS/Email sending functionality

This functionality is originally built by ragnar.kurm@wunder.io

People involved:
* Tomi Mikola 
* Timo Kirkkala

All notification related stuff lives in one module `fsa_notify`.

## TODO

Decisions and/or further investigation needed:
* Need to decide and work with what happens when sending fails to particular 
user. Stop sending altogether? Or continue? We dont know if this is some common 
error or just isolated case. In `src/FsaNotifyAPI*.php`, see `send()` method.
* Email bounce handling
* SMS bounce handling
* Multilingual functionality

## Known bugs

* In very rare cases people may get two non-overlapping digests for same period.
 It may happen when the system is not able to send all messages out in one cron 
 shot (very exceptional cases). And in between multiple attempts there is added 
 new content which goes to the digest. Likelihood for this event is virtually 
 nonexistent as sending is expected almost always to succeed (within say 15min)
 in first attempt and there are only few alerts per day.

## Configuration

### UI

Configure > FSA configuration > Notify

That page contains three things:
* Edit current configuration settings (key and id-s)
* Option to turn off alerts collecting and sending.
* Basic stats of subscribers

### State

Basic configuration parameters are held in Drupal state variables and can be 
managed by UI (or `drush`).

* `fsa_notify.api` - Notify API key
* `fsa_notify.template_sms` - Notify API SMS Template ID
* `fsa_notify.template_email` - Notify API Email Template ID
* `fsa_notify.collect_send_log_only` - Debug mode to collect alerts but only 
writing the content to log 

Runtime state variables are:

* `fsa_notify.last_daily` - timestamp of last time when daily was finished last 
time.
* `fsa_notify.last_weekly` - timestamp of last time when weekly was finished 
last time.

## API keys and Template IDs

Acquired from https://www.notifications.service.gov.uk/

Consult LastPass, Luke or Sally for access. We have a shared team.

1) One set is API keys, which specify mode you want to use the service:
* Live – sends to anyone.
* Team and whitelist – limits who you can send to
* Test – pretends to send messages

2) Other set is Template IDs which specify different kind of templates to use.

## Fields used

### user.field_notification_method DEPRECATED in favor of field_email_frequency

User can choose the email frequency for getting updates from the site.

### user.field_notification_sms

If user chooses SMS method, where to sent those?
This field is enforced then SMS method is chosen.
This field has additional check by `Field validation` module for data integrity:
* prevent spaces
* require the number begin with plus sign followed by numbers

### user.field_notification_cache

This field is not visible for user, but here we collect alerts per user to be 
sent.

When alerts are sent out, this field is emptied.

### user.field_subscribed_notifications

List of allergens user has signed up to.

### node.field_alert_send

Boolean field to trigger sending a node (news or consultation) to notify sending
 queue.

### node.field_alert_send_timestamp

Date field to store if node (news or consultation) was sent to sending queue.

## What happens when new alert is created?

News and Consultation content type items behave similarly when editor selects
the `field_alert_send` checkbox on node edit form.

* First node is queued for processing.
* Then during cron run the alert will be cached to every user who has signed up
for particular terms.
* During send-out event all cached notifications are sent out and user cache
emptied.

## Cron

During cron run following things happen:
* Queue is processed for each queued alert and the alerts will be reference from
user caches according to user allergen sign up field.
* All SMS messages are sent out
* All immediate messages are sent out
* All daily messages are sent out - if it is appropriate time
* All weekly messages are sent out - if it is appropriate time

Digest sending times can be configured in following functions:
* `fsa_notify_daily_is_ready_to_send()`
* `fsa_notify_weekly_is_ready_to_send()`

## Queue

Alerts are queued immediately. Drupal Queue is used. Because processing of them
takes considerable time. Might be 1-2minutes per queue item.

Queue processing distributes alert references to users who have signed up for
them.

## Sending out SMS and Email

It means looping through all users who have something to send out.
Message body is constructed and then sent.

## Timing

Since sending out messages are quite slow process and in order to track
performance and have some stats there is timer functionality included which logs
some stats only if something is sent out to Drupal Watchdog.

```
Timer: type weekly; elapsed 164.101; 968 items; 5.899 items/sec.
Timer: type daily; elapsed 168.791; 1020 items; 6.043 items/sec.
Timer: type immediate; elapsed 1.250; 2 items; 1.600 items/sec.
Timer: type sms; elapsed 17.924; 989 items; 55.177 items/sec.
Timer: type queue; elapsed 73.326; 1 items; 0.014 items/sec.
```

## Digest times

In `fsa_notify.module` there is functionality which decides if it is time to
send out daily or weekly.
Here follows ho it is decided.
There are 2 major factors:
* There has to be passed enough time since last time (a bit less than a day or a
bit less than a week).
* It has to be certain time period of day or week when notification delivery may
happen.

There is time window (few hours) during which the sending may happen. It is few
hours because if sending fails for whatever reason it will be attempted again.

For details, please refer to following functions:
* `fsa_notify_daily_is_ready_to_send()` - check if we can send out daily digests
now
* `fsa_notify_weekly_is_ready_to_send()` - check if we can send out weekly
digests now
* `fsa_notify_is_ready_to_send()` - underlying general functionality for
previous functions

When digest is sent (successfully finished), then is recorded by
`fsa_notify_sent()`. It is needed to calculate if enough time is passed since 
last time.

## If you need to change how a notification looks

Check following files `src/FsaNotifyMessage*.php`.

## Class FsaNotifyStorage

This class takes care of following:
* storing/distributing alert items to users based on their preferences
* retrieving messages for chunk of users with particular sending method
* theming/assembling messages according to sending method
* clearing user cache

Chunking is used to prevent Drupal cache saturation subsequent OOM event.

Everything here revolves around field `user.field_notification_cache`.

## Notify Callback

SMS sent to Notify number +44(0)7860064543 creates a callback defined in [Notify settings](https://www.notifications.service.gov.uk/services/6f00837a-4b8f-4ddd-ae96-ca2d3035fe57/service-settings/set-inbound-api)

* `fsa_notify.callback_url` route defines the callback.
   * `sms()` takes the SMS body and takes required actions.

## Testing

* On `/admin/config/fsa/notify` 
  * "*Collect notifications and send out to subscribers*" Selected. This will 
  send the API requsts to Notify.
  * "*Debug mode*" keeps collecting alerts but only log the requests that would 
  be sent to notify but. Excecution is terminated before the API call.
 
Use `Makefile` in `fsa_notify/testing` directory for creating alert nodes. (To 
test news/consultations alerts edit the content manually)

* Use testing keys in `/admin/config/fsa/notify` (or the actual keys if using 
debug mode)
* have 1 user in your local with at least 1 allergy.
* clear local alerts and possibly migration data
  * `drush mr --tag=alerts`
* run the import
  * `drush mi --tag=alerts`
* Or use `make alert_add` to add arbitrary nonsense-alert
  * Creates AA alert by default, pass type (AA/PRIN/FAFA) as parameter:
    for example `make alert_add TYPE=FAFA`
* Run `make test`
  * Have `drush wd-show --tail --full --extended &` open in another tab
* View the message logs at [Notify api tab](https://www.notifications.service.gov.uk/services/6f00837a-4b8f-4ddd-ae96-ca2d3035fe57/api)
  * Or local logs if debugging mode enabled.

## Files in this module

* `fsa_notify.module`
  * Phone number enforcement in user profile
  * Highlevel execution of Queue processing
  * Cron hook to initiate all functionality in this module - alert distribution and sending
  * Functionality to decide when to send daily or weekly digests
  * Highlevel sending of all types of notifications
* `src/FsaNotifyAPI.php`
  * Abstract class
  * Connect to Notify API
* `src/FsaNotifyAPIemail.php`
  * General Email sending
* `src/FsaNotifyAPIsms.php`
  * General SMS sending
* `src/FsaNotifyMessage.php`
  * Abstract class
  * Themes a node
  * Assembles nodes into a message / messages
* `src/FsaNotifyMessageSms.php`
  * Construct SMS messages
* `src/FsaNotifyMessageImmediate.php`
  * Construct Immediate messages
* `src/FsaNotifyMessageDaily.php`
  * Construct Daily message
* `src/FsaNotifyMessageWeekly.php`
  * Construct Weekly message
* `src/FsaNotifyStorage.php`
  * Notification storing
  * Notification retrieval in chunks per type in themed form
* `src/Form/FsaSettings.php`
  * Edit key and id-s
  * Enable/Disable distributing and sending notifications
  * Enable/disable notification sending debug mode
  * Enable/disable callback error logging
  * Basic stats
* `src/Plugin/QueueWorker/FsaNotifyStorageQueue.php`
  * Item processing (storing / distributing)  
* `src/FsaNotifyReceive.php`
  * Notify SMS callback for processing the requests
    * Unsubscribing feature.

## Related modules and packages

* alphagov/notifications-php-client
* drupal/field_validation
* php-http/guzzle6-adapter

## Related URL-s

* https://www.notifications.service.gov.uk
* https://github.com/alphagov/notifications-php-client
* https://www.notifications.service.gov.uk/integration_testing
