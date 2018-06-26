# FSA Content reminders

FSA Content reminder is a module that sends FSA content team notifications about
content that may require attention to revise, edit or archive old content.

Module related functionality also add a views-listing that displays all nodes 
that have the "Content reminder" field value past current day. 

## TODO

* In order to ensure the $base_url for emails `fsa_content_reminder_cron()`
duplicates a switch with `getenv("WKV_SITE_ENV")`. Consider movig that logic 
into a service to avoid code duplication. 

## Configuration

### UI

*Configure* > *FSA configuration* > *Content reminders*

* Administration form to store the email address that should be used as recipient 
for the reminder emails.
* Email dispatching can be disabled by removing the email from configurations.
* The configuration is ignored and allowed to be changed in remote environments.

### States

* `fsa_content_reminder.next_dispatch` - a timestamp to trigger the email 
sending once a day.


## Listing (views) 

*Content* > *Content reminders*

Lists all nodes that have the content reminder date past current time.
