import {create} from 'zustand'
import type {z} from "zod";
import {schemas} from "@/api/schemas.ts";

type TUser = z.infer<typeof schemas.User>

type AuthState = {
  user?: TUser,
  access_token?: string,
  refresh_token?: string,
  token_type?: string,
  expires_in?: number,
  setAccessToken: (value?: string) => void,
  setRefreshToken: (value?: string) => void,
  setTokenType: (value?: string) => void,
  setExpiresIn: (value?: number) => void,
  setUser: (value?: TUser) => void,
  logout: () => void,
  isLoggedIn: () => boolean,
}

export const useAuthStore = create<AuthState>()((set) => ({
  user: undefined,
  access_token: localStorage.getItem("access_token") ?? '',
  refresh_token: localStorage.getItem("refresh_token") ?? '',
  token_type: localStorage.getItem("token_type") ?? '',
  expires_in: localStorage.getItem("expires_in") ? Number(localStorage.getItem("expires_in")) : 0,
  setAccessToken: (access_token: string) =>  {
    set(() => ({ access_token }))
    localStorage.setItem("access_token", access_token)
  },
  setRefreshToken: (refresh_token: string) =>  {
    set(() => ({ refresh_token }))
    localStorage.setItem("refresh_token", refresh_token)
  },
  setTokenType: (token_type: string) =>  {
    set(() => ({ token_type }))
    localStorage.setItem("token_type", token_type)
  },
  setExpiresIn: (expires_in: number) =>  {
    set(() => ({ expires_in}))
    localStorage.setItem("expires_in", String(expires_in))
  },
  setUser: (user: TUser) => set(() => ({ user })),
  logout: () => {
    set(() => ({
      user: undefined,
      access_token: undefined,
      refresh_token: undefined,
      token_type: undefined,
      expires_in: undefined,
    }))
    localStorage.clear()
  },
  isLoggedIn: (): boolean => {
    const state: AuthState = useAuthStore.getState()
    return !!(
      state.access_token &&
      state.refresh_token &&
      state.token_type &&
      state.expires_in
    )
  }
}))