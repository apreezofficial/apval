import { Metadata } from 'next';
import DeveloperClient from '@/components/DeveloperClient';

export const metadata: Metadata = {
    title: "Meet the Developer | Precious Adedokun",
    description: "Learn about the architectural vision behind Apval. Engineer, architect, and dreamer Precious Adedokun shares the mission to blend security with digital romance.",
    alternates: {
        canonical: "/developer",
    },
};

export default function DeveloperPage() {
    return <DeveloperClient />;
}
