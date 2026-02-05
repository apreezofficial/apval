'use client';
import { motion } from 'framer-motion';
import Link from 'next/link';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

const faqs = [
    {
        question: "What is Apval?",
        answer: "Apval is a premium platform designed to simplify digital romance. We provide beautiful, customizable templates for digital Valentine's cards and personal romantic websites, helping you express your feelings in a unique and modern way."
    },
    {
        question: "How do I create a new romantic asset?",
        answer: "Simply go to the 'Templates' section, choose a design that speaks to you, and click 'Use Template'. Our intuitive editor will guide you through personalizing the content, colors, and music."
    },
    {
        question: "Is my data secure?",
        answer: "Yes. Architected by Precious Adedokun, Apval follows industry-standard security practices for data integrity and user privacy. Your messages and personalizations are stored securely on our backend infrastructure."
    },
    {
        question: "Can I edit my creations after saving?",
        answer: "Absolutely! You can find all your saved assets in the 'Dashboard'. Clicking the edit icon on any card will reopen the editor with your previous data, allowing you to make any necessary adjustments."
    },
    {
        question: "How do I share my creations?",
        answer: "Once you save a creation, a unique link is generated for it. You can copy this link from your dashboard and share it with your loved one via any messaging platform."
    }
];

export default function FAQClient() {
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
                <header className="flex bg-[#fdeae6] items-center justify-center h-[21rem] md:h-[40rem] lg:min-h-[50vh]">
                    <div className="text-center">
                        <motion.h1
                            initial={{ opacity: 0, y: -20 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="text-[2.5rem] md:text-[4rem] font-bold tracking-tight text-[#1a1a1a]"
                        >
                            FAQs.
                        </motion.h1>
                        <motion.p
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ delay: 0.3 }}
                            className="text-[1rem] md:text-[1.2rem] text-black/60 mt-4"
                        >
                            Commonly asked questions about Apval.
                        </motion.p>
                    </div>
                </header>

                <div className="flex justify-center mt-[3rem]">
                    <div className="p-2 my_fixed_width">
                        <motion.section
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.6 }}
                            className="mb-20"
                        >
                            <h4 className="text-[1rem] my-5 font-semibold">Quick Links</h4>
                            <div className="border-l-2 border-[#1a1a1a] flex flex-col pl-4 py-4 mb-9 text-[0.9rem] space-y-2">
                                <Link href="/developer"><span className="ml-2 text-[0.8rem] inline-flex py-2 rounded-lg space-x-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="0.9rem" height="0.9rem" fill="black">
                                        <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M8 13H12" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M8 17H16" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                    </svg>
                                    <span>About Me</span>
                                </span></Link>
                                <Link href="/faq"><span className="bg-[#1a1a1a] text-white px-3 inline-flex py-2 rounded-lg space-x-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="1.05rem" height="1.05rem" fill="white">
                                        <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M8 13H12" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M8 17H16" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                    </svg>
                                    <span>Frequently Asked Questions</span>
                                </span></Link>
                            </div>
                        </motion.section>

                        <div className="space-y-12 mb-20">
                            {faqs.map((faq, index) => (
                                <motion.div
                                    key={index}
                                    initial={{ opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ delay: index * 0.1 }}
                                >
                                    <h2 className="text-[1.3rem] font-semibold mb-4">{faq.question}</h2>
                                    <p className="text-[0.9rem] text-black/70 leading-relaxed">
                                        {faq.answer}
                                    </p>
                                </motion.div>
                            ))}
                        </div>

                        <h2 className="text-[1.3rem] font-semibold">Still have questions?</h2>
                        <p className="text-[0.9rem] my-2 mb-11">
                            If you couldn&#x27;t find the answer you were looking for, feel free to <a href="https://preciousadedokun.com.ng/contact" target="_blank" rel="noopener noreferrer" className="underline font-medium">contact me directly</a>. I&#x27;m always happy to help.
                        </p>
                    </div>
                </div>
            </div>

            <Footer />
        </main>
    );
}
