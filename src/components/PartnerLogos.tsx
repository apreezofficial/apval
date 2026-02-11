'use client';
import { motion } from 'framer-motion';

const partners = [
    { name: 'Proforms', icon: '◈' },
    { name: 'Zendo', icon: '◎' },
    { name: 'Brevpulse', icon: '⚡' },
    { name: 'APCodeSphere', icon: '⚙' },
    { name: 'Pxxl', icon: '◆' },
    { name: 'Paystack', icon: 'P' },
    { name: 'Moniepoint', icon: 'M' },
    { name: 'PiggyVest', icon: 'PV' },
    { name: 'Kuda', icon: 'K' }
];

export default function PartnerLogos() {
    return (
        <div className="w-full py-16 border-y border-white/5 bg-[#050505] overflow-hidden">
            <div className="max-w-7xl mx-auto px-6 mb-10">
                <p className="text-center text-[10px] font-black uppercase tracking-[0.4em] text-white/20">
                    Trusted Architecture Partners
                </p>
            </div>

            <div className="relative flex overflow-x-hidden group">
                <div className="animate-marquee flex items-center whitespace-nowrap">
                    {[...partners, ...partners].map((partner, i) => (
                        <div
                            key={i}
                            className="flex items-center gap-3 px-10 grayscale opacity-30 hover:grayscale-0 hover:opacity-100 transition-all duration-500 cursor-default"
                        >
                            <span className="text-xl font-bold text-white">{partner.icon}</span>
                            <span className="text-lg font-black tracking-tighter text-white uppercase italic">{partner.name}</span>
                        </div>
                    ))}
                </div>
            </div>

            <style jsx>{`
                @keyframes marquee {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .animate-marquee {
                    animation: marquee 30s linear infinite;
                }
            `}</style>
        </div>
    );
}
