Deployment
========================

Respect [Wunderflow](https://wunderflow.wunder.io)

#### Production deployment

* See `git tag` to figure out next tag number
* Compose release notes (from git commits)
  * One good tool for that is [git-release-notes](https://www.npmjs.com/package/git-release-notes) npm package
* `drush @fsa.prod ssh` and create a backup dump for last tag `drush cr; drush sql-dump > ~/v0.019.sql`
  * Only few last dumps are worth storing, feel free to delete oldies from `/home/www-admin`
* Use [Deploybot](https://wunder.deploybot.com/111465/environments/120921) to deploy
  * Paste the release notes along with version number (git tag) to deployment note
* Once all good with deployment post notification to the [FSA/Wunder Slack CMS channel](https://wunder-fsa.slack.com/messages/C862GVAF8) along with the release notes.

#### Dev/staging deployment

* Staging: Push to `master` branch and [Deploybot](https://wunder.deploybot.com/111465/environments/121580) deploys automatically
* Dev: Push to `develop` branch and [Deploybot](https://wunder.deploybot.com/111465/environments/121583) deploys automatically

