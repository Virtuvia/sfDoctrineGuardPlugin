<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGuardValidatorUser extends sfValidatorBase
{
  public function configure($options = array(), $messages = array())
  {
    $this->addOption('username_field', 'username');
    $this->addOption('password_field', 'password');
    $this->addOption('throw_global_error', false);

    $this->setMessage('invalid', 'The username and/or password is invalid.');
  }

  protected function doClean($value)
  {
    $username = $value[$this->getOption('username_field')] ?? '';
    $password = $value[$this->getOption('password_field')] ?? '';

    $allowEmail = sfConfig::get('app_sf_guard_plugin_allow_login_with_email', true);
    $method = $allowEmail ? 'retrieveByUsernameOrEmailAddress' : 'retrieveByUsername';

    // don't allow to sign in with an empty username
    if ($username)
    {
       if ($callable = sfConfig::get('app_sf_guard_plugin_retrieve_by_username_callable'))
       {
           $user = call_user_func_array($callable, array($username));
       } else {
           $user = $this->getTable()->$method($username);
       }
        // user exists?
       if($user)
       {
          // password is ok?
          if ($user->getIsActive() && $user->checkPassword($password))
          {
            return array_merge($value, array('user' => $user));
          }
       }
    }

    if ($this->getOption('throw_global_error'))
    {
      throw new sfValidatorError($this, 'invalid');
    }

    throw new sfValidatorErrorSchema($this, array($this->getOption('username_field') => new sfValidatorError($this, 'invalid')));
  }

  protected function getTable()
  {
    return Doctrine_Core::getTable('sfGuardUser');
  }
}
