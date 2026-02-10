import { Metadata } from 'next';
import AdminDashboardClient from '@/components/AdminDashboardClient';

export const metadata: Metadata = {
    title: 'Admin Dashboard | Apval',
    description: 'Manage premium upgrades and requests',
};

export default function AdminPage() {
    return <AdminDashboardClient />;
}
