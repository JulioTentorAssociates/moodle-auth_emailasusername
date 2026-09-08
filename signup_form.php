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
 * Authentication Plugin: Email As Username Authentication
 *
 * @copyright  2016 onwards David Pesce (http://exputo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package auth_emailasusername
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');

class login_signup_form extends moodleform implements renderable, templatable {
    public function definition() {
        global $USER, $CFG;

        $mform = $this->_form;

        // Username field overriden as the email address.
        $mform->addElement('text', 'username', get_string('auth_emailasusername_email', 'auth_emailasusername'), 'maxlength="100" size="35" autocapitalize="none"');
        $mform->setType('username', PARAM_NOTAGS);
        $mform->addRule('username', get_string('auth_emailasusername_emailmissing', 'auth_emailasusername'), 'required', null, 'client');

        // Email confirmation field.
        $mform->addElement('text', 'email', get_string('auth_emailasusername_emailconfirm', 'auth_emailasusername'), 'maxlength="100" size="35"');
        $mform->setType('email', core_user::get_property_type('email'));
        $mform->addRule('email', get_string('auth_emailasusername_emailmissing', 'auth_emailasusername'), 'required', null, 'client');
        $mform->setForceLtr('email');

        // Password policy info.
        if (!empty($CFG->passwordpolicy)){
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }

        // Password field.
        $mform->addElement('password', 'password', get_string('password'), [
            'maxlength' => MAX_PASSWORD_CHARACTERS,
            'size' => 12,
            'autocomplete' => 'new-password'
        ]);
        $mform->setType('password', core_user::get_property_type('password'));
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');
        $mform->addRule('password', get_string('maximumchars', '', MAX_PASSWORD_CHARACTERS),
            'maxlength', MAX_PASSWORD_CHARACTERS, 'client');


        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }

        profile_signup_fields($mform);

        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }

        // Hook for plugins to extend form definition.
        core_login_extend_signup_form($mform);

        // Add "Agree to sitepolicy" controls. By default it is a link to the policy text and a checkbox but
        // it can be implemented differently in custom sitepolicy handlers.
        $manager = new \core_privacy\local\sitepolicy\manager();
        $manager->signup_form($mform);

        // buttons
        $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('createaccount'));

    }

    public function definition_after_data() {
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }

    public function validation($data, $files) {
        global $CFG, $DB;

        $errors = parent::validation($data, $files);
        $authplugin = get_auth_plugin($CFG->registerauth);

        // Extend validation for any form extensions from plugins.
        $errors = array_merge($errors, core_login_validate_extend_signup_form($data));

        if (signup_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }

        // Check the allowed characters in the username (which is the email address)
        // before checking whether it is already taken: reporting "that address is
        // taken" for a value that could never have been stored is misleading.
        if ($data['username'] !== core_text::strtolower($data['username'])) {
            $errors['username'] = get_string('usernamelowercase');
        } else if ($data['username'] !== clean_param($data['username'], PARAM_USERNAME)) {
            $errors['username'] = get_string('invalidusername');
        } else if ($DB->record_exists('user', ['username' => $data['username'],
                'mnethostid' => $CFG->mnet_localhost_id])) {
            $errors['username'] = get_string('usernameexists');
        }

        // Check to see if the user already exists in external auth.
        if (!isset($errors['username']) && $authplugin->user_exists($data['username'])) {
            $errors['username'] = get_string('usernameexists');
        }

        // Validate the email field. The username is the email address, so the two
        // fields must agree; that is checked before the address is looked up, and
        // the whole chain is a single if/else so that a later test cannot overwrite
        // the error reported by an earlier one.
        if (!validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');
        } else if ($data['username'] !== $data['email']) {
            $errors['email'] = get_string('auth_emailasusername_emailmismatch', 'auth_emailasusername');
        } else if ($DB->record_exists('user', ['email' => $data['email'],
                'mnethostid' => $CFG->mnet_localhost_id])) {
            $forgotpassword = new moodle_url('/login/forgot_password.php');
            $errors['email'] = get_string('emailexists') . ' <a href="' . $forgotpassword->out() . '">' .
                get_string('newpassword') . '?</a>';
        }

        // Check if email is allowed.
        if (!isset($errors['email'])) {
            if ($err = email_is_not_allowed($data['email'])) {
                $errors['email'] = $err;
            }
        }

        $errmsg = '';
        if (!check_password_policy($data['password'], $errmsg)) {
            $errors['password'] = $errmsg;
        }

        // Validate customisable profile fields. (profile_validation expects an object as the parameter with userid set)
        $dataobject = (object)$data;
        $dataobject->id = 0;
        $errors += profile_validation($dataobject, $files);

        return $errors;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $context = [
            'formhtml' => $formhtml
        ];
        return $context;
    }

}