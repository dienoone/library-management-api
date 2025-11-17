<?php

namespace App\Authorization;

class AuthorizationResource
{
  public const DASHBOARD = 'Dashboard';
  public const USERS = 'Users';
  public const USER_ROLES = 'UserRoles';
  public const ROLES = 'Roles';
  public const PERMISSIONS = 'Permissions';

  // Library Resources
  public const AUTHORS = 'Authors';
  public const BOOKS = 'Books';
  public const MEMBERS = 'Members';
  public const BORROWINGS = 'Borrowings';
  public const CATEGORIES = 'Categories';
  public const PUBLISHERS = 'Publishers';


  public static function all(): array
  {
    return [
      self::DASHBOARD,
      self::USERS,
      self::USER_ROLES,
      self::ROLES,
      self::AUTHORS,
      self::BOOKS,
      self::MEMBERS,
      self::BORROWINGS,
      self::CATEGORIES,
      self::PUBLISHERS,
    ];
  }
}
