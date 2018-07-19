Deployment
========================

#### Scheduling a production deployment

Before deploying to production check from client (FSA/Wunder or FSA/Epimorphics Slack channel) that no alerts are scheduled to be published soon and that the daily/weekly alert Notification digest is not about to be triggered since they could potentially fail if deployment hits at the same time.

The last sent digest timestamps are stored to Drupal states `fsa_notify.last_daily` and `fsa_notify.last_weekly` but are displayed on the [Notify settings page](https://www.food.gov.uk/admin/config/fsa/notify) for conveniency.

#### Production deployment

1. See `git tag` to figure out the next tag number
2. Merge `master` to `production` as described in [Wunderflow examples](http://wunderflow.wunder.io/#examples)
3. Compose release notes from git commits.
    * A good tool for release notes generation is [git-release-notes](https://www.npmjs.com/package/git-release-notes) npm package
        * Run as `git-release-notes production...master markdown > [TAGNAME].md`. Moderation is usually required unless all git commit messages make perfect sense.
4. `drush @fsa.prod ssh` and create a backup dump against last (currently active) tag `drush cr; drush sql-dump > ~/[CURRENT-TAGNAME].sql`
    * Old dumps can be deleted from `/home/www-admin`
5. Use [Deploybot](https://wunder.deploybot.com/111465/environments/120921) to deploy
    * Paste release notes along with version number (git tag) to deployment note
6. Once deployed succesfully post notification about the new release to the [FSA/Wunder Slack CMS channel](https://wunder-fsa.slack.com/messages/C862GVAF8) along with the release notes.

#### Dev/staging deployments

* Staging: Push to `master` branch and [Deploybot](https://wunder.deploybot.com/111465/environments/121580) deploys automatically
* Dev: Push to `develop` branch and [Deploybot](https://wunder.deploybot.com/111465/environments/121583) deploys automatically

