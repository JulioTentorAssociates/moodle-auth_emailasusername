<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Privacy provider for auth_emailasusername.
 *
 * @package    auth_emailasusername
 * @copyright  2024 David Pesce (http://exputo.com)
 * @copyright  2026 Julio Tentor & Associates <https://juliotentor.com>
 * @author     Julio Tentor <jtentor@juliotentor.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_emailasusername\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;

/**
 * Privacy provider for the emailasusername authentication plugin.
 *
 * The only personal data this plugin stores of its own is a single user
 * preference, written at signup and removed at confirmation, so the provider
 * follows the shape core uses for the same case (see core_editor).
 *
 * @copyright  2024 David Pesce (http://exputo.com)
 * @copyright  2026 Julio Tentor & Associates <https://juliotentor.com>
 * @author     Julio Tentor <jtentor@juliotentor.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This plugin stores one user preference and no other personal data.
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\user_preference_provider {
    /**
     * Name of the preference this plugin stores.
     *
     * Repeated from auth_plugin_emailasusername::WANTSURL_PREFERENCE rather than
     * referenced: auth.php is not autoloaded, and this class must not depend on it
     * having been included.
     */
    public const WANTSURL_PREFERENCE = 'auth_emailasusername_wantsurl';

    /**
     * Describe the personal data this plugin stores.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference(
            self::WANTSURL_PREFERENCE,
            'privacy:metadata:preference:wantsurl'
        );

        return $collection;
    }

    /**
     * Export the user preference stored by this plugin.
     *
     * @param int $userid The user whose preferences are being exported.
     */
    public static function export_user_preferences(int $userid) {
        $wantsurl = get_user_preferences(self::WANTSURL_PREFERENCE, null, $userid);

        if ($wantsurl !== null) {
            writer::export_user_preference(
                'auth_emailasusername',
                self::WANTSURL_PREFERENCE,
                $wantsurl,
                get_string('privacy:preference:wantsurl', 'auth_emailasusername')
            );
        }
    }
}
