import { useEffect, useRef, useCallback } from 'react';
import { useApiClient } from '@/api/apiClient.ts';
import { useAuthStore } from '@/store/useAuthStore.ts';

export const useTokenRefresh = () => {
  const intervalRef = useRef<NodeJS.Timeout | null>(null);
  const { refresh_token, setAuthTokens, logout, isLoggedIn } = useAuthStore();

  const refreshMutation = useApiClient.useMutation('/auth/refresh-token', {
    onSuccess: (data) => {
      if (data.access_token && data.refresh_token && data.token_type && data.expires_in) {
        setAuthTokens({
          access_token: data.access_token,
          refresh_token: data.refresh_token,
          token_type: data.token_type,
          expires_in: data.expires_in
        });
        console.log('Tokens refreshed successfully');
      }
    },
    onError: (error) => {
      console.error('Failed to refresh tokens:', error);
      logout();
    }
  });

  const refreshTokens = useCallback(async () => {
    if (!refresh_token || !isLoggedIn()) {
      console.warn('Cannot refresh tokens: user not logged in or no refresh token available');
      return;
    }

    try {
      await refreshMutation.mutateAsync({
        refresh_token
      });
    } catch (error) {
      console.error('Token refresh failed:', error);
    }
  }, [refresh_token, refreshMutation, isLoggedIn]);

  const startTokenRefresh = useCallback(() => {
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
    }

    // Set interval for 10 minutes (600000ms)
    intervalRef.current = setInterval(() => {
      refreshTokens();
    }, 10 * 60 * 1000);

    console.log('Token refresh interval started (10 minutes)');
  }, [refreshTokens]);

  const stopTokenRefresh = useCallback(() => {
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
      intervalRef.current = null;
      console.log('Token refresh interval stopped');
    }
  }, []);

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      stopTokenRefresh();
    };
  }, [stopTokenRefresh]);

  // Start/stop based on login status
  useEffect(() => {
    if (isLoggedIn() && refresh_token) {
      startTokenRefresh();
    } else {
      stopTokenRefresh();
    }
  }, [isLoggedIn, refresh_token, startTokenRefresh, stopTokenRefresh]);

  return {
    refreshTokens,
    startTokenRefresh,
    stopTokenRefresh,
    isRefreshing: refreshMutation.isPending,
    refreshError: refreshMutation.error
  };
};