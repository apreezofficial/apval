import { Metadata } from 'next';
import RegisterClient from '@/components/RegisterClient';

export const metadata: Metadata = {
    title: "Join Apval | Create Digital Romance",
    description: "Create an account to start building and deploying personalized cinematic romantic assets.",
    robots: {
        index: false,
        follow: true,
    },
};

export default function RegisterPage() {
    return <RegisterClient />;
}
