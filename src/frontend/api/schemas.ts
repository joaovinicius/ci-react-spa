import { makeApi, Zodios, type ZodiosOptions } from "@zodios/core";
import { z } from "zod";

type AuthResponse = Partial<{
  token: string;
  user: User;
}>;
type User = Partial<{
  id: number;
  email: string;
  name: string;
  phone: string | null;
  bio: string | null;
  email_verified: boolean;
  org_id: number;
  tenant_id: number;
  role: Array<string>;
  created_at: Partial<{
    date: string;
    timezone_type: number;
    timezone: string;
  }>;
  updated_at: Partial<{
    date: string;
    timezone_type: number;
    timezone: string;
  }>;
}>;

const User: z.ZodType<User> = z
  .object({
    id: z.number().int(),
    email: z.string(),
    name: z.string(),
    phone: z.union([z.string(), z.null()]),
    bio: z.union([z.string(), z.null()]),
    email_verified: z.boolean(),
    org_id: z.number().int(),
    tenant_id: z.number().int(),
    role: z.array(z.string()),
    created_at: z
      .object({
        date: z.string(),
        timezone_type: z.number().int(),
        timezone: z.string(),
      })
      .partial()
      .strict()
      .passthrough(),
    updated_at: z
      .object({
        date: z.string(),
        timezone_type: z.number().int(),
        timezone: z.string(),
      })
      .partial()
      .strict()
      .passthrough(),
  })
  .partial()
  .strict()
  .passthrough();
const verifyUserPassword_Body = z
  .object({ email: z.string().email(), password: z.string() })
  .strict()
  .passthrough();
const resetUserPassword_Body = z
  .object({
    token: z.string(),
    password: z.string(),
    password_confirm: z.string(),
  })
  .strict()
  .passthrough();
const UserInput = z
  .object({
    email: z.string(),
    name: z.string(),
    password: z.string(),
    phone: z.union([z.string(), z.null()]).optional(),
    bio: z.union([z.string(), z.null()]).optional(),
    email_verified: z.boolean().optional(),
    org_id: z.number().int().optional(),
    tenant_id: z.number().int().optional(),
    role: z.array(z.string()).optional(),
  })
  .strict()
  .passthrough();
const Organization = z
  .object({
    id: z.number().int(),
    name: z.string(),
    slug: z.string(),
    created_at: z
      .object({
        date: z.string(),
        timezone_type: z.number().int(),
        timezone: z.string(),
      })
      .partial()
      .strict()
      .passthrough(),
    updated_at: z
      .object({
        date: z.string(),
        timezone_type: z.number().int(),
        timezone: z.string(),
      })
      .partial()
      .strict()
      .passthrough(),
  })
  .partial()
  .strict()
  .passthrough();
const OrganizationInput = z
  .object({ slug: z.string(), name: z.string() })
  .strict()
  .passthrough();
const Tenant = z
  .object({
    id: z.number().int(),
    name: z.string(),
    domain: z.string(),
    config: z.object({}).partial().strict().passthrough(),
    status: z.string(),
    org_id: z.number().int(),
    created_at: z
      .object({
        date: z.string(),
        timezone_type: z.number().int(),
        timezone: z.string(),
      })
      .partial()
      .strict()
      .passthrough(),
    updated_at: z
      .object({
        date: z.string(),
        timezone_type: z.number().int(),
        timezone: z.string(),
      })
      .partial()
      .strict()
      .passthrough(),
  })
  .partial()
  .strict()
  .passthrough();
const TenantInput = z
  .object({
    domain: z.string(),
    name: z.string(),
    status: z.enum(["draft", "published", "archived"]),
    config: z.object({}).partial().strict().passthrough(),
    org_id: z.number().int(),
  })
  .strict()
  .passthrough();
const AuthResponse: z.ZodType<AuthResponse> = z
  .object({ token: z.string(), user: User })
  .partial()
  .strict()
  .passthrough();

export const schemas = {
  User,
  verifyUserPassword_Body,
  resetUserPassword_Body,
  UserInput,
  Organization,
  OrganizationInput,
  Tenant,
  TenantInput,
  AuthResponse,
};

