import axios, { AxiosRequestConfig, AxiosResponse } from 'axios';
import { siteConfig } from './config';

// Create axios instance with base URL
const api = axios.create({
    baseURL: siteConfig.apiUrl,
    headers: {
        'Content-Type': 'application/json',
    },
});

/**
 * Helper to make GET requests
 * Returns the data directly
 */
export async function apiGet<T = any>(endpoint: string): Promise<T> {
    const response = await api.get<T>(endpoint);
    return response.data;
}

/**
 * Helper to make POST requests
 * Returns the data directly
 */
export async function apiPost<T = any>(
    endpoint: string,
    data: any
): Promise<T> {
    const response = await api.post<T>(endpoint, data);
    return response.data;
}

/**
 * Helper to make PUT requests
 * Returns the data directly
 */
export async function apiPut<T = any>(
    endpoint: string,
    data: any
): Promise<T> {
    const response = await api.put<T>(endpoint, data);
    return response.data;
}

/**
 * Helper to make DELETE requests
 * Returns the data directly
 */
export async function apiDelete<T = any>(endpoint: string): Promise<T> {
    const response = await api.delete<T>(endpoint);
    return response.data;
}

export default api;
