import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface Setting {
    id: number;
    name: string;
    email: string;
    phone: string;
    address: string;
    facebook_url?: string | null;
    instagram_url?: string | null;
    twitter_url?: string | null;
    created_at: string;
    updated_at: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
    settings: Setting;
};

export interface LayoutContextProps {
    user: User | null;
    settings: Setting | null;
    isAuthenticated: boolean;
    login: (userData: User) => void;
    logout: () => void;
    setSettings: (settings: Setting) => void;
}

export interface LayoutProviderProps {
    initialUser?: User | null;
    initialSettings?: Setting | null;
    children: ReactNode;
}
