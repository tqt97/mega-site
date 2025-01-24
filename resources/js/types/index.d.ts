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

export interface Amenity {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
    pivot: {
        room_type_id: number;
        amenity_id: number;
    };
}

export interface RoomType {
    id: number;
    name: string;
    description: string;
    price_per_night: number;
    thumbnailUrl: string;
    size: number;
    capacity: number;
    amenities: Amenity[];
    rooms: Room[];
    created_at: string;
    updated_at: string;
}

export interface SearchResults {
    roomTypes: RoomType[];
}

export interface RoomTypeCardProps {
    roomType: RoomType;
}

export interface Pricing {
    nights: number;
    price_per_night: string;
    total_price: number;
}

export interface BookingData {
    room_type_id: number;
    check_in: string;
    check_out: string;
    guests: number;
    pricing: Pricing;
    roomType: RoomType;
    rooms: Room[];
}

export interface FlashMessageProps {
    message: string | null;
    type: 'success' | 'error';
}

export interface CreateBookingData {
    room_type_id: number;
    check_in: string;
    check_out: string;
    guests: number;
}

export interface SearchData {
    check_in: string;
    check_out: string;
    guests: number;
}

export interface BookingFormData {
    room_type_id: number | null;
    check_in: string;
    check_out: string;
    guests: number;
    name: string;
    email: string;
    room_id: number | null;
}
