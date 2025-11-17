<?php

namespace App\Authorization;

class AuthorizationRole
{
  public const ADMIN = 'Admin';
  public const LIBRARIAN = 'Librarian';
  public const AUTHOR = 'Author';
  public const MEMBER = 'Member';


  public static function defaultRoles(): array
  {
    return [
      self::ADMIN,
      self::LIBRARIAN,
      self::AUTHOR,
      self::MEMBER,
    ];
  }

  /**
   * This method build for multi-tenant
   * @param string $roleName
   * @return bool
   */
  public static function isDefault(string $roleName): bool
  {
    return in_array($roleName, self::defaultRoles());
  }
}
