Deployment
========================

#### Scheduling a production deployment

Before deployment it is worth checking from client that no alerts are scheduled to publish and the weekly or daily alert Notification digest is not about to be triggered during the deployment.

The digest "last sent" timestamps can be requested with 
```
drush @fsa.prod sget fsa_notify.last_daily
drush @fsa.prod sget fsa_notify.last_weekly
```
#### Production deployment

1. See `git tag` to figure out the next tag number
2. Push `master` to `production` as described in [Wunderflow examples](http://wunderflow.wunder.io/#examples)
3. Compose release notes from git commits. This is good also to understand what goes out with the deployment
    * A good tool for release notes generation is [git-release-notes](https://www.npmjs.com/package/git-release-notes) npm package
        * Run as `git-release-notes production...master markdown > [TAGNAME].md`. Some moderation is required unless all git commit messages make perfect sense.
4. `drush @fsa.prod ssh` and create a backup dump for the last (currently active) tag `drush cr; drush sql-dump > ~/[TAGNAME].sql`
    * Only few last dumps are worth storing, feel free to delete oldies from `/home/www-admin`
5. Use [Deploybot](https://wunder.deploybot.com/111465/environments/120921) to deploy
    * Paste the release notes along with version number (git tag) to deployment note
6. Once all good with deployment post notification about the new version to the [FSA/Wunder Slack CMS channel](https://wunder-fsa.slack.com/messages/C862GVAF8) along with the release notes.

#### Dev/staging deployments

* Staging: Push to `master` branch and [Deploybot](https://wunder.deploybot.com/111465/environments/121580) deploys automatically
* Dev: Push to `develop` branch and [Deploybot](https://wunder.deploybot.com/111465/environments/121583) deploys automatically

