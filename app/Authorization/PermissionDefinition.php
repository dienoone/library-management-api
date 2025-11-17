<?php

namespace App\Authorization;

class PermissionDefinition
{
  public string $name;

  public function __construct(
    public string $description,
    public string $action,
    public string $resource,
    public bool $isRoot = true,
    public bool $isLibrarian = true,
    public bool $isAuthor = false,
    public bool $isMember = false
  ) {
    $this->name = AuthorizationPermission::nameFor($action, $resource);
  }
}
