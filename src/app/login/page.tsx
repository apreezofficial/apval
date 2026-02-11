import { Metadata } from 'next';
import LoginClient from '@/components/LoginClient';

export const metadata: Metadata = {
    title: "Sign In",
    description: "Access your Apval account to manage your digital romance assets and cinematic Valentine experiences.",
    robots: {
        index: true,
        follow: true,
    },
};

export default function LoginPage() {
    return <LoginClient />;
}
