'use client';
import { motion } from 'framer-motion';
import Link from 'next/link';
import { Twitter, Instagram, Facebook, Heart } from 'lucide-react';

export default function Footer() {
    return (
        <footer className="mt-12 flex justify-center py-[5rem] md:mt-0 bg-[#0A0A0A] text-white font-geist border-t border-white/5">
            <div className="my_fixed_width">
                <div className="flex items-center justify-between mb-12">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        className="flex items-center gap-3"
                    >
                        <div className="w-10 h-10 bg-myRed rounded-xl flex items-center justify-center shadow-lg shadow-myRed/20">
                            <Heart className="text-white w-5 h-5 fill-current" />
                        </div>
                        <span className="text-2xl font-bold tracking-tighter uppercase">Ap<span className="text-myRed">val</span></span>
                    </motion.div>
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        className="flex justify-between items-center gap-4"
                    >
                        <a href="https://x.com/preciousade" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-white/5 hover:bg-myRed hover:text-white rounded-xl flex justify-center items-center transition-all border border-white/5">
                            <Twitter size={18} />
                        </a>
                        <a href="https://instagram.com/preciousade" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-white/5 hover:bg-myRed hover:text-white rounded-xl flex justify-center items-center transition-all border border-white/5">
                            <Instagram size={18} />
                        </a>
                        <a href="#" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-white/5 hover:bg-myRed hover:text-white rounded-xl flex justify-center items-center transition-all border border-white/5">
                            <Facebook size={18} />
                        </a>
                    </motion.div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        className="md:col-span-2 space-y-6"
                    >
                        <p className="text-white/60 text-lg leading-relaxed max-w-md">
                            Apval is a cutting-edge platform designed to simplify and enhance digital romance through personalized assets and beautiful templates, engineered by Precious Adedokun.
                        </p>
                    </motion.div>

                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        className="flex flex-col gap-4"
                    >
                        <p className="font-bold text-xs uppercase tracking-[0.2em] text-white/40 mb-2">Project</p>
                        <Link className="text-white/60 hover:text-myRed transition-colors font-medium" href="/developer">About Me</Link>
                        <Link className="text-white/60 hover:text-myRed transition-colors font-medium" href="/templates">Templates</Link>
                        <Link className="text-white/60 hover:text-myRed transition-colors font-medium" href="/support">Support</Link>
                        <Link className="text-white/60 hover:text-myRed transition-colors font-medium" href="/faq">FAQs</Link>
                        <a className="text-white/60 hover:text-myRed transition-colors font-medium" href="https://preciousadedokun.com.ng/contact" target="_blank" rel="noopener noreferrer">Contact Me</a>
                    </motion.div>

                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        className="flex flex-col gap-4"
                    >
                        <p className="font-bold text-xs uppercase tracking-[0.2em] text-white/40 mb-2">Legal</p>
                        <Link className="text-white/60 hover:text-myRed transition-colors font-medium" href="/privacy">Privacy Policy</Link>
                        <Link className="text-white/60 hover:text-myRed transition-colors font-medium" href="/terms">Terms & Conditions</Link>
                    </motion.div>
                </div>

                <div className="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-8">
                    <motion.p
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        className="text-white/20 text-[10px] font-bold uppercase tracking-[0.3em] text-center md:text-left"
                    >
                        Apval is a digital lifestyle project. Spread love responsibly.
                    </motion.p>
                    <motion.p
                        initial={{ opacity: 0 }}
                        whileInView={{ opacity: 1 }}
                        className="text-white/20 text-[10px] font-bold uppercase tracking-[0.3em]"
                    >
                        © {new Date().getFullYear()} APVAL GLOBAL DELIVERY NETWORK.
                    </motion.p>
                </div>
            </div>
        </footer>
    );
}
