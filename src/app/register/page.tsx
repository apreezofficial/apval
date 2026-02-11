import { Metadata } from 'next';
import RegisterClient from '@/components/RegisterClient';

export const metadata: Metadata = {
    title: "Join Now",
    description: "Create an account to start building and deploying personalized cinematic romantic assets.",
    robots: {
        index: true,
        follow: true,
    },
};

export default function RegisterPage() {
    return <RegisterClient />;
}
