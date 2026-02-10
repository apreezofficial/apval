import { Metadata } from 'next';
import PrivacyClient from '@/components/PrivacyClient';

export const metadata: Metadata = {
    title: "Privacy Policy",
    description: "Your privacy is our priority. Learn how Apval collects, uses, and safeguards your personal data and digital romance assets.",
    alternates: {
        canonical: "/privacy",
    },
};

export default function PrivacyPage() {
    return <PrivacyClient />;
}
