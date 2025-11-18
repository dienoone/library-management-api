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
      new PermissionDefinition('View Users', AuthorizationAction::VIEW, AuthorizationResource::USERS, isLibrarian: false),
      new PermissionDefinition('Search Users', AuthorizationAction::SEARCH, AuthorizationResource::USERS, isLibrarian: false),
      new PermissionDefinition('Create Users', AuthorizationAction::CREATE, AuthorizationResource::USERS, isLibrarian: false),
      new PermissionDefinition('Update Users', AuthorizationAction::UPDATE, AuthorizationResource::USERS, isLibrarian: false),
      new PermissionDefinition('Delete Users', AuthorizationAction::DELETE, AuthorizationResource::USERS, isLibrarian: false),
      new PermissionDefinition('Export Users', AuthorizationAction::EXPORT, AuthorizationResource::USERS, isLibrarian: false),

      // Role Management
      new PermissionDefinition('View UserRoles', AuthorizationAction::VIEW, AuthorizationResource::USER_ROLES, isLibrarian: false),
      new PermissionDefinition('Update UserRoles', AuthorizationAction::UPDATE, AuthorizationResource::USER_ROLES, isLibrarian: false),
      new PermissionDefinition('View Roles', AuthorizationAction::VIEW, AuthorizationResource::ROLES, isLibrarian: false),
      new PermissionDefinition('Create Roles', AuthorizationAction::CREATE, AuthorizationResource::ROLES, isLibrarian: false),
      new PermissionDefinition('Update Roles', AuthorizationAction::UPDATE, AuthorizationResource::ROLES, isLibrarian: false),
      new PermissionDefinition('Delete Roles', AuthorizationAction::DELETE, AuthorizationResource::ROLES, isLibrarian: false),

      // === ADMIN & LIBRARIAN PERMISSIONS ===

      // Dashboard
      new PermissionDefinition('View Dashboard', AuthorizationAction::VIEW, AuthorizationResource::DASHBOARD),

      // Authors Management
      new PermissionDefinition('View Authors', AuthorizationAction::VIEW, AuthorizationResource::AUTHORS, isAuthor: true, isMember: true),
      new PermissionDefinition('Search Authors', AuthorizationAction::SEARCH, AuthorizationResource::AUTHORS, isAuthor: true),
      new PermissionDefinition('Create Authors', AuthorizationAction::CREATE, AuthorizationResource::AUTHORS),
      new PermissionDefinition('Update Authors', AuthorizationAction::UPDATE, AuthorizationResource::AUTHORS, isAuthor: true),
      new PermissionDefinition('Delete Authors', AuthorizationAction::DELETE, AuthorizationResource::AUTHORS, isAuthor: true),
      new PermissionDefinition('Export Authors', AuthorizationAction::EXPORT, AuthorizationResource::AUTHORS),

      // Books Management
      new PermissionDefinition('View Books', AuthorizationAction::VIEW, AuthorizationResource::BOOKS, isAuthor: true, isMember: true),
      new PermissionDefinition('Search Books', AuthorizationAction::SEARCH, AuthorizationResource::BOOKS, isAuthor: true, isMember: true),
      new PermissionDefinition('Create Books', AuthorizationAction::CREATE, AuthorizationResource::BOOKS, isAuthor: true),
      new PermissionDefinition('Update Books', AuthorizationAction::UPDATE, AuthorizationResource::BOOKS, isAuthor: true),
      new PermissionDefinition('Delete Books', AuthorizationAction::DELETE, AuthorizationResource::BOOKS, isAuthor: true),
      new PermissionDefinition('Export Books', AuthorizationAction::EXPORT, AuthorizationResource::BOOKS),

      // Members Management
      new PermissionDefinition('View Members', AuthorizationAction::VIEW, AuthorizationResource::MEMBERS),
      new PermissionDefinition('Search Members', AuthorizationAction::SEARCH, AuthorizationResource::MEMBERS),
      new PermissionDefinition('Create Members', AuthorizationAction::CREATE, AuthorizationResource::MEMBERS),
      new PermissionDefinition('Update Members', AuthorizationAction::UPDATE, AuthorizationResource::MEMBERS, isMember: true),
      new PermissionDefinition('Delete Members', AuthorizationAction::DELETE, AuthorizationResource::MEMBERS, isMember: true),
      new PermissionDefinition('Export Members', AuthorizationAction::EXPORT, AuthorizationResource::MEMBERS),

      // Borrowings Management
      new PermissionDefinition('View Borrowings', AuthorizationAction::VIEW, AuthorizationResource::BORROWINGS, isMember: true),
      new PermissionDefinition('Search Borrowings', AuthorizationAction::SEARCH, AuthorizationResource::BORROWINGS),
      new PermissionDefinition('Create Borrowings', AuthorizationAction::CREATE, AuthorizationResource::BORROWINGS, isMember: true),
      new PermissionDefinition('Update Borrowings', AuthorizationAction::UPDATE, AuthorizationResource::BORROWINGS, isMember: true),
      new PermissionDefinition('Delete Borrowings', AuthorizationAction::DELETE, AuthorizationResource::BORROWINGS),
      new PermissionDefinition('Export Borrowings', AuthorizationAction::EXPORT, AuthorizationResource::BORROWINGS),

      // Categories Management
      new PermissionDefinition('View Categories', AuthorizationAction::VIEW, AuthorizationResource::CATEGORIES, isAuthor: true, isMember: true),
      new PermissionDefinition('Search Categories', AuthorizationAction::SEARCH, AuthorizationResource::CATEGORIES, isAuthor: true, isMember: true),
      new PermissionDefinition('Create Categories', AuthorizationAction::CREATE, AuthorizationResource::CATEGORIES),
      new PermissionDefinition('Update Categories', AuthorizationAction::UPDATE, AuthorizationResource::CATEGORIES),
      new PermissionDefinition('Delete Categories', AuthorizationAction::DELETE, AuthorizationResource::CATEGORIES),
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
