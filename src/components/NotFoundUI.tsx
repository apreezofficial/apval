'use client';
import { motion } from 'framer-motion';
import { Heart, Home, ArrowRight, ShieldAlert } from 'lucide-react';
import Link from 'next/link';

export default function NotFoundUI({ message = "might have been deleted or link broken", path = "" }: { message?: string, path?: string }) {
    return (
        <main className="min-h-screen bg-[#050505] text-white flex flex-col items-center justify-center p-6 relative overflow-hidden">
            {/* Background elements */}
            <div className="absolute inset-0 opacity-[0.03] pointer-events-none bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:40px_40px]" />
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[60vw] h-[60vw] bg-myRed/10 rounded-full blur-[150px] pointer-events-none" />

            <motion.div
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                className="text-center space-y-8 relative z-10"
            >
                <div className="flex justify-center">
                    <div className="relative">
                        <motion.div
                            animate={{ scale: [1, 1.1, 1], rotate: [0, 5, -5, 0] }}
                            transition={{ duration: 4, repeat: Infinity }}
                            className="w-24 h-24 bg-myRed/20 rounded-[32px] flex items-center justify-center border border-myRed/30"
                        >
                            <ShieldAlert className="w-12 h-12 text-myRed" />
                        </motion.div>
                        <div className="absolute -top-2 -right-2 w-8 h-8 bg-white/5 backdrop-blur-xl border border-white/10 rounded-full flex items-center justify-center">
                            <Heart className="w-4 h-4 text-myRed fill-current" />
                        </div>
                    </div>
                </div>

                <div className="space-y-4">
                    <h1 className="text-5xl md:text-7xl font-bold tracking-tight">
                        Oops. <span className="text-myRed">Not Found</span>
                    </h1>
                    <p className="text-white/40 text-lg md:text-xl max-w-md mx-auto font-medium">
                        The asset you are looking for {message}.
                    </p>
                </div>

                {path && (
                    <div className="inline-flex items-center gap-2 px-6 py-3 bg-white/5 border border-white/10 rounded-2xl">
                        <span className="text-[10px] font-bold uppercase tracking-widest text-white/20">Accessed:</span>
                        <code className="text-sm font-mono text-white/60">{path}</code>
                    </div>
                )}

                <div className="flex flex-col md:flex-row items-center justify-center gap-4 pt-8">
                    <Link
                        href="/"
                        className="w-full md:w-auto px-10 py-5 bg-white/5 border border-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all flex items-center justify-center gap-3"
                    >
                        <Home className="w-5 h-5" />
                        <span>Return Home</span>
                    </Link>
                    <Link
                        href="/templates"
                        className="w-full md:w-auto px-10 py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 shadow-[0_10px_30px_rgba(252,65,0,0.2)] group"
                    >
                        {/* Vercel-like logo (Triangle) */}
                        <svg className="w-5 h-5 fill-current" viewBox="0 0 76 65" xmlns="http://www.w3.org/2000/svg">
                            <path d="M37.5274 0L75.0548 65H0L37.5274 0Z" />
                        </svg>
                        <span>Deploy Your New One</span>
                        <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                    </Link>
                </div>
            </motion.div>
        </main>
    );
}
