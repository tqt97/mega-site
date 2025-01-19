import {
    LayoutContextProps,
    LayoutProviderProps,
    Setting,
    User,
} from '@/types';
import { createContext, useContext, useState } from 'react';

const LayoutContext = createContext<LayoutContextProps | undefined>(undefined);

export const LayoutProvider: React.FC<LayoutProviderProps> = ({
    initialUser = null,
    initialSettings = null,
    children,
}) => {
    const [user, setUser] = useState<User | null>(initialUser);
    const [settings, setSettings] = useState<Setting | null>(initialSettings);

    const login = (userData: User): void => {
        setUser(userData);
        // Add any additional login logic here
    };

    const logout = (): void => {
        setUser(null);
        // Add any additional logout logic here
    };

    return (
        <LayoutContext.Provider
            value={{
                user,
                settings,
                isAuthenticated: !!user,
                login,
                logout,
                setSettings,
            }}
        >
            {children}
        </LayoutContext.Provider>
    );
};

// Custom hook to use the LayoutContext
export const useLayout = (): LayoutContextProps => {
    const context = useContext(LayoutContext);
    if (!context) {
        throw new Error('useLayout must be used within a LayoutProvider');
    }
    return context;
};
