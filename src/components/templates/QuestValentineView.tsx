'use client';
import { useState } from 'react';
import { motion } from 'framer-motion';
import { Heart, Volume2, VolumeX, MessageCircle } from 'lucide-react';
import MusicPlayer from '../MusicPlayer';

interface QuestValentineViewProps {
    data: any;
}

export default function QuestValentineView({ data }: QuestValentineViewProps) {
    const [noCount, setNoCount] = useState(0);
    const [yesPressed, setYesPressed] = useState(false);
    const [isMuted, setIsMuted] = useState(false);

    const yesButtonSize = Math.min(noCount * 25 + 18, 500); // Caps size at 500px

    const handleNoClick = () => {
        setNoCount(noCount + 1);
    };

    const getNoButtonText = () => {
        const phrases = [
            "No",
            "Are you sure?",
            "What if I asked really nicely?",
            "Pretty please",
            "With a cherry on top",
            "What about a chocolate crepe?",
            "PLEASE POOKIE",
            "But :(",
            "I'm actually crying",
            "Yep, I'm dead",
            "ok you're breaking my heart",
            "PLEASE PLEASE PLEASE",
            "Don't do this to me",
            "I'm crying now",
            "You're so mean!"
        ];
        return phrases[Math.min(noCount, phrases.length - 1)];
    };

    return (
        <>
            {data.musicUrl && <MusicPlayer url={data.musicUrl} isMuted={isMuted} />}

            {yesPressed ? (
                <div className="flex flex-col items-center justify-center min-h-screen bg-[#050505] text-center p-6 overflow-hidden relative font-geist w-full">
                    <div className="absolute inset-0 bg-[#FC4100]/10 blur-[120px] rounded-full scale-150 animate-pulse" />

                    <motion.div
                        initial={{ scale: 0, rotate: -20 }}
                        animate={{ scale: 1, rotate: 0 }}
                        transition={{ type: "spring", damping: 10, stiffness: 100 }}
                        className="relative z-10"
                    >
                        <div className="relative">
                            <motion.div
                                animate={{ scale: [1, 1.1, 1] }}
                                transition={{ duration: 2, repeat: Infinity }}
                                className="absolute -inset-4 bg-myRed/20 blur-2xl rounded-full"
                            />
                            <img
                                src="https://media.tenor.com/gU66uj338IAAAAAi/milk-and-mocha.gif"
                                alt="Bears Kissing"
                                className="w-72 h-72 mb-8 object-contain relative z-10"
                            />
                        </div>
                    </motion.div>

                    <motion.div
                        initial={{ y: 30, opacity: 0 }}
                        animate={{ y: 0, opacity: 1 }}
                        transition={{ delay: 0.3 }}
                        className="space-y-4 relative z-10"
                    >
                        <h1 className="text-5xl md:text-8xl font-black text-white tracking-tighter">
                            OK YAY!!! <span className="text-myRed">❤️</span>
                        </h1>
                        <p className="text-xl md:text-2xl text-white/60 font-medium tracking-tight">
                            {data.recipient}, you just made my entire world!
                        </p>

                        {data.whatsapp && (
                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.6 }}
                                className="pt-8"
                            >
                                <a
                                    href={`https://wa.me/${data.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(`Hey ${data.sender}! I just said YES to your beautiful Valentine request on Apval! ❤️❤️❤️`)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-3 px-8 py-4 bg-[#25D366] text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-[0_20px_40px_rgba(37,211,102,0.2)]"
                                >
                                    <MessageCircle className="w-5 h-5 fill-current" />
                                    <span>Tell {data.sender} on WhatsApp</span>
                                </a>
                            </motion.div>
                        )}
                    </motion.div>

                    {/* Celebration Particles */}
                    <div className="absolute inset-0 pointer-events-none">
                        {[...Array(7)].map((_, i) => (
                            <motion.div
                                key={i}
                                initial={{
                                    top: "100%",
                                    left: `${Math.random() * 100}%`,
                                    scale: Math.random() * 0.5 + 0.5,
                                    opacity: 0
                                }}
                                animate={{
                                    top: "-20%",
                                    opacity: [0, 1, 1, 0],
                                    rotate: 360,
                                    x: (Math.random() - 0.5) * 200
                                }}
                                transition={{
                                    duration: Math.random() * 4 + 3,
                                    repeat: Infinity,
                                    delay: Math.random() * 5
                                }}
                                className="absolute"
                            >
                                <Heart className="text-myRed fill-current w-10 h-10 shadow-[0_0_20px_rgba(252,65,0,0.5)]" />
                            </motion.div>
                        ))}
                    </div>
                </div>
            ) : (
                <div className="flex flex-col items-center justify-center min-h-screen bg-[#050505] text-center p-6 overflow-hidden relative font-geist w-full">
                    {/* Liquid Background */}
                    <div className="absolute inset-0 overflow-hidden pointer-events-none">
                        <div className="absolute top-1/4 -left-20 w-[400px] h-[400px] bg-myRed/10 blur-[100px] rounded-full animate-pulse" />
                        <div className="absolute bottom-1/4 -right-20 w-[400px] h-[400px] bg-blue-500/5 blur-[100px] rounded-full" />
                    </div>

                    <div className="relative z-10 max-w-2xl w-full flex flex-col items-center">
                        <motion.div
                            key={noCount}
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            className="mb-12"
                        >
                            <div className="relative group">
                                <div className="absolute -inset-4 bg-white/5 blur-xl rounded-full scale-0 group-hover:scale-100 transition-transform duration-500" />
                                <img
                                    src={noCount === 0
                                        ? "https://media.tenor.com/IC92C6pE88AAAAAi/be-my-valentine.gif"
                                        : "https://media.tenor.com/M6L2M1yW55EAAAAi/mochi-mochi-peach-cat-cat.gif"
                                    }
                                    alt="Reaction"
                                    className="w-64 h-64 md:w-80 md:h-80 object-contain relative"
                                />
                            </div>
                        </motion.div>

                        <h1 className="text-4xl md:text-7xl font-black text-white tracking-tighter leading-none mb-16 drop-shadow-2xl">
                            {data.headline && (
                                <span className="block text-xl md:text-3xl text-myRed/80 mb-6 uppercase tracking-[0.2em] font-medium">{data.headline}</span>
                            )}
                            {data.recipient}, <br />
                            will you be my <span className="text-myRed">Valentine?</span> 🥹❤️
                        </h1>

                        <div className="flex flex-col items-center justify-center gap-8 w-full max-w-sm">
                            <motion.button
                                onClick={() => setYesPressed(true)}
                                style={{ fontSize: yesButtonSize }}
                                whileHover={{ scale: 1.05 }}
                                whileTap={{ scale: 0.95 }}
                                className="w-full bg-[#1DB954] hover:bg-[#1ed760] text-white font-black py-6 px-10 rounded-[32px] shadow-[0_20px_40px_-10px_rgba(29,185,84,0.3)] transition-all flex items-center justify-center gap-4"
                            >
                                <span>YES</span>
                                <Heart className="fill-current w-6 h-6" />
                            </motion.button>

                            <motion.button
                                layout
                                onClick={handleNoClick}
                                whileHover={{ x: [0, -5, 5, -5, 5, 0] }}
                                className="text-white/40 hover:text-white/60 font-bold py-4 px-8 rounded-2xl border border-white/5 hover:bg-white/5 transition-all text-sm uppercase tracking-widest"
                            >
                                {getNoButtonText()}
                            </motion.button>
                        </div>

                        {data.sender && (
                            <div className="mt-20 flex items-center gap-3 px-6 py-3 bg-white/5 rounded-2xl border border-white/5 backdrop-blur-sm opacity-60">
                                <Heart className="w-4 h-4 text-myRed fill-current" />
                                <span className="text-sm font-bold tracking-tight text-white/60">DEPLOYED BY {data.sender.toUpperCase()}</span>
                            </div>
                        )}
                    </div>

                    {/* Music Hint/Control */}
                    {data.musicUrl && (
                        <button
                            onClick={() => setIsMuted(!isMuted)}
                            className="fixed top-10 right-10 z-50 w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center group hover:bg-white/10 transition-all backdrop-blur-xl"
                        >
                            {isMuted ? <VolumeX className="w-5 h-5 text-white/40" /> : <Volume2 className="w-5 h-5 text-white animate-pulse" />}
                        </button>
                    )}
                </div>
            )}
        </>
    );
}
