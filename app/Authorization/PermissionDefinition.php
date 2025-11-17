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
    public bool $isLibrarian = false,
    public bool $isMember = false,
    public bool $isAuthor = false
  ) {
    $this->name = AuthorizationPermission::nameFor($action, $resource);
  }
}
