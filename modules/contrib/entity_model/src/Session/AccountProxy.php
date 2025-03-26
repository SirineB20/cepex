<?php

namespace Drupal\entity_model\Session;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy as DrupalAccountProxy;
use Drupal\user\UserInterface;

/**
 * An alternative implementation of AccountProxyInterface.
 *
 * This makes the getAccount method return the actual User entity
 * instead of an instance of Drupal\Core\Session\UserSession.
 */
class AccountProxy extends DrupalAccountProxy {

  /**
   * The full user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $fullUser;

  /**
   * {@inheritdoc}
   */
  public function setAccount(AccountInterface $account) {
    if ($account instanceof DrupalAccountProxy) {
      $account = $this->getCachedAccount();
    }

    $this->account = $account;
    $this->id = $account->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getAccount() {
    if (!isset($this->fullUser)) {
      $user = $this->loadUserEntity($this->id);
      $this->fullUser = $user;
      $this->setAccount($user);
      $this->account = $this->fullUser;
    }

    return $this->account;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoles($exclude_locked_roles = FALSE) {
    return $this->getCachedAccount()->getRoles($exclude_locked_roles);
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermission($permission) {
    return $this->getCachedAccount()->hasPermission($permission);
  }

  /**
   * {@inheritdoc}
   */
  public function isAuthenticated() {
    return $this->getCachedAccount()->isAuthenticated();
  }

  /**
   * {@inheritdoc}
   */
  public function isAnonymous() {
    return $this->getCachedAccount()->isAnonymous();
  }

  /**
   * {@inheritdoc}
   */
  public function getPreferredLangcode($fallback_to_default = TRUE) {
    return $this->getCachedAccount()->getPreferredLangcode($fallback_to_default);
  }

  /**
   * {@inheritdoc}
   */
  public function getPreferredAdminLangcode($fallback_to_default = TRUE) {
    return $this->getCachedAccount()->getPreferredAdminLangcode($fallback_to_default);
  }

  /**
   * {@inheritdoc}
   */
  public function getAccountName() {
    return $this->getCachedAccount()->getAccountName();
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayName() {
    return $this->getCachedAccount()->getDisplayName();
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail() {
    return $this->getCachedAccount()->getEmail();
  }

  /**
   * {@inheritdoc}
   */
  public function getTimeZone() {
    return $this->getCachedAccount()->getTimeZone();
  }

  /**
   * {@inheritdoc}
   */
  public function getLastAccessedTime() {
    return $this->getCachedAccount()->getLastAccessedTime();
  }

  /**
   * Get the cached account.
   */
  protected function getCachedAccount() {
    return parent::getAccount();
  }

}
