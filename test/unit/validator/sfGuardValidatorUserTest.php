<?php

/**
 * sfGuardValidatorUser tests.
 */

require_once dirname(__DIR__, 2) . '/bootstrap/unit.php';

$t = new lime_test(10);

class MockUser
{
  public
    $active   = true,
    $username = 'mock',
    $emailAddress = 'mock@example.com',
    $password = 'correct';

  public function getIsActive()
  {
    return $this->active;
  }

  public function checkPassword($password)
  {
    return $password == $this->password;
  }
}

class MockTable
{
  static public $user = null;

  public function retrieveByUsername($username)
  {
    return self::$user->username == $username ? self::$user : null;
  }

  public function retrieveByUsernameOrEmailAddress($username)
  {
    return $this->retrieveByUsername($username) || self::$user->emailAddress == $username ? self::$user : null;
  }
}

class TestValidator extends sfGuardValidatorUser
{
  protected function getTable()
  {
    return new MockTable();
  }
}

// ->clean()
$t->diag('->clean()');

$validator = new TestValidator();

$activeUser = new MockUser();
$activeUser->active = true;

$inactiveUser = new MockUser();
$inactiveUser->active = false;

MockTable::$user = $activeUser;

try
{
  $values = $validator->clean(array('username' => 'mock', 'password' => 'correct'));

  $t->pass('->clean() does not throw an error if an active user is found');
  $t->isa_ok($values['user'], 'MockUser', '->clean() adds the user object to the cleaned values');
}
catch (sfValidatorErrorSchema $error)
{
  $t->fail('->clean() does not throw an error if an active user is found');
  $t->skip();
}

try
{
  $values = $validator->clean(array('username' => 'mock@example.com', 'password' => 'correct'));

  $t->pass('->clean() does not throw an error if an active user is found');
  $t->isa_ok($values['user'], 'MockUser', '->clean() adds the user object to the cleaned values');
}
catch (sfValidatorErrorSchema $error)
{
  $t->fail('->clean() does not throw an error if an active user is found');
  $t->skip();
}

try
{
  $validator->clean(array('username' => 'mock', 'password' => 'incorrect'));
  $t->fail('->clean() throws an error if password is incorrect');
}
catch (sfValidatorErrorSchema $error)
{
  $t->pass('->clean() throws an error if password is incorrect');
}

try
{
  $validator->clean(array('username' => null, 'password' => null));

  $t->fail('->clean() throws an error if no username is provided');
  $t->skip('', 2);
}
catch (sfValidatorErrorSchema $error)
{
  $t->pass('->clean() throws an error if no username is provided');

  $t->ok(isset($error['username']), '->clean() throws a "username" error if no username is provided');
  $t->is($error['username']->getCode(), 0, '->clean() throws a "0" error code if no username is provided');
  $t->is($error['username']->getCodeString(), 'invalid', '->clean() throws an "invalid" error string if no username is provided');
}

$validator->setOption('throw_global_error', true);

try
{
  $validator->clean(array('username' => null, 'password' => null));
  $t->fail('->clean() throws a global error if the "throw_global_error" option is true');
}
catch (sfValidatorErrorSchema $error)
{
  $t->fail('->clean() throws a global error if the "throw_global_error" option is true');
}
catch (sfValidatorError $error)
{
  $t->pass('->clean() throws a global error if the "throw_global_error" option is true');
}


