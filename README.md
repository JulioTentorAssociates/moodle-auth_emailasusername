# moodle-auth_emailasusername

Moodle authentication plugin providing email-based self-registration with the email
address as the username.

## Origin and attribution

This repository is a fork of
[davidpesce/moodle-auth_emailasusername](https://github.com/davidpesce/moodle-auth_emailasusername),
originally written and maintained by **David Pesce** ([exputo.com](http://exputo.com))
and published under GPL v3 or later since 2016. The plugin, its design and the
component name are his work; this fork exists to carry it forward to current Moodle
releases.

Every file derived from the original keeps David Pesce's copyright notice. Files
added by this fork carry their own. Modified files carry both.

The component name `auth_emailasusername` is deliberately unchanged. Moodle stores
`emailasusername` in the `auth` field of every account the plugin creates, and it
will not allow an authentication plugin to be uninstalled while any user still has
that auth type. Renaming the component would leave existing installations unable to
upgrade in place, so the name is kept for the benefit of sites already running the
original plugin — not as a claim over it.

## Status

**Under development. Not ready for installation.**

This fork targets Moodle 5.2.2 and later. Support for earlier releases is out of
scope and will be removed rather than maintained: sites on Moodle 4.x should
continue to use the upstream plugin.

The tree currently in this branch has not yet been adapted — it is the upstream
2.0.0 code, which declares Moodle 4.4 as its minimum. Do not install it against
5.2.2 expecting it to work.

## Requirements

- Moodle 5.2.2 or later
- PHP as required by that Moodle release

## Installation

Not documented yet; this section will be written when the plugin is releasable.

The plugin directory is `auth/emailasusername`. In addition to enabling the plugin
under *Site administration → Plugins → Authentication → Manage authentication*, the
site's self-registration method must be set to this plugin for it to take effect.

## Licence

GNU GPL v3 or later. See [LICENSE](LICENSE).

Copyright (C) 2016 onwards David Pesce (http://exputo.com)
Copyright (C) 2026 onwards Julio Tentor & Associates

## Contributing

Issues and pull requests are welcome here. Changes that are not tied to a specific
Moodle release are also offered upstream, where they benefit sites this fork no
longer supports.
