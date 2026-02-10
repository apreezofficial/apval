import { Metadata } from 'next';
import SupportClient from '@/components/SupportClient';

export const metadata: Metadata = {
    title: "Support Apval | Show Your Love",
    description: "Support the Apval project by starring on GitHub, making a donation, or reaching out via WhatsApp. Every bit of support helps keep digital romance alive!",
    alternates: {
        canonical: "/support",
    },
};

export default function SupportPage() {
    return <SupportClient />;
}
