<?php

namespace App\Enums;

enum Role: string
{
  case User = 'User';
  case Admin = 'Admin';
  case OrgAdmin = 'OrgAdmin';
  case TenantAdmin = 'TenantAdmin';
}