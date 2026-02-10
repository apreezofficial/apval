'use client';
import { motion } from 'framer-motion';
import Link from 'next/link';
import { Github, Heart, MessageCircle, Banknote, Copy, Check } from 'lucide-react';
import { useState } from 'react';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

export default function SupportClient() {
    const [copiedAccount, setCopiedAccount] = useState(false);
    const [copiedPhone, setCopiedPhone] = useState(false);

    const accountNumber = "9064779856";
    const accountName = "Precious Adedokun";
    const bankName = "Moniepoint";
    const whatsappNumber = "+2349064779856";
    const githubRepo = "apreezofficial/apval";

    const copyToClipboard = (text: string, type: 'account' | 'phone') => {
        navigator.clipboard.writeText(text);
        if (type === 'account') {
            setCopiedAccount(true);
            setTimeout(() => setCopiedAccount(false), 2000);
        } else {
            setCopiedPhone(true);
            setTimeout(() => setCopiedPhone(false), 2000);
        }
    };

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
                <header className="flex bg-[#fdeae6] items-center justify-center h-[21rem] md:h-screen md:max-h-[750px] lg:min-h-screen">
                    <div className="text-center">
                        <motion.div
                            initial="hidden"
                            animate="visible"
                            className="text-[1.5rem] md:text-[2.2rem] font-semibold tracking-[-1px] text-[#1a1a1a] flex justify-center flex-wrap"
                        >
                            {"Support Apval.".split("").map((char, index) => (
                                <motion.span
                                    key={index}
                                    variants={{
                                        hidden: { opacity: 0, y: 10 },
                                        visible: {
                                            opacity: 1,
                                            y: 0,
                                            transition: {
                                                duration: 0.1,
                                                delay: index * 0.1,
                                                ease: "easeOut"
                                            }
                                        }
                                    }}
                                    className={char === " " ? "mr-2" : ""}
                                >
                                    {char}
                                </motion.span>
                            ))}
                        </motion.div>
                    </div>
                </header>

                <div className="flex justify-center mt-[3rem]">
                    <div className="p-2 my_fixed_width">
                        <motion.section
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.6 }}
                        >
                            <h4 className="text-[1rem] my-5 font-semibold">Ways to Support</h4>
                            <div className="border-l-2 border-[#1a1a1a] flex flex-col pl-4 py-4 mb-9 text-[0.9rem] space-y-2">
                                <a href={`https://github.com/${githubRepo}`} target="_blank" rel="noopener noreferrer">
                                    <span className="bg-[#1a1a1a] text-white px-3 inline-flex py-2 rounded-lg space-x-4 hover:bg-[#333] transition-colors">
                                        <Github size={18} />
                                        <span>Star on GitHub</span>
                                    </span>
                                </a>
                                <a href={`https://wa.me/${whatsappNumber.replace(/\D/g, '')}`} target="_blank" rel="noopener noreferrer">
                                    <span className="ml-2 text-[0.8rem] inline-flex py-2 rounded-lg space-x-4 hover:text-[#555] transition-colors">
                                        <MessageCircle size={16} />
                                        <span>Chat on WhatsApp</span>
                                    </span>
                                </a>
                                <Link href="/developer">
                                    <span className="ml-2 text-[0.8rem] inline-flex py-2 rounded-lg space-x-4 hover:text-[#555] transition-colors">
                                        <Heart size={16} />
                                        <span>About the Developer</span>
                                    </span>
                                </Link>
                            </div>
                        </motion.section>

                        <motion.section
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.6, delay: 0.2 }}
                        >
                            <h2 className="text-[1.3rem] font-semibold flex items-center gap-2">
                                <Github size={24} />
                                Star on GitHub
                            </h2>
                            <p className="text-[0.9rem] my-2 mb-6">
                                If you love what Apval is doing for digital romance, show your support by starring the project on GitHub! Every star helps the project grow and reach more people who want to share love digitally.
                            </p>
                            <a
                                href={`https://github.com/${githubRepo}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 bg-[#1a1a1a] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#333] transition-all shadow-lg hover:shadow-xl mb-11"
                            >
                                <Github size={20} />
                                <span>Star apreezofficial/apval</span>
                            </a>
                        </motion.section>

                        <motion.section
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.6, delay: 0.3 }}
                        >
                            <h2 className="text-[1.3rem] font-semibold flex items-center gap-2">
                                <Banknote size={24} />
                                Support via Donation
                            </h2>
                            <p className="text-[0.9rem] my-2 mb-4">
                                Running Apval takes time, effort, and resources. Your financial support helps keep the servers running, adds new features, and ensures the platform remains free for everyone. Every contribution, no matter how small, makes a huge difference!
                            </p>

                            <div className="bg-[#fdeae6] border border-[#1a1a1a]/10 rounded-2xl p-6 mb-6">
                                <div className="space-y-4">
                                    <div>
                                        <p className="text-xs uppercase tracking-wider text-[#1a1a1a]/60 mb-1">Bank</p>
                                        <p className="text-lg font-semibold">{bankName}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs uppercase tracking-wider text-[#1a1a1a]/60 mb-1">Account Name</p>
                                        <p className="text-lg font-semibold">{accountName}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs uppercase tracking-wider text-[#1a1a1a]/60 mb-1">Account Number</p>
                                        <div className="flex items-center gap-3">
                                            <p className="text-2xl font-bold tracking-wider">{accountNumber}</p>
                                            <button
                                                onClick={() => copyToClipboard(accountNumber, 'account')}
                                                className="p-2 hover:bg-white/50 rounded-lg transition-colors"
                                                title="Copy account number"
                                            >
                                                {copiedAccount ? (
                                                    <Check size={20} className="text-green-600" />
                                                ) : (
                                                    <Copy size={20} className="text-[#1a1a1a]/60" />
                                                )}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p className="text-[0.85rem] text-[#1a1a1a]/60 italic mb-11">
                                Thank you for believing in the mission of making digital romance accessible, beautiful, and secure for everyone! 💜
                            </p>
                        </motion.section>

                        <motion.section
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.6, delay: 0.4 }}
                        >
                            <h2 className="text-[1.3rem] font-semibold flex items-center gap-2">
                                <MessageCircle size={24} />
                                Get in Touch
                            </h2>
                            <p className="text-[0.9rem] my-2 mb-4">
                                Have questions, feedback, or just want to say hi? I'm always happy to hear from users! Reach out on WhatsApp for quick support, feature requests, or collaboration ideas.
                            </p>

                            <div className="bg-[#fdeae6] border border-[#1a1a1a]/10 rounded-2xl p-6 mb-6">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-xs uppercase tracking-wider text-[#1a1a1a]/60 mb-1">WhatsApp</p>
                                        <p className="text-xl font-bold tracking-wide">{whatsappNumber}</p>
                                    </div>
                                    <button
                                        onClick={() => copyToClipboard(whatsappNumber, 'phone')}
                                        className="p-2 hover:bg-white/50 rounded-lg transition-colors"
                                        title="Copy phone number"
                                    >
                                        {copiedPhone ? (
                                            <Check size={20} className="text-green-600" />
                                        ) : (
                                            <Copy size={20} className="text-[#1a1a1a]/60" />
                                        )}
                                    </button>
                                </div>
                            </div>

                            <a
                                href={`https://wa.me/${whatsappNumber.replace(/\D/g, '')}?text=${encodeURIComponent('Hi! I\'d like to support Apval 💜')}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 bg-[#25D366] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#20BA5A] transition-all shadow-lg hover:shadow-xl mb-11"
                            >
                                <MessageCircle size={20} />
                                <span>Open WhatsApp Chat</span>
                            </a>
                        </motion.section>

                        <motion.section
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.6, delay: 0.5 }}
                            className="mb-11"
                        >
                            <h2 className="text-[1.3rem] font-semibold">Why Support Matters</h2>
                            <p className="text-[0.9rem] my-2">
                                Apval is a passion project built to make digital expressions of love beautiful, secure, and accessible. Your support—whether through a GitHub star, a donation, or simply sharing the platform—helps keep this project alive and growing. Together, we're building something special. Thank you for being part of this journey! ❤️
                            </p>
                        </motion.section>
                    </div>
                </div>
            </div>

            <Footer />
        </main>
    );
}
