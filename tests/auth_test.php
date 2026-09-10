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
 * Tests for the emailasusername authentication plugin.
 *
 * @package    auth_emailasusername
 * @copyright  2026 Julio Tentor & Associates <https://juliotentor.com>
 * @author     Julio Tentor <jtentor@juliotentor.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_emailasusername;

use PHPUnit\Framework\Attributes\CoversClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/auth/emailasusername/auth.php');

/**
 * Tests for auth_plugin_emailasusername.
 *
 * @copyright  2026 Julio Tentor & Associates <https://juliotentor.com>
 * @author     Julio Tentor <jtentor@juliotentor.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\auth_plugin_emailasusername::class)]
final class auth_test extends \advanced_testcase {
    /** @var \auth_plugin_emailasusername The plugin under test. */
    protected $auth;

    /**
     * Load the plugin before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->auth = get_auth_plugin('emailasusername');
    }

    /**
     * Create an unconfirmed account belonging to this plugin.
     *
     * @param string $address The address, used as both username and email.
     * @return \stdClass The user record.
     */
    protected function create_unconfirmed_user(string $address): \stdClass {
        return $this->getDataGenerator()->create_user([
            'username' => $address,
            'email' => $address,
            'auth' => 'emailasusername',
            'confirmed' => 0,
            'secret' => 'abc123',
        ]);
    }

    /**
     * The plugin offers self-registration and keeps passwords in Moodle.
     */
    public function test_plugin_capabilities(): void {
        $this->assertTrue($this->auth->can_signup());
        $this->assertFalse($this->auth->prevent_local_passwords());
        $this->assertTrue($this->auth->is_internal());
    }

    /**
     * A correct secret confirms the account.
     */
    public function test_user_confirm_with_correct_secret(): void {
        global $DB;

        $user = $this->create_unconfirmed_user('correct@example.com');

        $this->assertSame(AUTH_CONFIRM_OK, $this->auth->user_confirm($user->username, 'abc123'));
        $this->assertEquals(1, $DB->get_field('user', 'confirmed', ['id' => $user->id]));
    }

    /**
     * A wrong secret is rejected, and the account stays unconfirmed.
     *
     * This is the regression test for the defect where this path returned null,
     * which matches none of the AUTH_CONFIRM_* constants the callers compare
     * against.
     */
    public function test_user_confirm_with_wrong_secret(): void {
        global $DB;

        $user = $this->create_unconfirmed_user('wrong@example.com');

        $this->assertSame(AUTH_CONFIRM_ERROR, $this->auth->user_confirm($user->username, 'tampered'));
        $this->assertEquals(0, $DB->get_field('user', 'confirmed', ['id' => $user->id]));
    }

    /**
     * An account that is already confirmed reports that, not success.
     */
    public function test_user_confirm_when_already_confirmed(): void {
        $user = $this->getDataGenerator()->create_user([
            'username' => 'already@example.com',
            'email' => 'already@example.com',
            'auth' => 'emailasusername',
            'confirmed' => 1,
            'secret' => 'abc123',
        ]);

        $this->assertSame(AUTH_CONFIRM_ALREADY, $this->auth->user_confirm($user->username, 'abc123'));
    }

    /**
     * An address with no account is an error, not a silent success.
     */
    public function test_user_confirm_with_unknown_user(): void {
        $this->assertSame(AUTH_CONFIRM_ERROR, $this->auth->user_confirm('nobody@example.com', 'abc123'));
    }

    /**
     * An account belonging to another authentication method is refused.
     */
    public function test_user_confirm_rejects_another_auth_method(): void {
        $user = $this->getDataGenerator()->create_user([
            'username' => 'manual@example.com',
            'email' => 'manual@example.com',
            'auth' => 'manual',
            'confirmed' => 0,
            'secret' => 'abc123',
        ]);

        $this->assertSame(AUTH_CONFIRM_ERROR, $this->auth->user_confirm($user->username, 'abc123'));
    }

    /**
     * Confirmation restores the page the user was heading for, then clears it.
     */
    public function test_user_confirm_restores_wantsurl(): void {
        global $SESSION;

        $user = $this->create_unconfirmed_user('wantsurl@example.com');
        set_user_preference(
            \auth_plugin_emailasusername::WANTSURL_PREFERENCE,
            'https://example.com/course/view.php?id=2',
            $user
        );

        $this->assertSame(AUTH_CONFIRM_OK, $this->auth->user_confirm($user->username, 'abc123'));
        $this->assertSame('https://example.com/course/view.php?id=2', $SESSION->wantsurl);
        $this->assertFalse(
            get_user_preferences(\auth_plugin_emailasusername::WANTSURL_PREFERENCE, false, $user->id)
        );
    }

    /**
     * A failed confirmation leaves the preference alone.
     */
    public function test_wantsurl_survives_a_failed_confirmation(): void {
        $user = $this->create_unconfirmed_user('keep@example.com');
        set_user_preference(
            \auth_plugin_emailasusername::WANTSURL_PREFERENCE,
            'https://example.com/course/view.php?id=3',
            $user
        );

        $this->assertSame(AUTH_CONFIRM_ERROR, $this->auth->user_confirm($user->username, 'tampered'));
        $this->assertSame(
            'https://example.com/course/view.php?id=3',
            get_user_preferences(\auth_plugin_emailasusername::WANTSURL_PREFERENCE, false, $user->id)
        );
    }

    /**
     * Login succeeds only with the right password.
     */
    public function test_user_login(): void {
        $this->getDataGenerator()->create_user([
            'username' => 'login@example.com',
            'email' => 'login@example.com',
            'auth' => 'emailasusername',
            'password' => 'Str0ng-p4ssword!',
        ]);

        $this->assertTrue($this->auth->user_login('login@example.com', 'Str0ng-p4ssword!'));
        $this->assertFalse($this->auth->user_login('login@example.com', 'wrong'));
        $this->assertFalse($this->auth->user_login('nobody@example.com', 'Str0ng-p4ssword!'));
    }
}
