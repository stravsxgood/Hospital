import { ref } from 'vue';
import api from '@/lib/axios';
import type { User, LoginCredentials, RegisterPayload, AuthResponse } from '@/types/auth';

const isClient = typeof window !== 'undefined';

const getInitialUser = (): User | null => {
  if (!isClient) {
return null;
}

  const saved = localStorage.getItem('user_data');

  return saved ? JSON.parse(saved) : null;
};

const getInitialAuth = (): boolean => {
  if (!isClient) {
return false;
}

  return !!localStorage.getItem('auth_token');
};

const currentUser = ref<User | null>(getInitialUser());
const isAuthenticated = ref<boolean>(getInitialAuth());

export function useAuth() {
  const isLoading = ref<boolean>(false);
  const validationErrors = ref<Record<string, string[]>>({});
  const errorMessage = ref<string>('');

  const resetErrors = () => {
    validationErrors.value = {};
    errorMessage.value = '';
  };

  const handleAuthSuccess = (response: AuthResponse) => {
    const token = response.token || response.data?.token;
    const user = response.user || response.data?.user;

    if (isClient) {
      if (token) {
        localStorage.setItem('auth_token', token);
        isAuthenticated.value = true;
      }

      if (user) {
        localStorage.setItem('user_data', JSON.stringify(user));
        currentUser.value = user;
      }
    }
  };

  const login = async (credentials: LoginCredentials): Promise<boolean> => {
    isLoading.value = true;
    resetErrors();

    try {
      const response = await api.post<AuthResponse>('/login', credentials);
      handleAuthSuccess(response.data);

      return true;
    } catch (error: any) {
      if (error.response?.status === 422) {
        validationErrors.value = error.response.data.errors || {};
      } else {
        errorMessage.value = error.response?.data?.message || 'Email atau kata sandi tidak valid.';
      }

      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const register = async (payload: RegisterPayload): Promise<boolean> => {
    isLoading.value = true;
    resetErrors();

    try {
      const response = await api.post<AuthResponse>('/register', payload);
      handleAuthSuccess(response.data);

      return true;
    } catch (error: any) {
      if (error.response?.status === 422) {
        validationErrors.value = error.response.data.errors || {};
      } else {
        errorMessage.value = error.response?.data?.message || 'Pendaftaran gagal diproses.';
      }

      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async (): Promise<void> => {
    try {
      await api.post('/logout');
    } catch (_e: any) {
      // Abaikan error saat proses logout di backend
    } finally {
      if (isClient) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        window.location.href = '/login';
      }

      currentUser.value = null;
      isAuthenticated.value = false;
    }
  };

  return {
    currentUser,
    isAuthenticated,
    isLoading,
    validationErrors,
    errorMessage,
    login,
    register,
    logout,
    resetErrors,
  };
}