export const Roles = {
  Admin: 'Admin',
  User: 'User',
  OrgAdmin: 'OrgAdmin',
  TenantAdmin: 'TenantAdmin',
} as const;

export type Role = typeof Roles[keyof typeof Roles];

export const AllRoles = [Roles.Admin, Roles.User, Roles.OrgAdmin, Roles.TenantAdmin]

export const RoleLabels: Record<Role, string> = {
  Admin: 'Admin',
  User: 'User',
  OrgAdmin: 'Organization Admin',
  TenantAdmin: 'Tenant Admin',
};
