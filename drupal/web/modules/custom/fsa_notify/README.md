# Notify API and SMS/Email sending functionality

This functionality is originally built by ragnar.kurm@wunder.io

Tomi Mikola has been also involved.

## TODO

Can be done immediately:
* Need to decide and work with what happens when sending fails to particular user. Stop sending altogether? Or continue? In FsaNotifyAPI.php, sms() and email() methods.
* Formtting - Theming, assembly etc, move to own classes
* Statistics - create basic stats of blocked & methods to conf page

Decisions and/or futher investigation needed:
* There are overlapping fields: field_notification_allergys and	field_subscribed_notifications. This module is built with the former.
* There are no food nor news alerts yet
* Unsubscribe by email functionality
* Optout by sms functionality
* Email bounce handling
* SMS bounce handling

All notification related stuff lives in one module `fsa_notify`.

## Known bugs

* In very rare cases people may get two non-overlapping digests for same period of time. It may happen when the system is not able to send all messages out in one cron shot (very exceptional cases). And inbetween multiple attempts there is added new content which goes to the digest.

## Configuration

### UI

Configure > FSA configuration > Notify

That page contains two things:
* Edit current configuration settings (key and id-s)
* Option to turn off alerts collecting and sending.

### State

Basic configuration parameters are held in Drupal state variables and can be managed by UI (or `drush`).

* `fsa_notify.api` - Notify API key
* `fsa_notify.template_sms` - Notify API SMS Template ID
* `fsa_notify.template_email` - Notify API Email Template ID

Runtime state variables are:

* `fsa_notify.last_daily` - timestamp of last time when daily was finished last time.
* `fsa_notify.last_weekly` - timestamp of last time when weekly was finished last time.

## API keys and Template IDs

Those can be aquired from https://www.notifications.service.gov.uk/

Consult LastPass, Luke or Sally for access.

1) One set is API keys, which specify mode you want to use the service:
* Live – sends to anyone.
* Team and whitelist – limits who you can send to
* Test – pretends to send messages

2) Other set is Template IDs which specify different kind of templates to use.

## Fields used

### user.field_notification_method

User can choose between different kind of methods for getting updates from the site.
Also, can turn it off here.

### user.field_notification_sms

If user chooses SMS method, where to sent those?
This field is enforced then SMS method is chosen.
This field has additional check by `Field validation` module for data integrity:
* prevent spaces
* require the number begin with plus sign followed by numbers

### user.field_notification_cache

This field is not visible for user, but here we collect alerts per user to be sent.

When alerts are sent out, this field is emptied.

### user.field_notification_allergys

List of allergens user has signed up to.

## What happens when new alert is created?

* First it is queued for processing.
* Then during cron run the alert will be cached to every user who has signed up for particular allergen(s).
* During send-out event all cached notifications are sent out and user cache emptied.

## Cron

During cron run following things happen:
* Queue is processed for each queued alert and the alerts will be reference from user caches according to user allergen signup field.
* All SMS messages are sent out
* All immediate messages are sent out
* All daily messages are sent out
* All weekly messages are sent out.

## Queue

New alerts are queued. Drupal Queue is used. Because processing of them takes considerable time. Might be 1-2minutes per queue item.

Queue processing distributes alert references to users who have signed up for them.

## Sending out SMS and Email

It means looping through all users who have something to send out.
Message body is constructed and then sent.

## Timing

Since sending out messages are quite slow process in order to track performance and have some stats there is timer functionality included which logs some stats only if something is sent out.

```
Timer: type weekly; elapsed 164.101; 968 items; 5.899 items/sec.
Timer: type daily; elapsed 168.791; 1020 items; 6.043 items/sec.
Timer: type immediate; elapsed 1.250; 2 items; 1.600 items/sec.
Timer: type sms; elapsed 17.924; 989 items; 55.177 items/sec.
Timer: type queue; elapsed 73.326; 1 items; 0.014 items/sec.
```

## Digest times

In `fsa_notify.module` there is functionality which decides if it is time to send out daily or weekly.
Here follows ho it is decided.
There are 2 major factors:
* There has to be passed enough time since last time (a bit less than a day or a bit less than a week).
* It has to be certain time period of day or week when notification delivery may happen.

There is time window (few hours) during which the sending may happen. It is few hours because if sending fails for whatever reason it will be attempted again.

For details, please refer to following functions:
* `fsa_notify_daily_is_ready_to_send()` - check if we can send out daily digests now
* `fsa_notify_weekly_is_ready_to_send()` - check if we can send out weekly digests now
* `fsa_notify_is_ready_to_send()` - underlying general functionality for previous functions

When digest is sent (successfully finished), then is recorded by `fsa_notify_sent()`. It is needed to calclulate if enough time is passed since last time.

## If you need to change how a notification looks

* Check the template in Notify API in web
* `src/FsaNotifyStorage.php`:
  * `themeXXX()` functions
  * `$assembly_map`
* `src/FsaNotifyAPI.php`
  * email login link
  * email unsubscribe link
* `fsa_notify.module`:
  * template placeholder replacements

## Class FsaNotifyStorage

This class takes care of following:
* storing/distributing alert items to users based on their preferences
* retrieving messages for chunk of users with particular sending method
* theming/assembling messages according to sending method
* clearing user cache

Chunking is used to prevent Drupal cache saturation subsequent OOM event.

Everything here revolves around field `user.field_notification_cache`.

## Class FsaNotifyAPI

This class takes care of following:
* Connects to Notify API
* Sending out an email
* Sending out a SMS
* Error-handling and logging

## Files in this module

* `fsa_notify.module`
  * Phone number enforcement in user profile
  * Highlevel execution of Queue processing
  * Cron hook to initiate all functionality in this module - alert distribution and sending
  * Functionality to decide when to send daily or weekly digests
  * Highlevel sending of all types of notifications
* `src/FsaNotifyAPI.php`
  * Connect to Notify API
  * General Email sending
  * General SMS sending
* `src/FsaNotifyStorage.php`
  * Notification storing
  * User cache clearing of notifications
  * Notification retrieval in chunks per type in themed form
  * Theming functions
  * "Short" link generation for messages
* `src/Form/FsaSettings.php`
  * Edit key and id-s
  * Enable/Disable distributing and sending notifications
* `src/Plugin/QueueWorker/FsaNotifyStorageQueue.php`
  * Item processing (storing / distributing)

## Related modules and packages

* alphagov/notifications-php-client
* drupal/field_validation
* php-http/guzzle6-adapter

## Related URL-s

* https://www.notifications.service.gov.uk
* https://github.com/alphagov/notifications-php-client
