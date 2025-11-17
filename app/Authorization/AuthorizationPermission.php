<?php

namespace App\Authorization;

class AuthorizationPermission
{
  private static array $all = [];

  public static function all(): array
  {
    if (!empty(self::$all)) {
      return self::$all;
    }

    self::$all = [
      // === ADMIN ONLY PERMISSIONS (isRoot = true) ===

      // User Management
      new PermissionDefinition('View Users', AuthorizationAction::VIEW, AuthorizationResource::USERS, true),
      new PermissionDefinition('Search Users', AuthorizationAction::SEARCH, AuthorizationResource::USERS, true),
      new PermissionDefinition('Create Users', AuthorizationAction::CREATE, AuthorizationResource::USERS, true),
      new PermissionDefinition('Update Users', AuthorizationAction::UPDATE, AuthorizationResource::USERS, true),
      new PermissionDefinition('Delete Users', AuthorizationAction::DELETE, AuthorizationResource::USERS, true),
      new PermissionDefinition('Export Users', AuthorizationAction::EXPORT, AuthorizationResource::USERS, true),

      // Role Management
      new PermissionDefinition('View UserRoles', AuthorizationAction::VIEW, AuthorizationResource::USER_ROLES, true),
      new PermissionDefinition('Update UserRoles', AuthorizationAction::UPDATE, AuthorizationResource::USER_ROLES, true),
      new PermissionDefinition('View Roles', AuthorizationAction::VIEW, AuthorizationResource::ROLES, true),
      new PermissionDefinition('Create Roles', AuthorizationAction::CREATE, AuthorizationResource::ROLES, true),
      new PermissionDefinition('Update Roles', AuthorizationAction::UPDATE, AuthorizationResource::ROLES, true),
      new PermissionDefinition('Delete Roles', AuthorizationAction::DELETE, AuthorizationResource::ROLES, true),

      // === ADMIN & LIBRARIAN PERMISSIONS ===

      // Dashboard
      new PermissionDefinition('View Dashboard', AuthorizationAction::VIEW, AuthorizationResource::DASHBOARD, true, true),

      // Authors Management
      new PermissionDefinition('View Authors', AuthorizationAction::VIEW, AuthorizationResource::AUTHORS, true, true),
      new PermissionDefinition('Search Authors', AuthorizationAction::SEARCH, AuthorizationResource::AUTHORS, true, true),
      new PermissionDefinition('Create Authors', AuthorizationAction::CREATE, AuthorizationResource::AUTHORS, true, true),
      new PermissionDefinition('Update Authors', AuthorizationAction::UPDATE, AuthorizationResource::AUTHORS, true, true),
      new PermissionDefinition('Delete Authors', AuthorizationAction::DELETE, AuthorizationResource::AUTHORS, true, true),
      new PermissionDefinition('Export Authors', AuthorizationAction::EXPORT, AuthorizationResource::AUTHORS, true, true),

      // Books Management (Full)
      new PermissionDefinition('Create Books', AuthorizationAction::CREATE, AuthorizationResource::BOOKS, true, true),
      new PermissionDefinition('Update Books', AuthorizationAction::UPDATE, AuthorizationResource::BOOKS, true, true),
      new PermissionDefinition('Delete Books', AuthorizationAction::DELETE, AuthorizationResource::BOOKS, true, true),
      new PermissionDefinition('Export Books', AuthorizationAction::EXPORT, AuthorizationResource::BOOKS, true, true),
      new PermissionDefinition('Generate Books', AuthorizationAction::GENERATE, AuthorizationResource::BOOKS, true, true),

      // Members Management
      new PermissionDefinition('View Members', AuthorizationAction::VIEW, AuthorizationResource::MEMBERS, true, true),
      new PermissionDefinition('Search Members', AuthorizationAction::SEARCH, AuthorizationResource::MEMBERS, true, true),
      new PermissionDefinition('Create Members', AuthorizationAction::CREATE, AuthorizationResource::MEMBERS, true, true),
      new PermissionDefinition('Update Members', AuthorizationAction::UPDATE, AuthorizationResource::MEMBERS, true, true),
      new PermissionDefinition('Delete Members', AuthorizationAction::DELETE, AuthorizationResource::MEMBERS, true, true),
      new PermissionDefinition('Export Members', AuthorizationAction::EXPORT, AuthorizationResource::MEMBERS, true, true),

      // Borrowings Management
      new PermissionDefinition('View Borrowings', AuthorizationAction::VIEW, AuthorizationResource::BORROWINGS, true, true),
      new PermissionDefinition('Search Borrowings', AuthorizationAction::SEARCH, AuthorizationResource::BORROWINGS, true, true),
      new PermissionDefinition('Update Borrowings', AuthorizationAction::UPDATE, AuthorizationResource::BORROWINGS, true, true),
      new PermissionDefinition('Delete Borrowings', AuthorizationAction::DELETE, AuthorizationResource::BORROWINGS, true, true),
      new PermissionDefinition('Export Borrowings', AuthorizationAction::EXPORT, AuthorizationResource::BORROWINGS, true, true),
      new PermissionDefinition('Return Borrowings', AuthorizationAction::UPDATE, AuthorizationResource::BORROWINGS, true, true),

      // Categories Management
      new PermissionDefinition('Create Categories', AuthorizationAction::CREATE, AuthorizationResource::CATEGORIES, true, true),
      new PermissionDefinition('Update Categories', AuthorizationAction::UPDATE, AuthorizationResource::CATEGORIES, true, true),
      new PermissionDefinition('Delete Categories', AuthorizationAction::DELETE, AuthorizationResource::CATEGORIES, true, true),

      // Publishers Management
      new PermissionDefinition('Create Publishers', AuthorizationAction::CREATE, AuthorizationResource::PUBLISHERS, true, true),
      new PermissionDefinition('Update Publishers', AuthorizationAction::UPDATE, AuthorizationResource::PUBLISHERS, true, true),
      new PermissionDefinition('Delete Publishers', AuthorizationAction::DELETE, AuthorizationResource::PUBLISHERS, true, true),

      // === AUTHOR PERMISSIONS ===

      // Books (Author can create and update their own books)
      new PermissionDefinition('Create Books', AuthorizationAction::CREATE, AuthorizationResource::BOOKS, false, false, false, true),
      new PermissionDefinition('Update Books', AuthorizationAction::UPDATE, AuthorizationResource::BOOKS, false, false, false, true),

      // === MEMBER PERMISSIONS ===

      // Books (View and Search)
      new PermissionDefinition('View Books', AuthorizationAction::VIEW, AuthorizationResource::BOOKS, false, false, true),
      new PermissionDefinition('Search Books', AuthorizationAction::SEARCH, AuthorizationResource::BOOKS, false, false, true),

      // Borrowings (Member can borrow and renew)
      new PermissionDefinition('Create Borrowings', AuthorizationAction::CREATE, AuthorizationResource::BORROWINGS, false, false, true),
      new PermissionDefinition('Renew Borrowings', AuthorizationAction::UPDATE, AuthorizationResource::BORROWINGS, false, false, true),

      // Categories (View and Search)
      new PermissionDefinition('View Categories', AuthorizationAction::VIEW, AuthorizationResource::CATEGORIES, false, false, true),
      new PermissionDefinition('Search Categories', AuthorizationAction::SEARCH, AuthorizationResource::CATEGORIES, false, false, true),

      // Publishers (View and Search)
      new PermissionDefinition('View Publishers', AuthorizationAction::VIEW, AuthorizationResource::PUBLISHERS, false, false, true),
      new PermissionDefinition('Search Publishers', AuthorizationAction::SEARCH, AuthorizationResource::PUBLISHERS, false, false, true),

      // === ALL ROLES (including Authors & Members) ===

      // Books (View and Search for all)
      new PermissionDefinition('View Books', AuthorizationAction::VIEW, AuthorizationResource::BOOKS, false, false, true, true),
      new PermissionDefinition('Search Books', AuthorizationAction::SEARCH, AuthorizationResource::BOOKS, false, false, true, true),

      // Borrowings (Borrow and Renew for all non-admin roles)
      new PermissionDefinition('Create Borrowings', AuthorizationAction::CREATE, AuthorizationResource::BORROWINGS, false, true, true, true),
      new PermissionDefinition('Renew Borrowings', AuthorizationAction::UPDATE, AuthorizationResource::BORROWINGS, false, true, true, true),

      // Categories (View and Search for all)
      new PermissionDefinition('View Categories', AuthorizationAction::VIEW, AuthorizationResource::CATEGORIES, false, false, true, true),
      new PermissionDefinition('Search Categories', AuthorizationAction::SEARCH, AuthorizationResource::CATEGORIES, false, false, true, true),

      // Publishers (View and Search for all)
      new PermissionDefinition('View Publishers', AuthorizationAction::VIEW, AuthorizationResource::PUBLISHERS, false, false, true, true),
      new PermissionDefinition('Search Publishers', AuthorizationAction::SEARCH, AuthorizationResource::PUBLISHERS, false, false, true, true),
    ];

    return self::$all;
  }

  public static function admin(): array
  {
    return array_filter(self::all(), fn($p) => $p->isRoot);
  }

  public static function librarian(): array
  {
    return array_filter(self::all(), fn($p) => $p->isLibrarian);
  }

  public static function author(): array
  {
    return array_filter(self::all(), fn($p) => $p->isAuthor);
  }

  public static function member(): array
  {
    return array_filter(self::all(), fn($p) => $p->isMember);
  }

  public static function nameFor(string $action, string $resource): string
  {
    return "Permissions.{$resource}.{$action}";
  }
}
