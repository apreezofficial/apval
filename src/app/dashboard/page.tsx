import { Metadata } from 'next';
import DashboardClient from '@/components/DashboardClient';

export const metadata: Metadata = {
    title: "Dashboard | My Love Assets",
    description: "Keep track of your heart's work and share the vibes. Edit, share, or create new cinematic experiences in your Apval dashboard.",
    alternates: {
        canonical: "/dashboard",
    },
};

export default function DashboardPage() {
    return <DashboardClient />;
}
