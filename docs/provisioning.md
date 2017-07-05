Provisioning Servers
====================

This project uses Upcloud server provisioning with ansible. The servers are deployend und Wunder Upcloud UK account. In order to use it you need to export username and password for ansible. This is easiest to do with `lpass` lastpass cli helper. You can install it from https://github.com/lastpass/lastpass-cli.

Setup credentials with lastpass cli
-----------------------------------
**bash/zsh shell**
```bash
# Remember to use your credentials
$ lpass login first.last@wunder.io
# Use lpass to export the USER and PASSWORD
$ export UPCLOUD_API_USER=$(lpass show "Shared-Care/Services/Upcloud UK" --username) UPCLOUD_API_PASSWD=$(lpass show "Shared-Care/Services/Upcloud UK" --password)
```

**fish shell**
```bash
# Remember to use your credentials
$ lpass login first.last@wunder.io
# Use lpass to export the USER and PASSWORD
$ set -x UPCLOUD_API_USER=$(lpass show "Shared-Care/Services/Upcloud UK" --username)
$ set -x UPCLOUD_API_PASSWD=$(lpass show "Shared-Care/Services/Upcloud UK" --password)
```

Deploy servers to Upcloud
-------------------------

Deployment uses `upcloud_server_spec_list` list from `conf/variables.yml` to deploy and update servers. You can update them to latest config with:
```
$ ./provision.sh -p ~/.ansible-pass-file upcloud
```
