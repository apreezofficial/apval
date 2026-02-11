'use client';
import NotFoundUI from '@/components/NotFoundUI';
import { usePathname } from 'next/navigation';

export default function NotFound() {
    const pathname = usePathname();

    return (
        <NotFoundUI
            message="is currently unavailable in our global network"
            path={pathname}
        />
    );
}
