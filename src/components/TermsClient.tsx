'use client';
import { motion } from 'framer-motion';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

export default function TermsClient() {
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
                            Terms and Conditions.
                        </motion.h1>
                    </div>
                </header>

                <div className="flex justify-center mt-[3rem] mb-20">
                    <div className="p-2 my_fixed_width text-[0.9rem] leading-relaxed space-y-8">
                        <section>
                            <h2 className="text-[1.3rem] font-semibold mb-4">Acceptance of Terms</h2>
                            <p>By accessing or using Apval, you agree to be bound by these Terms and Conditions. If you do not agree, please do not use our services.</p>
                        </section>

                        <section>
                            <h2 className="text-[1.3rem] font-semibold mb-4">Use of Service</h2>
                            <p>You agree to use Apval for lawful purposes only. You are responsible for the content you generate and share using our platform. We reserve the right to remove content that violates our standards.</p>
                        </section>

                        <section>
                            <h2 className="text-[1.3rem] font-semibold mb-4">Intellectual Property</h2>
                            <p>The templates and platform architecture provided by Apval are the property of Precious Adedokun. You are granted a limited license to use these assets for personal, non-commercial use in sending digital romantic messages.</p>
                        </section>

                        <section>
                            <h2 className="text-[1.3rem] font-semibold mb-4">Disclaimers</h2>
                            <p>Apval is a digital lifestyle project provided "as is." While we strive for excellence and security, we do not guarantee that the service will be uninterrupted or error-free.</p>
                        </section>
                    </div>
                </div>
            </div>

            <Footer />
        </main>
    );
}
