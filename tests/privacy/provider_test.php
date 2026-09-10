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
 * Tests for the emailasusername privacy provider.
 *
 * @package    auth_emailasusername
 * @copyright  2026 Julio Tentor & Associates <https://juliotentor.com>
 * @author     Julio Tentor <jtentor@juliotentor.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_emailasusername\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;

/**
 * Tests for auth_emailasusername\privacy\provider.
 *
 * @copyright  2026 Julio Tentor & Associates <https://juliotentor.com>
 * @author     Julio Tentor <jtentor@juliotentor.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends \core_privacy\tests\provider_testcase {

    /**
     * The one preference this plugin stores is declared.
     */
    public function test_get_metadata(): void {
        $collection = new collection('auth_emailasusername');
        $items = provider::get_metadata($collection)->get_collection();

        $this->assertCount(1, $items);
        $this->assertSame(provider::WANTSURL_PREFERENCE, $items[0]->get_name());
    }

    /**
     * A stored preference is exported for its owner.
     */
    public function test_export_user_preferences(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user(['auth' => 'emailasusername']);
        set_user_preference(provider::WANTSURL_PREFERENCE, 'https://example.com/my/', $user);

        provider::export_user_preferences($user->id);

        $writer = writer::with_context(\context_system::instance());
        $this->assertTrue($writer->has_any_data());

        $exported = $writer->get_user_preferences('auth_emailasusername');
        $this->assertSame('https://example.com/my/', $exported->{provider::WANTSURL_PREFERENCE}->value);
    }

    /**
     * A user with no stored preference exports nothing at all.
     */
    public function test_export_user_preferences_when_none_stored(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user(['auth' => 'emailasusername']);

        provider::export_user_preferences($user->id);

        $this->assertFalse(writer::with_context(\context_system::instance())->has_any_data());
    }
}
