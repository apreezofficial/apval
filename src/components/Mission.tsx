'use client';
import { motion } from 'framer-motion';
import { Heart, Shield, Cpu, Zap } from 'lucide-react';
import { TypewriterText, TypewriterStepWrapper } from './TypewriterText';

export default function Mission() {
    return (
        <section className="py-24 px-6 bg-[#050505] overflow-hidden">
            <div className="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">

                {/* Visual Side (Mockup Infrastructure) */}
                <div className="relative group">
                    <div className="absolute -inset-4 bg-myRed/10 rounded-[40px] blur-3xl opacity-50 group-hover:opacity-100 transition-opacity" />
                    <div className="relative aspect-square rounded-[40px] border border-white/10 bg-white/[0.02] backdrop-blur-3xl overflow-hidden p-12 flex items-center justify-center">
                        <div className="relative">
                            <motion.div
                                animate={{ y: [0, -20, 0] }}
                                transition={{ duration: 4, repeat: Infinity }}
                                className="w-48 h-64 bg-myRed rounded-3xl shadow-2xl shadow-myRed/20 border-4 border-white flex flex-col items-center justify-center gap-6"
                            >
                                <Heart className="w-16 h-16 text-white fill-current" />
                                <div className="space-y-2 w-full px-8">
                                    <div className="h-1 bg-white/20 rounded-full" />
                                    <div className="h-1 bg-white/20 rounded-full w-2/3" />
                                </div>
                            </motion.div>
                            <div className="absolute -right-8 -bottom-8 w-32 h-32 bg-[#0A0A0A] border border-white/10 rounded-2xl p-6 flex items-center justify-center shadow-2xl">
                                <Cpu className="w-full h-full text-myRed" />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Content Side */}
                <div className="space-y-12">
                    <div className="space-y-6">
                        <TypewriterStepWrapper>
                            {(step, setStep) => (
                                <>
                                    <h2 className="text-5xl md:text-7xl font-medium tracking-tighter text-white leading-tight">
                                        Build trust and explain <br />
                                        <span className="text-myRed">
                                            <TypewriterText
                                                text="your mission"
                                                speed={0.06}
                                                onComplete={() => setStep(1)}
                                            />
                                        </span>
                                    </h2>
                                    <p className="text-xl text-white/40 font-medium leading-relaxed max-w-xl">
                                        <TypewriterText
                                            text="Our mission is to power love with infrastructure that's reliable, and built to scale—without the typical romantic complexity."
                                            trigger={step >= 1}
                                            speed={0.02}
                                        />
                                    </p>
                                </>
                            )}
                        </TypewriterStepWrapper>
                    </div>

                    <div className="bg-[#0A0A0A] border border-white/5 rounded-[32px] p-10 space-y-8 shadow-2xl">
                        <div className="w-16 h-16 bg-myRed rounded-2xl flex items-center justify-center shadow-lg">
                            <Shield className="text-white w-8 h-8" />
                        </div>

                        <div className="space-y-4">
                            <h3 className="text-2xl font-bold text-white tracking-tight">Reliable infrastructure for modern Valentine</h3>
                            <div className="space-y-3">
                                <div className="flex items-center gap-4 text-white/60 font-medium">
                                    <Zap className="w-5 h-5 text-myRed" />
                                    <span>Real-time Template Rendering</span>
                                </div>
                                <div className="flex items-center gap-4 text-white/60 font-medium">
                                    <Cpu className="w-5 h-5 text-myRed" />
                                    <span>Scalable Link Distribution</span>
                                </div>
                            </div>
                        </div>

                        <p className="text-white/30 text-sm leading-relaxed">
                            Built for the next generation of romance, our infrastructure combines high performance, seamless scalability, and trusted reliability to support everything from simple cards to cinematic experiences.
                        </p>
                    </div>
                </div>

            </div>
        </section>
    );
}
