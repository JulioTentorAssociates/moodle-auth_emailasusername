# Changelog

All notable changes to this plugin are recorded here. Versions follow the
`$plugin->release` value in `version.php`.

## [2.1.0]

First release from Julio Tentor & Associates. It continues
[David Pesce's plugin](https://github.com/davidpesce/moodle-auth_emailasusername)
under the same component name, `auth_emailasusername`, so that sites already
running it can upgrade in place. Version 2.0.0 and everything before it is his
work; this file records what changed after that point.

### Support

- **Moodle 5.2.2 is now the minimum.** Support for earlier releases is not
  maintained and will not be restored. A site on Moodle 4.x should stay on
  version 2.0.0 until it has upgraded Moodle itself; there is no path that
  keeps this plugin working across the gap.
- Upgrading from 2.0.0 requires no database change and no manual step. Accounts,
  their `auth` value and their profile data are untouched.

### Fixed

- `user_confirm()` returned nothing when the confirmation secret did not match an
  unconfirmed account. Callers compare that result against Moodle's
  `AUTH_CONFIRM_*` constants and null matches none of them, so a wrong or tampered
  confirmation link produced undefined handling instead of a rejection. It now
  returns `AUTH_CONFIRM_ERROR`.
- The "this address is already registered" message linked to
  `/login/forgot_password.php`, an address resolved against the web server root
  rather than the Moodle installation. On a site installed in a subdirectory the
  link was broken, on the one screen that tells a user they need it. The link is
  now built from the site's own URL.
- Signup validation reported an address that could never have been stored as
  "already registered" instead of as invalid. The allowed characters are now
  checked before the address is looked up.
- The duplicate address check matched accounts belonging to any MNet host rather
  than local accounts only. The username check beside it was already restricted;
  the two now agree.
- A submission whose address was both mistyped and already registered reported only
  the mismatch, because the "username must match email" test overwrote the error the
  previous check had set. The tests are now a single chain, ordered from the most
  fundamental failure to the most specific.

### Changed

- **The signup return URL is stored under this plugin's own preference key**,
  `auth_emailasusername_wantsurl`. It was previously written under
  `auth_email_wantsurl`, a key belonging to core's own `auth_email` component, so
  the plugin was writing into another component's namespace and depending on a core
  key that could be renamed without warning — a failure that would have been silent,
  dropping the user on the site home page with nothing in the logs.

  A user who signed up under 2.0.0 and confirms after upgrading will land on the
  site home page rather than the page they were originally trying to reach. Nothing
  else is affected: confirmation works, the account is created, no data is lost. A
  transitional read of the old key was written and then deliberately removed —
  carrying a branch and a deletion reminder was not worth a cosmetic redirect during
  a window of a few days.

- **The privacy provider is rewritten.** It previously declared two interfaces,
  returned an empty metadata collection — asserting that no personal data was
  stored, which was not true, since the signup return URL is a user preference — and
  carried three methods belonging to interfaces it did not implement, none of which
  were ever called. It now implements `\core_privacy\local\metadata\provider` and
  `\core_privacy\local\request\user_preference_provider`, declaring and exporting
  the one preference this plugin stores.

### Removed

- `upgrade.txt`, which documented a Moodle 3.3 change unreachable from this
  release. This file replaces it.

### Internal

- GPL licence boilerplate added to `signup_form.php`, `settings.php` and
  `db/upgrade.php`, which had none.
- Coding style pass across the tree: short array syntax, docblocks on the signup
  form and its methods, whitespace and final newlines.
