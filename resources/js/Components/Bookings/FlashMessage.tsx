import { FlashMessageProps } from '@/types';
import { useEffect, useState } from 'react';

export default function FlashMessage({ message, type }: FlashMessageProps) {
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        const timer = setTimeout(() => setVisible(false), 3000);
        return () => clearTimeout(timer);
    }, []);

    if (!message || !visible) return null;

    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const textColor = 'text-white';

    return (
        <div
            className={`fixed right-4 top-4 rounded p-4 shadow-lg ${bgColor} ${textColor}`}
        >
            {message}
        </div>
    );
}
