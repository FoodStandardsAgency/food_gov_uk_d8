all:
	@echo 'make test'
	@echo 'make alert_add'
	@echo 'make cron'

test:
	clear
	drush eval 'fsa_notify_cron();'

alert_add:
	drush scr $$(pwd)/alert_create.php $(TYPE)

cron:
	drush cron
