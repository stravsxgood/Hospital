import type { Nurse } from '@/types/hospital';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    role?: string;
    is_doctor?: boolean;
    current_team_id?: number | null;
    doctor?: {
        doctor_id?: number;
        name: string;
        sip_number?: string;
        specialization_name?: string;
    } | null;
    nurse?: Nurse | null;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

/* Tambahan Tipe untuk Modul Autentikasi API */
export type LoginCredentials = {
    email: string;
    password: string;
};

export type RegisterPayload = {
    name: string;
    email: string;
    resident_n: string;
    gender: string;
    birthday_date: string;
    number_phone?: string;
    password: string;
    password_confirmation: string;
};

export type AuthResponse = {
    status: string;
    message: string;
    token?: string;
    user?: User;
    data?: {
        token: string;
        user: User;
    };
};
