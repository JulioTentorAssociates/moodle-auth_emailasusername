@auth @auth_emailasusername
Feature: Self-registration with the email address as the username
  In order to create an account without inventing a username
  As a visitor
  I need the signup form to accept my address and to explain clearly when it cannot

  Background:
    Given the following config values are set as admin:
      | auth           | emailasusername |
      | registerauth   | emailasusername |
      | passwordpolicy | 0               |
    And the following "users" exist:
      | username            | email               | firstname | lastname |
      | taken@example.com   | taken@example.com   | Already   | There    |

  @javascript
  Scenario: An address that is already registered offers password recovery
    Given I am on site homepage
    And I follow "Log in"
    When I press "Create new account"
    And I set the following fields to these values:
      | Email address         | taken@example.com |
      | Email address (again) | taken@example.com |
      | Password              | Str0ng-p4ssword!  |
      | First name            | Someone           |
      | Last name             | Else              |
    And I press "Create my new account"
    Then I should see "This email address is already registered"
    And I should not see "This username already exists"

  @javascript
  Scenario: The two address fields must agree
    Given I am on site homepage
    And I follow "Log in"
    When I press "Create new account"
    And I set the following fields to these values:
      | Email address         | first@example.com |
      | Email address (again) | other@example.com |
      | Password              | Str0ng-p4ssword!  |
      | First name            | Someone           |
      | Last name             | Else              |
    And I press "Create my new account"
    Then I should see "Email addresses do not match"

  @javascript
  Scenario: An address containing a plus sign is refused with an explanation
    Given I am on site homepage
    And I follow "Log in"
    When I press "Create new account"
    And I set the following fields to these values:
      | Email address         | someone+moodle@example.com |
      | Email address (again) | someone+moodle@example.com |
      | Password              | Str0ng-p4ssword!           |
      | First name            | Someone                    |
      | Last name             | Else                       |
    And I press "Create my new account"
    Then I should see "This site does not allow accounts to be created with an address containing that character"

  @javascript
  Scenario: An address typed with capitals is accepted and stored in lower case
    Given I am on site homepage
    And I follow "Log in"
    When I press "Create new account"
    And I set the following fields to these values:
      | Email address         | Mixed.Case@Example.com |
      | Email address (again) | Mixed.Case@Example.com |
      | Password              | Str0ng-p4ssword!       |
      | First name            | Mixed                  |
      | Last name             | Case                   |
    And I press "Create my new account"
    Then I should see "An email should have been sent to your address at mixed.case@example.com"
