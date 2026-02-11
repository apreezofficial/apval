'use client';
import { motion } from 'framer-motion';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

export default function PrivacyClient() {
    return (
        <main className="min-h-screen bg-white text-black font-geist antialiased">
            <style jsx global>{`
                @import url('https://fonts.googleapis.com/css2?family=Onest:wght@300;400;500;600;700&display=swap');
                
                body {
                    font-family: 'Geist', 'Onest', sans-serif;
                    background-color: white !important;
                    color: black !important;
                }
            `}</style>

            <Navbar />

            <div className="mt-0">
                <header className="flex bg-[#fdeae6] items-center justify-center h-[21rem] md:h-[30rem]">
                    <div className="text-center">
                        <motion.h1
                            initial={{ opacity: 0, y: -20 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="text-[2.5rem] md:text-[4rem] font-bold tracking-tight text-[#1a1a1a]"
                        >
                            Privacy Policy.
                        </motion.h1>
                    </div>
                </header>

                <div className="flex justify-center mt-[3rem] mb-20">
                    <div className="p-2 my_fixed_width text-[0.9rem] leading-relaxed space-y-8">
                        <section>
                            <h2 className="text-[1.3rem] font-semibold mb-4">Introduction</h2>
                            <p>Apval ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you use our digital romance platform.</p>
                        </section>

                        <section>
                            <h2 className="text-[1.3rem] font-semibold mb-4">Information We Collect</h2>
                            <p>We collect information that you provide directly to us when you create an account, personalize a template, or contact us. This may include your name, email address, and the content of the messages you include in your digital cards.</p>
                        </section>

                        <section>
                            <h2 className="text-[1.3rem] font-semibold mb-4">How We Use Your Information</h2>
                            <p>We use your information to provide and improve our services, customize your experience, and ensure the security of our platform. Your personalized messages are stored to allow you to share them with your intended recipients.</p>
                        </section>

                        <section>
                            <h2 className="text-[1.3rem] font-semibold mb-4">Data Security</h2>
                            <p>Architected by Precious Adedokun, Apval implements robust security measures to protect your data from unauthorized access, alteration, or disclosure. We prioritize backend integrity and secure infrastructure design.</p>
                        </section>
                    </div>
                </div>
            </div>

            <Footer />
        </main>
    );
}
