'use client';

import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, MessageCircle, Sparkles } from 'lucide-react';
import confetti from 'canvas-confetti';

interface ClassicValentineViewProps {
    data: any;
    isPreview?: boolean;
}

export default function ClassicValentineView({ data, isPreview }: ClassicValentineViewProps) {
    const [noCount, setNoCount] = useState(0);
    const [yesPressed, setYesPressed] = useState(false);

    const noPhrases = [
        "No",
        "Are you sure?",
        "What if I ask really nicely?",
        "Pretty please?",
        "With a chocolate on top?",
        "What about a puppy?",
        "PLEASE POOKIE",
        "But :(",
        "I am going to cry...",
        "You're breaking my heart ;(",
    ];

    const getNoButtonText = () => noPhrases[Math.min(noCount, noPhrases.length - 1)];

    const handleYes = () => {
        setYesPressed(true);
        confetti({
            particleCount: 150,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#ff4d8d', '#fc4100', '#ffffff']
        });
    };

    const handleNo = () => {
        setNoCount(noCount + 1);
    };

    const yesButtonSize = noCount * 20 + 16;

    if (yesPressed) {
        return (
            <div className={`w-full h-full ${isPreview ? 'relative' : 'fixed inset-0'} bg-[#FFB6C1] flex flex-col items-center justify-center p-6 text-center select-none`}>
                <motion.img
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    src="https://media.tenor.com/gU8vX7S1pYAAAAAC/bear-kiss-bear-kisses.gif"
                    alt="Bear Kiss"
                    className="w-64 h-64 rounded-3xl shadow-2xl mb-8 object-cover"
                />
                <motion.div
                    initial={{ y: 20, opacity: 0 }}
                    animate={{ y: 0, opacity: 1 }}
                    className="space-y-4"
                >
                    <h1 className="text-5xl font-black text-white italic drop-shadow-lg">
                        Yay!!! Best Day Ever! ❤️
                    </h1>
                    <p className="text-white/80 font-bold text-xl uppercase tracking-widest">
                        I love you so much {data.recipient}!
                    </p>
                </motion.div>

                {data.whatsapp && (
                    <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.5 }}
                        className="mt-12"
                    >
                        <a
                            href={`https://wa.me/${data.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(`I SAID YES! ❤️ Best Valentine ever!`)}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-3 px-8 py-4 bg-white text-[#ff4d8d] rounded-2xl font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-2xl"
                        >
                            <MessageCircle className="w-5 h-5 fill-current" />
                            <span>Tell {data.sender}!</span>
                        </a>
                    </motion.div>
                )}
            </div>
        );
    }

    return (
        <div className={`w-full h-full ${isPreview ? 'relative' : 'fixed inset-0'} bg-[#FFB6C1] flex flex-col items-center justify-center p-6 text-center select-none`}>
            <motion.div
                key={noCount}
                initial={{ scale: 0.8, opacity: 0 }}
                animate={{ scale: 1, opacity: 1 }}
                className="mb-12"
            >
                <img
                    src={noCount === 0 ? "https://media1.tenor.com/m/al4a1pG1fScAAAAC/jump-bear.gif" : "https://media.tenor.com/9GToI-57g6EAAAAi/milk-and-mocha.gif"}
                    alt="Cute Bear"
                    className="w-64 h-64 rounded-3xl shadow-2xl object-cover border-8 border-white"
                />
            </motion.div>

            <motion.div
                initial={{ y: 20, opacity: 0 }}
                animate={{ y: 0, opacity: 1 }}
                className="space-y-12 w-full max-w-md"
            >
                <h1 className="text-4xl md:text-6xl font-black text-white italic drop-shadow-md leading-tight">
                    ♡ Will you be my Valentine, {data.recipient}? ♡
                </h1>

                <div className="flex flex-col items-center gap-6">
                    <button
                        onClick={handleYes}
                        style={{ fontSize: yesButtonSize }}
                        className="bg-green-500 text-white font-black py-4 px-10 rounded-2xl shadow-[0_15px_30px_rgba(34,197,94,0.4)] transition-all hover:scale-110 active:scale-95 z-50 whitespace-nowrap"
                    >
                        Yes
                    </button>
                    <button
                        onClick={handleNo}
                        className="bg-white/20 backdrop-blur-md text-white font-bold py-4 px-10 rounded-2xl border border-white/40 hover:bg-white/30 transition-all text-sm uppercase tracking-widest whitespace-nowrap"
                    >
                        {getNoButtonText()}
                    </button>
                </div>
            </motion.div>

            {/* Floating Hearts Background */}
            <div className="absolute inset-0 pointer-events-none overflow-hidden opacity-30">
                {[...Array(7)].map((_, i) => (
                    <motion.div
                        key={i}
                        animate={{
                            y: ['110vh', '-10vh'],
                            x: [`${Math.random() * 100}vw`, `${Math.random() * 100}vw`],
                            rotate: [0, 360],
                            scale: [0.5, 1.2, 0.8]
                        }}
                        transition={{
                            duration: 7 + Math.random() * 5,
                            repeat: Infinity,
                            delay: Math.random() * 5,
                            ease: "linear"
                        }}
                        className="absolute text-3xl"
                    >
                        ❤️
                    </motion.div>
                ))}
            </div>
        </div>
    );
}
