import axios from 'axios';
import type {
    AxiosInstance,
    InternalAxiosRequestConfig,
    AxiosResponse,
    AxiosError,
} from 'axios';

const api: AxiosInstance = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

api.interceptors.request.use(
    (config: InternalAxiosRequestConfig): InternalAxiosRequestConfig => {
        if (typeof window !== 'undefined') {
            const token = localStorage.getItem('auth_token');

            if (token && config.headers) {
                config.headers.Authorization = `Bearer ${token}`;
            }
        }

        return config;
    },
    (error: AxiosError) => Promise.reject(error),
);

api.interceptors.response.use(
    (response: AxiosResponse): AxiosResponse => response,
    (error: AxiosError) => {
        if (error.response?.status === 401 && typeof window !== 'undefined') {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');

            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }

        return Promise.reject(error);
    },
);

export default api;
