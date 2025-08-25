import axios, {AxiosError} from 'axios';
import {createApiClient} from '@/api/schemas.ts';
import {ZodiosHooks} from "@zodios/react";

const API_URL = import.meta.env.VITE_API || "http://localhost/api";
const BASE_URL = import.meta.env.VITE_BASE_URL || "http://localhost";

const axiosInstance = axios.create({
  baseURL: API_URL,
});

axiosInstance.defaults.headers.common['Accept'] = 'application/json';

axiosInstance.defaults.headers.common['Content-Type'] = 'application/json';

axiosInstance.interceptors.request.use(
  async (config) => {
    const tokenType = localStorage.getItem('token_type');
    const token = localStorage.getItem('access_token'); // Or your preferred secure storage
    if (token) {
      config.headers.Authorization = `${tokenType} ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

axiosInstance.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    const isLoginPage = window.location.pathname.endsWith('/login')
    const status = (error as AxiosError)?.response?.status

    if (status === 401 && !isLoginPage) {
      console.error('Unauthorized: Redirecting to login...');
      localStorage.removeItem('accessToken');
      window.location.href = BASE_URL + '/auth/login';
      return
    }

    return Promise.reject(error);
  },
);

export const apiClient = createApiClient(API_URL, {
  axiosInstance: axiosInstance,
  axiosConfig: {
    responseType: 'json',
  }
});

export const useApiClient = new ZodiosHooks("api", apiClient);
