# moodle-auth_emailasusername

Moodle authentication plugin providing email-based self-registration with the email
address as the username.

## Origin and attribution

This plugin continues
[davidpesce/moodle-auth_emailasusername](https://github.com/davidpesce/moodle-auth_emailasusername),
originally written and maintained by **David Pesce** ([exputo.com](http://exputo.com))
and published under GPL v3 or later since 2016. Version 2.0.0 and everything before
it is his work: the plugin, its design and the component name. This repository
carries it forward to current Moodle releases.

Every file derived from the original keeps David Pesce's copyright notice. Files
added here carry their own. Modified files carry both. The repository's history
begins with his tree, unmodified, so the derivation is visible in `git log` and not
only in this file.

The component name `auth_emailasusername` is deliberately unchanged. Moodle stores
`emailasusername` in the `auth` field of every account the plugin creates, and it
will not allow an authentication plugin to be uninstalled while any user still has
that auth type. Renaming the component would leave existing installations unable to
upgrade in place, so the name is kept for the benefit of sites already running the
original plugin — not as a claim over it.

## Status

**Pre-release. Installed and exercised on Moodle 5.2.2, not yet used in
production.**

Version 2.1.0 targets Moodle 5.2.2 and later. Support for earlier releases is not
maintained: a site on Moodle 4.x should stay on version 2.0.0 until it has upgraded
Moodle itself.

Several defects are fixed and the privacy provider rewritten — see
[CHANGELOG.md](CHANGELOG.md). The signup and confirmation paths have been walked
through by hand on a test site and are covered by PHPUnit and Behat, which run on
every push against PostgreSQL and MariaDB. `$plugin->maturity` stays at
`MATURITY_ALPHA` until the plugin has run somewhere real.

## Requirements

- Moodle 5.2.2 or later
- PHP as required by that Moodle release

## Installation

Place the plugin so that it sits at `auth/emailasusername` inside the Moodle code
directory — on Moodle 5.x that is `public/auth/emailasusername`. The directory name
must be `emailasusername`, not the name of this repository, or Moodle will not
recognise the component.

Then visit the site as an administrator to complete the database upgrade, or run
`php admin/cli/upgrade.php` from the command line.

Two settings have to be changed before anything is visible to a visitor, both under
*Site administration → Plugins → Authentication → Manage authentication*:

1. Enable **Email-based self-registration with the username as the email address**.
2. Set **Self registration** to that same plugin. Enabling it is not enough; the
   site chooses one self-registration method and this setting is where it does so.

### Site settings worth knowing about

**Authentication instructions.** Found under *Site administration → Plugins →
Authentication → Manage authentication*, or by searching the admin interface for
`auth_instructions`. This field is shared by every authentication method on the
site, not owned by any plugin. Whether it is filled in changes how the login page
presents the link to create an account, and in the Boost theme the instructions
themselves are shown only on narrow viewports. An administrator who fills the field
and sees no change on a desktop browser is looking at theme behaviour, not at a
fault in this plugin.

**Extended username characters.** Found under *Site administration → Security →
Site security settings*. While it is off, which is the Moodle default, an address
containing a character Moodle does not accept in a username cannot be used to
create an account. The plus sign is the case that matters: `name+tag@example.com`
is common, and it is refused. The signup form says so in plain words and the plugin
settings page shows the same notice while the setting is off. Turning it on accepts
those addresses, and applies to every authentication method on the site.

### Upgrading from version 2.0.0

No database change and no manual step. Accounts, their `auth` value and their
profile data are untouched. One behaviour changes: a user who registered under
2.0.0 and confirms after the upgrade lands on the site home page rather than the
page they were originally trying to reach. See [CHANGELOG.md](CHANGELOG.md).

## Licence

GNU GPL v3 or later. See [LICENSE](LICENSE).

Copyright (C) 2016 onwards David Pesce (http://exputo.com)
Copyright (C) 2026 onwards Julio Tentor & Associates

## Contributing

Issues and pull requests are welcome here.
