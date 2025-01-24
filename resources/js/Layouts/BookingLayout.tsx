import Footer from '@/Components/Bookings/Footer';
import Navigation from '@/Components/Bookings/Navigation';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <>
            <div className="relative min-h-screen">
                <div className="absolute inset-0">
                    <img
                        src={'images/background-image.jpeg'}
                        alt="Luxury Hotel"
                        className="h-full w-full object-cover"
                    />
                    <div className="absolute inset-0 bg-black/40"></div>
                </div>
                <Navigation />

                <div className="relative z-10 mx-auto max-w-7xl px-6 pb-20 pt-16">
                    {children}
                </div>
            </div>
            <Footer />
        </>
    );
}
