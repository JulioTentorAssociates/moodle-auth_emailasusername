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

**Under development. Not ready for installation.**

Version 2.1.0 targets Moodle 5.2.2 and later. Support for earlier releases is not
maintained: a site on Moodle 4.x should stay on version 2.0.0 until it has upgraded
Moodle itself.

Several defects are fixed and the privacy provider rewritten — see
[CHANGELOG.md](CHANGELOG.md) — but the plugin has not yet been installed or run on a
Moodle site, has no automated tests, and `version.php` still declares the earlier
minimum. Do not deploy it.

## Requirements

- Moodle 5.2.2 or later
- PHP as required by that Moodle release

## Installation

Not documented yet; this section will be written when the plugin is releasable.
Upgrading from version 2.0.0 needs no database change and no manual step.

The plugin directory is `auth/emailasusername`. In addition to enabling the plugin
under *Site administration → Plugins → Authentication → Manage authentication*, the
site's self-registration method must be set to this plugin for it to take effect.

## Licence

GNU GPL v3 or later. See [LICENSE](LICENSE).

Copyright (C) 2016 onwards David Pesce (http://exputo.com)
Copyright (C) 2026 onwards Julio Tentor & Associates

## Contributing

Issues and pull requests are welcome here.
