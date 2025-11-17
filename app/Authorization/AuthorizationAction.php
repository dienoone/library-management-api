<?php

namespace App\Authorization;

class AuthorizationAction
{
  public const VIEW = 'View';
  public const SEARCH = 'Search';
  public const CREATE = 'Create';
  public const UPDATE = 'Update';
  public const DELETE = 'Delete';
  public const SUBMISSION = 'Submission';
  public const EXPORT = 'Export';
  public const GENERATE = 'Generate';
  public const CLEAN = 'Clean';

  public static function all(): array
  {
    return [
      self::VIEW,
      self::SEARCH,
      self::CREATE,
      self::UPDATE,
      self::DELETE,
      self::SUBMISSION,
      self::EXPORT,
      self::GENERATE,
      self::CLEAN,
    ];
  }
}