const endpoints = makeApi([
  {
    method: "post",
    path: "/auth/forgot-password",
    alias: "forgotPassword",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: z.object({ email: z.string().email() }).strict().passthrough(),
      },
    ],
    response: z
      .object({ message: z.string() })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 400,
        description: `Invalid input (e.g., email not provided or invalid format)`,
        schema: z
          .object({ messages: z.object({}).partial().strict().passthrough() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "post",
    path: "/auth/login",
    alias: "verifyUserPassword",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: verifyUserPassword_Body,
      },
    ],
    response: z
      .object({
        access_token: z.string(),
        refresh_token: z.string(),
        token_type: z.string(),
        expires_in: z.number().int(),
        user: User,
      })
      .strict()
      .passthrough(),
    errors: [
      {
        status: 400,
        description: `Validation error (e.g., missing or invalid email/password)`,
        schema: z
          .object({ messages: z.object({}).partial().strict().passthrough() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 401,
        description: `Invalid credentials`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/auth/me",
    alias: "getCurrentUser",
    requestFormat: "json",
    response: User,
    errors: [
      {
        status: 401,
        description: `Unauthorized`,
        schema: z
          .object({
            status: z.number().int(),
            error: z.number().int(),
            messages: z
              .object({ error: z.string() })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "post",
    path: "/auth/refresh-token",
    alias: "refreshTokens",
    description: `Exchanges a valid refresh token for a new access token and a new refresh token.`,
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: z.object({ refresh_token: z.string() }).strict().passthrough(),
      },
    ],
    response: z
      .object({
        access_token: z.string(),
        refresh_token: z.string(),
        token_type: z.string(),
        expires_in: z.number().int(),
      })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 400,
        description: `Validation error (e.g., missing refresh_token)`,
        schema: z
          .object({ messages: z.object({}).partial().strict().passthrough() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 401,
        description: `Unauthorized`,
        schema: z
          .object({
            status: z.number().int(),
            error: z.number().int(),
            messages: z
              .object({ error: z.string() })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "post",
    path: "/auth/register",
    alias: "register",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: UserInput,
      },
    ],
    response: User,
    errors: [
      {
        status: 422,
        description: `Validation Error`,
        schema: z
          .object({
            errors: z
              .object({
                email: z.string(),
                name: z.string(),
                password: z.string(),
              })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "post",
    path: "/auth/reset-password",
    alias: "resetUserPassword",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: resetUserPassword_Body,
      },
    ],
    response: z
      .object({ message: z.string() })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 400,
        description: `Validation error (e.g., passwords don&#x27;t match, token missing)`,
        schema: z
          .object({ messages: z.object({}).partial().strict().passthrough() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 401,
        description: `Invalid, expired, or misused token`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 404,
        description: `User not found for the token`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error or password update failure`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "post",
    path: "/auth/verify-reset-token",
    alias: "verifyPasswordResetToken",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: z.object({ token: z.string() }).strict().passthrough(),
      },
    ],
    response: z
      .object({ message: z.string() })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 400,
        description: `Invalid input (e.g., token not provided)`,
        schema: z
          .object({ messages: z.object({}).partial().strict().passthrough() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 401,
        description: `Invalid or expired token`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/orgs",
    alias: "getOrganizations",
    requestFormat: "json",
    parameters: [
      {
        name: "limit",
        type: "Query",
        schema: z.number().int().optional().default(20),
      },
      {
        name: "offset",
        type: "Query",
        schema: z.number().int().optional().default(0),
      },
    ],
    response: z
      .object({
        data: z.array(Organization),
        paging: z
          .object({
            total: z.number().int(),
            limit: z.number().int(),
            offset: z.number().int(),
          })
          .partial()
          .strict()
          .passthrough(),
      })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "post",
    path: "/orgs",
    alias: "createOrganization",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: OrganizationInput,
      },
    ],
    response: Organization,
    errors: [
      {
        status: 422,
        description: `Validation Error`,
        schema: z
          .object({
            errors: z
              .object({ slug: z.string(), name: z.string() })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/orgs/:id",
    alias: "getOrganizationById",
    requestFormat: "json",
    parameters: [
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: Organization,
    errors: [
      {
        status: 404,
        description: `Organization not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "put",
    path: "/orgs/:id",
    alias: "updateOrganization",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: OrganizationInput,
      },
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: Organization,
    errors: [
      {
        status: 404,
        description: `Organization not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 422,
        description: `Validation Error`,
        schema: z
          .object({
            errors: z
              .object({ slug: z.string(), name: z.string() })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "delete",
    path: "/orgs/:id",
    alias: "deleteOrganization",
    requestFormat: "json",
    parameters: [
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: z
      .object({ id: z.number().int() })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 404,
        description: `Organization not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/orgs/all",
    alias: "getAllOrganizations",
    requestFormat: "json",
    response: z.array(Organization),
    errors: [
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/tenants",
    alias: "getTenants",
    requestFormat: "json",
    parameters: [
      {
        name: "limit",
        type: "Query",
        schema: z.number().int().optional().default(20),
      },
      {
        name: "offset",
        type: "Query",
        schema: z.number().int().optional().default(0),
      },
    ],
    response: z
      .object({
        data: z.array(Tenant),
        paging: z
          .object({
            total: z.number().int(),
            limit: z.number().int(),
            offset: z.number().int(),
          })
          .partial()
          .strict()
          .passthrough(),
      })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "post",
    path: "/tenants",
    alias: "createTenant",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: TenantInput,
      },
    ],
    response: Tenant,
    errors: [
      {
        status: 422,
        description: `Validation Error`,
        schema: z
          .object({
            errors: z
              .object({ slug: z.string(), name: z.string() })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/tenants/:id",
    alias: "getTenantById",
    requestFormat: "json",
    parameters: [
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: Tenant,
    errors: [
      {
        status: 404,
        description: `Tenant not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "put",
    path: "/tenants/:id",
    alias: "updateTenant",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: TenantInput,
      },
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: Tenant,
    errors: [
      {
        status: 404,
        description: `Tenant not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 422,
        description: `Validation Error`,
        schema: z
          .object({
            errors: z
              .object({ slug: z.string(), name: z.string() })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "delete",
    path: "/tenants/:id",
    alias: "deleteTenant",
    requestFormat: "json",
    parameters: [
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: z
      .object({ id: z.number().int() })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 404,
        description: `Tenant not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/tenants/all",
    alias: "getAllTenants",
    requestFormat: "json",
    response: z.array(Tenant),
    errors: [
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/tenants/domain/:domain",
    alias: "getTenantByDomain",
    requestFormat: "json",
    parameters: [
      {
        name: "domain",
        type: "Path",
        schema: z.string(),
      },
    ],
    response: Tenant,
    errors: [
      {
        status: 404,
        description: `Tenant not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/tenants/org/:orgId",
    alias: "getTenantsByOrgId",
    requestFormat: "json",
    parameters: [
      {
        name: "orgId",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: z.array(Tenant),
    errors: [
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/users",
    alias: "getUsers",
    requestFormat: "json",
    parameters: [
      {
        name: "limit",
        type: "Query",
        schema: z.number().int().optional().default(20),
      },
      {
        name: "offset",
        type: "Query",
        schema: z.number().int().optional().default(0),
      },
    ],
    response: z
      .object({
        data: z.array(User),
        paging: z
          .object({
            total: z.number().int(),
            limit: z.number().int(),
            offset: z.number().int(),
          })
          .partial()
          .strict()
          .passthrough(),
      })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "post",
    path: "/users",
    alias: "createUser",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: UserInput,
      },
    ],
    response: User,
    errors: [
      {
        status: 422,
        description: `Validation Error`,
        schema: z
          .object({
            errors: z
              .object({
                email: z.string(),
                name: z.string(),
                password: z.string(),
              })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/users/:id",
    alias: "getUserById",
    requestFormat: "json",
    parameters: [
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: User,
    errors: [
      {
        status: 404,
        description: `User not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "put",
    path: "/users/:id",
    alias: "updateUser",
    requestFormat: "json",
    parameters: [
      {
        name: "body",
        type: "Body",
        schema: UserInput,
      },
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: User,
    errors: [
      {
        status: 404,
        description: `User not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 422,
        description: `Validation Error`,
        schema: z
          .object({
            errors: z
              .object({ email: z.string(), name: z.string() })
              .partial()
              .strict()
              .passthrough(),
          })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "delete",
    path: "/users/:id",
    alias: "deleteUser",
    requestFormat: "json",
    parameters: [
      {
        name: "id",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: z
      .object({ id: z.number().int() })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 404,
        description: `User not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/users/all",
    alias: "getAllUsers",
    requestFormat: "json",
    response: z.array(User),
    errors: [
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/users/org/:orgId",
    alias: "getUsersByOrgId",
    requestFormat: "json",
    parameters: [
      {
        name: "orgId",
        type: "Path",
        schema: z.number().int(),
      },
      {
        name: "limit",
        type: "Query",
        schema: z.number().int().optional().default(20),
      },
      {
        name: "offset",
        type: "Query",
        schema: z.number().int().optional().default(0),
      },
    ],
    response: z
      .object({
        data: z.array(User),
        paging: z
          .object({
            total: z.number().int(),
            limit: z.number().int(),
            offset: z.number().int(),
          })
          .partial()
          .strict()
          .passthrough(),
      })
      .partial()
      .strict()
      .passthrough(),
    errors: [
      {
        status: 404,
        description: `Organization not found`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
  {
    method: "get",
    path: "/users/tenant/:tenantId",
    alias: "getUsersByTenantId",
    requestFormat: "json",
    parameters: [
      {
        name: "tenantId",
        type: "Path",
        schema: z.number().int(),
      },
    ],
    response: z.array(User),
    errors: [
      {
        status: 500,
        description: `Server Error`,
        schema: z
          .object({ message: z.string() })
          .partial()
          .strict()
          .passthrough(),
      },
    ],
  },
]);

export const api = new Zodios(endpoints);

export function createApiClient(baseUrl: string, options?: ZodiosOptions) {
  return new Zodios(baseUrl, endpoints, options);
}
