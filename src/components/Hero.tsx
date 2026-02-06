'use client';
import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Heart, ArrowUpRight, Cpu } from 'lucide-react';
import HeroPreview from './HeroPreview';
import { TypewriterText } from './TypewriterText';

export default function Hero() {
    const [step, setStep] = React.useState(0);

    return (
        <section className="relative min-h-screen flex items-center pt-20 px-6 md:px-24 overflow-hidden bg-[#050505]">
            {/* Background Grid */}
            <div className="absolute inset-0 opacity-[0.05] pointer-events-none bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:60px_60px]" />

            {/* Glow Effects */}
            <div className="absolute top-[20%] right-[10%] w-[40vw] h-[40vw] bg-myRed/10 rounded-full blur-[150px] pointer-events-none" />

            <div className="relative z-10 w-full max-w-7xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-12">

                {/* Left Side: Content (RESTORED) */}
                <div className="flex-1 space-y-12 text-left">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        className="space-y-4"
                    >
                        <h1 className="text-6xl md:text-8xl font-black tracking-tighter text-white leading-none">
                            Apval.
                        </h1>
                        <div className="flex flex-col gap-2 text-2xl md:text-3xl font-medium tracking-tight text-white/40 uppercase">
                            <div>
                                <TypewriterText
                                    text="Valentine Experiences"
                                    speed={0.04}
                                    onComplete={() => setStep(2)}
                                />
                            </div>
                            <div>
                                <TypewriterText
                                    text="Ready to Share in Seconds"
                                    trigger={step >= 2}
                                    speed={0.04}
                                />
                            </div>
                        </div>
                    </motion.div>

                    <motion.p
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 0.2 }}
                        className="max-w-xl text-xl md:text-2xl text-white/60 font-medium leading-relaxed tracking-tight"
                    >
                        Create beautiful Valentine pages from ready-made templates and share them instantly with a single link.
                    </motion.p>

                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 0.4 }}
                        className="flex items-center gap-10"
                    >
                        <button className="group relative px-8 py-4 bg-myRed text-white font-medium rounded-full hover:bg-myRed/90 transition-all flex items-center gap-3 shadow-[0_0_30px_rgba(252,65,0,0.3)]">
                            <span className="relative z-10 border-r border-white/20 pr-3">Claim Love</span>
                            <Heart className="w-5 h-5 fill-current" />
                        </button>

                        <a href="#templates" className="group flex items-center gap-2 text-white/60 hover:text-white transition-colors font-medium border-b border-white/20 pb-1">
                            <span>Deploy Your Heart</span>
                            <ArrowUpRight className="w-4 h-4" />
                        </a>
                    </motion.div>
                </div>

                {/* Right Side: Keep the new Simulation */}
                <div className="relative flex-1 w-full max-w-sm aspect-[9/19] lg:block hidden">
                    <motion.div
                        initial={{ opacity: 0, x: 100, rotate: 10 }}
                        animate={{ opacity: 1, x: 0, rotate: 0 }}
                        transition={{ duration: 1.5, ease: [0.16, 1, 0.3, 1] }}
                        className="w-full h-full"
                    >
                        <HeroPreview />
                    </motion.div>
                </div>
            </div>
        </section>
    );
}
