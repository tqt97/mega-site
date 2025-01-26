import { useLayout } from '@/contexts/LayoutContext';

export default function Navigation() {
    const { settings } = useLayout();

    return (
        <nav className="relative z-10 px-6 py-4">
            <div className="mx-auto flex max-w-7xl items-center justify-between">
                <a
                    href={route('bookings.index')}
                    className="text-2xl font-semibold text-white"
                >
                    {settings?.name}
                </a>
            </div>
        </nav>
    );
}
