WD = $(shell pwd)
DUMP = $(WD)/dump.sql

default:
	@echo
	@echo "  make generate   - populate database with entities"
	@echo "  make dump       - do sql dump"
	@echo "  make terstore   - restore database from dump"
	@echo "  make reset      - prepare entities for testing"
	@echo "  make test_get   - test getAllDigestedMessages()"
	@echo "  make test_save  - test storeDelayedMessage()"
	@echo "  make test_clear - test clearMessages()"
	@echo "  make clean      - delete generated entities"
	@echo

# drush devel package is buggy, need to patch manually few things
# cannot generate 100 content items, need to break down
generate:
	@echo "Generating terms... few secs..."
	drush generate-terms alerts_allergen 100
	@echo "Generating alerts... few secs..."
	drush generate-content 50 --types=alert --feedback --languages=cy
	drush generate-content 50 --types=alert --feedback --languages=en
	@echo "Generating users... 3-4 hours..."
	drush generate-users 100000

dump:
	drush -v sql-dump --skip-tables-key=common --result-file="$(DUMP)"
	ls -lah "$(DUMP)"

restore:
	drush sql-cli < "$(DUMP)"

reset:
	drush scr $(WD)/users_reset.php

test_get:
	drush eval 'var_dump( getAllDigestedMessages("weekly") );'

test_save:
	drush eval 'storeDelayedMessage(Drupal\node\Entity\Node::load(398));'

test_clear:
	drush eval 'clearMessages(Drupal\user\Entity\User::load(123217));'

clean:
	drush scr $(WD)/users_del.php
	# need to del terms
	# need to del alerts
