import {create} from 'zustand'
import type {z} from "zod";
import {schemas} from "@/api/schemas.ts";

type TTenant = z.infer<typeof schemas.Tenant>

type TenantState = {
  tenants: TTenant[]
  tenant?: TTenant,
  setTenant: (tenant: TTenant) => void
  setTenants: (tenants: TTenant[]) => void
}

export const useTenantStore = create<TenantState>()((set) => ({
  tenants: [],
  tenant: undefined,
  setTenant: (tenant: TTenant) => set(() => ({ tenant })),
  setTenants: (tenants: TTenant[]) => set(() => ({ tenants }))
}))