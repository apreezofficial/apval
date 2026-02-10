import { Metadata } from 'next';
import TermsClient from '@/components/TermsClient';

export const metadata: Metadata = {
    title: "Terms and Conditions",
    description: "Read the rules and guidelines for using Apval. Understand your rights and responsibilities when creating and sharing digital romantic assets.",
    alternates: {
        canonical: "/terms",
    },
};

export default function TermsPage() {
    return <TermsClient />;
}
