'use client';
import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, Volume2, VolumeX, MessageCircle, Gamepad2, Zap, Trophy, Star } from 'lucide-react';
import MusicPlayer from '../MusicPlayer';

interface GamingEliteViewProps {
    data: any;
    isPreview?: boolean;
}

export default function GamingEliteView({ data, isPreview }: GamingEliteViewProps) {
    const [noCount, setNoCount] = useState(0);
    const [yesPressed, setYesPressed] = useState(false);
    const [isMuted, setIsMuted] = useState(false);
    const [score, setScore] = useState(0);

    const yesButtonSize = Math.min(noCount * 30 + 20, 600);

    useEffect(() => {
        setScore(noCount * 100);
    }, [noCount]);

    const handleNoClick = () => {
        setNoCount(noCount + 1);
    };

    const getNoButtonText = () => {
        const phrases = [
            "DECLINE",
            "RETRY?",
            "CONTINUE?",
            "RESPAWN?",
            "CHECKPOINT?",
            "POWER UP?",
            "LEVEL UP?",
            "BOSS FIGHT?",
            "FINAL ROUND?",
            "GAME OVER?",
            "NEW GAME+?",
            "ACHIEVEMENT UNLOCKED?",
            "LEGENDARY MODE?",
            "ULTRA RARE?",
            "MYTHIC TIER?"
        ];
        return phrases[Math.min(noCount, phrases.length - 1)];
    };

    return (
        <div className="w-full h-full font-mono selection:bg-purple-500 selection:text-white bg-black">
            {data.musicUrl && <MusicPlayer url={data.musicUrl} isMuted={isMuted} />}

            {/* CRT Scanline Effect */}
            <div className="absolute inset-0 pointer-events-none z-[50] bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] bg-[size:100%_4px,6px_100%]" />
            <div className="absolute inset-0 pointer-events-none z-[50] mix-blend-overlay opacity-20 bg-gradient-to-br from-transparent via-purple-500/10 to-transparent" />

            {yesPressed ? (
                <div className={`flex flex-col items-center justify-center ${isPreview ? 'h-full' : 'min-h-screen'} bg-[#050008] text-center p-6 overflow-hidden relative w-full`}>
                    {/* Animated Grid Background */}
                    <div className="absolute inset-0 opacity-20 pointer-events-none bg-[linear-gradient(to_right,#8B5CF6_1px,transparent_1px),linear-gradient(to_bottom,#8B5CF6_1px,transparent_1px)] bg-[size:40px_40px] animate-pulse" />

                    {/* Glowing Orbs */}
                    <div className="absolute inset-0 overflow-hidden pointer-events-none">
                        <div className="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-purple-600/20 blur-[120px] rounded-full animate-pulse" />
                        <div className="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-cyan-500/20 blur-[120px] rounded-full animate-pulse" style={{ animationDelay: '1s' }} />
                    </div>

                    <motion.div
                        initial={{ scale: 0, rotate: -20 }}
                        animate={{ scale: 1, rotate: 0 }}
                        transition={{ type: "spring", damping: 12, stiffness: 200 }}
                        className="relative z-10"
                    >
                        {/* Victory Badge */}
                        <motion.div
                            animate={{ rotate: [0, 5, -5, 0] }}
                            transition={{ duration: 2, repeat: Infinity }}
                            className="relative mb-8"
                        >
                            <div className="w-64 h-64 md:w-80 md:h-80 relative flex items-center justify-center">
                                {/* Hexagon Badge */}
                                <div className="absolute inset-0 flex items-center justify-center">
                                    <svg viewBox="0 0 100 100" className="w-full h-full drop-shadow-[0_0_50px_rgba(139,92,246,0.6)]">
                                        <defs>
                                            <linearGradient id="badgeGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" stopColor="#7C3AED" />
                                                <stop offset="50%" stopColor="#DB2777" />
                                                <stop offset="100%" stopColor="#0891B2" />
                                            </linearGradient>
                                        </defs>
                                        <polygon
                                            points="50,5 95,25 95,75 50,95 5,75 5,25"
                                            fill="url(#badgeGradient)"
                                            stroke="#fff"
                                            strokeWidth="2"
                                            className="opacity-90"
                                        />
                                    </svg>
                                </div>

                                {/* Trophy Icon */}
                                <div className="absolute inset-0 flex items-center justify-center">
                                    <Trophy className="w-24 h-24 md:w-32 md:h-32 text-yellow-300 fill-current drop-shadow-[0_0_20px_rgba(253,224,71,0.8)]" />
                                </div>

                                {/* Floating Controllers */}
                                <motion.div
                                    animate={{ y: [0, -15, 0], rotate: [-10, -5, -10] }}
                                    transition={{ duration: 3, repeat: Infinity }}
                                    className="absolute -left-12 top-1/2 -translate-y-1/2"
                                >
                                    <Gamepad2 className="w-16 h-16 md:w-24 md:h-24 text-purple-400 drop-shadow-[0_0_25px_rgba(167,139,250,0.8)] stroke-[1.5]" />
                                </motion.div>
                                <motion.div
                                    animate={{ y: [0, -15, 0], rotate: [10, 5, 10] }}
                                    transition={{ duration: 3, repeat: Infinity, delay: 1.5 }}
                                    className="absolute -right-12 top-1/2 -translate-y-1/2"
                                >
                                    <Gamepad2 className="w-16 h-16 md:w-24 md:h-24 text-cyan-400 drop-shadow-[0_0_25px_rgba(34,211,238,0.8)] stroke-[1.5]" />
                                </motion.div>
                            </div>
                        </motion.div>
                    </motion.div>

                    <motion.div
                        initial={{ y: 30, opacity: 0 }}
                        animate={{ y: 0, opacity: 1 }}
                        transition={{ delay: 0.3 }}
                        className="space-y-6 relative z-10 w-full max-w-4xl"
                    >
                        {/* Achievement Banner */}
                        <motion.div
                            initial={{ x: -100, opacity: 0 }}
                            animate={{ x: 0, opacity: 1 }}
                            transition={{ delay: 0.5 }}
                            className="bg-black/40 border-l-4 border-r-4 border-purple-500 max-w-md mx-auto py-4 backdrop-blur-md relative overflow-hidden group"
                        >
                            <div className="absolute inset-0 bg-purple-500/10 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000" />
                            <div className="flex items-center gap-4 justify-center relative z-10">
                                <Star className="w-6 h-6 text-yellow-400 fill-current animate-[spin_3s_linear_infinite]" />
                                <div className="text-left">
                                    <p className="text-[10px] md:text-xs font-bold uppercase tracking-widest text-purple-400">System Notification</p>
                                    <p className="text-sm md:text-lg font-bold text-white tracking-wider">ACHIEVEMENT UNLOCKED</p>
                                </div>
                                <Star className="w-6 h-6 text-yellow-400 fill-current animate-[spin_3s_linear_infinite_reverse]" />
                            </div>
                        </motion.div>

                        <h1 className="text-5xl md:text-8xl font-black text-transparent bg-clip-text bg-gradient-to-b from-purple-300 to-purple-600 tracking-tighter leading-none drop-shadow-[0_5px_0_rgba(88,28,135,1)]">
                            VICTORY!
                        </h1>

                        <p className="text-xl md:text-3xl text-white font-bold tracking-tight px-4 shadow-black drop-shadow-md">
                            <span className="text-cyan-400">&gt;</span> {data.recipient} just joined the party!
                        </p>

                        <div className="flex items-center justify-center gap-4 md:gap-12 text-white/80 font-mono text-xs md:text-sm pt-4">
                            <div className="flex items-center gap-2 bg-white/5 px-4 py-2 rounded-lg border border-white/10">
                                <Zap className="w-4 h-4 text-yellow-400" />
                                <span>SCORE: <span className="text-yellow-400">{score + 99999}</span></span>
                            </div>
                            <div className="flex items-center gap-2 bg-white/5 px-4 py-2 rounded-lg border border-white/10">
                                <Heart className="w-4 h-4 text-pink-500 fill-current" />
                                <span>HP: <span className="text-green-400">100%</span></span>
                            </div>
                        </div>

                        {data.whatsapp && (
                            <motion.div
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ delay: 1 }}
                                className="pt-8"
                            >
                                <a
                                    href={`https://wa.me/${data.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(`GG WP ${data.sender}! 🎮 I ACCEPT the challenge! Let's go! 💜`)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-3 px-8 py-4 bg-green-600 hover:bg-green-500 text-white border-b-4 border-green-800 active:border-b-0 active:translate-y-1 transition-all uppercase font-bold tracking-wider text-sm md:text-base shadow-[0_0_20px_rgba(34,197,94,0.4)]"
                                >
                                    <MessageCircle className="w-5 h-5 fill-current" />
                                    <span>Press Start (WhatsApp)</span>
                                </a>
                            </motion.div>
                        )}
                    </motion.div>

                    {/* Celebration Particles */}
                    <div className="absolute inset-0 pointer-events-none overflow-hidden">
                        {[...Array(40)].map((_, i) => (
                            <motion.div
                                key={i}
                                initial={{ top: "110%", left: `${Math.random() * 100}%`, scale: 0, opacity: 0 }}
                                animate={{ top: "-10%", opacity: [0, 1, 1, 0], scale: [0, 1, 1], rotate: [0, 180, 360], x: (Math.random() - 0.5) * 200 }}
                                transition={{ duration: Math.random() * 3 + 2, repeat: Infinity, delay: Math.random() * 2, ease: "linear" }}
                                className="absolute"
                            >
                                {i % 3 === 0 ? <Heart className="text-pink-500/80 fill-current w-6 h-6" /> :
                                    i % 3 === 1 ? <Star className="text-yellow-400/80 fill-current w-6 h-6" /> :
                                        <div className="w-3 h-3 bg-cyan-400 shadow-[0_0_10px_cyan]" />}
                            </motion.div>
                        ))}
                    </div>
                </div>
            ) : (
                <div className={`flex flex-col items-center justify-center ${isPreview ? 'h-full' : 'min-h-screen'} bg-[#050008] text-center p-6 overflow-hidden relative w-full`}>
                    {/* Animated Grid Background */}
                    <div className="absolute inset-0 opacity-15 pointer-events-none bg-[linear-gradient(to_right,#4C1D95_1px,transparent_1px),linear-gradient(to_bottom,#4C1D95_1px,transparent_1px)] bg-[size:50px_50px]" />

                    {/* Glowing Orbs */}
                    <div className="absolute inset-0 overflow-hidden pointer-events-none">
                        <div className="absolute top-1/4 -left-20 w-[400px] h-[400px] bg-purple-900/40 blur-[100px] rounded-full animate-pulse" />
                        <div className="absolute bottom-1/4 -right-20 w-[400px] h-[400px] bg-cyan-900/40 blur-[100px] rounded-full animate-pulse" style={{ animationDelay: '1.5s' }} />
                    </div>

                    <div className="relative z-10 max-w-4xl w-full flex flex-col items-center">
                        {/* Score Display HUD */}
                        <div className="absolute top-0 left-0 right-0 p-4 flex justify-between items-start font-bold text-xs md:text-sm tracking-widest text-purple-300/60 uppercase pointer-events-none">
                            <div className="flex gap-4">
                                <span>P1: {data.sender || 'YOU'}</span>
                                <span>P2: CPU</span>
                            </div>
                            <div className="animate-pulse text-red-500">INSERT COIN</div>
                            <div>CREDITS: 00</div>
                        </div>

                        {/* Main Content */}
                        <motion.div
                            initial={{ y: 20, opacity: 0 }}
                            animate={{ y: 0, opacity: 1 }}
                            className="flex flex-col items-center gap-8 mt-12 mb-12"
                        >
                            <div className="relative group">
                                <div className="absolute -inset-8 bg-purple-600/20 blur-2xl rounded-full group-hover:bg-purple-600/30 transition-all duration-500" />
                                <motion.div
                                    animate={{
                                        y: [0, -10, 0],
                                        rotateZ: [0, -2, 2, 0]
                                    }}
                                    transition={{ duration: 4, repeat: Infinity, ease: "easeInOut" }}
                                    className="relative"
                                >
                                    <Gamepad2 className="w-48 h-48 md:w-64 md:h-64 text-purple-500 drop-shadow-[0_0_30px_rgba(168,85,247,0.6)] stroke-[1] fill-black/50" />
                                    <div className="absolute inset-0 flex items-center justify-center">
                                        <Heart className="w-16 h-16 text-pink-500 fill-current animate-pulse drop-shadow-[0_0_20px_rgba(236,72,153,0.8)]" />
                                    </div>
                                </motion.div>
                            </div>

                            <div className="space-y-4 max-w-2xl px-4">
                                <h1 className="text-4xl md:text-7xl font-black text-white tracking-tighter leading-tight drop-shadow-2xl">
                                    {data.headline ? (
                                        <span className="block text-2xl md:text-4xl text-cyan-400 mb-2 font-bold tracking-widest uppercase drop-shadow-[0_0_10px_rgba(34,211,238,0.5)]">
                                            {data.headline}
                                        </span>
                                    ) : (
                                        <span className="block text-2xl md:text-4xl text-cyan-400 mb-2 font-bold tracking-widest uppercase drop-shadow-[0_0_10px_rgba(34,211,238,0.5)]">
                                            PLAYER 2 READY?
                                        </span>
                                    )}
                                    <span className="bg-clip-text text-transparent bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 animate-gradient-x">
                                        {data.recipient}
                                    </span>
                                </h1>
                                <p className="text-lg md:text-xl text-purple-200/80 font-medium">
                                    {data.message || "Ready for a co-op adventure? Press Start to join."}
                                </p>
                            </div>
                        </motion.div>

                        {/* Action Buttons */}
                        <div className="flex flex-col items-center justify-center gap-6 w-full max-w-xs md:max-w-md relative z-20">
                            <motion.button
                                onClick={() => setYesPressed(true)}
                                whileHover={{ scale: 1.05 }}
                                whileTap={{ scale: 0.95 }}
                                className="w-full bg-green-500 hover:bg-green-400 text-black font-black text-xl md:text-2xl py-6 px-12 border-b-8 border-green-700 active:border-b-0 active:translate-y-2 rounded-lg transition-all shadow-[0_0_30px_rgba(34,197,94,0.3)] flex items-center justify-center gap-3 uppercase tracking-wider group"
                            >
                                <Zap className="w-6 h-6 fill-current group-hover:animate-ping" />
                                <span>START GAME</span>
                                <Zap className="w-6 h-6 fill-current group-hover:animate-ping" />
                            </motion.button>

                            <motion.button
                                layout
                                onClick={handleNoClick}
                                whileHover={{ x: [0, -4, 4, -4, 4, 0] }}
                                className="text-white/50 hover:text-red-400 font-bold tracking-widest text-sm md:text-base py-4 hover:bg-white/5 w-full rounded transition-colors uppercase"
                            >
                                {getNoButtonText()}
                            </motion.button>
                        </div>
                    </div>

                    {/* Mute Control */}
                    {data.musicUrl && (
                        <button
                            onClick={() => setIsMuted(!isMuted)}
                            className="fixed top-6 right-6 z-50 p-3 rounded-lg bg-black/50 border border-white/10 hover:bg-white/10 transition-colors backdrop-blur-md text-white/60 hover:text-white"
                        >
                            {isMuted ? <VolumeX className="w-6 h-6" /> : <Volume2 className="w-6 h-6 animate-pulse" />}
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
