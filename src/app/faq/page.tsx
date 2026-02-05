import { Metadata } from 'next';
import FAQClient from '@/components/FAQClient';

export const metadata: Metadata = {
    title: "Frequently Asked Questions | Apval Help",
    description: "Find answers to common questions about Apval. Learn how to create, edit, share, and secure your digital romantic assets.",
    alternates: {
        canonical: "/faq",
    },
};

export default function FAQPage() {
    return <FAQClient />;
}
