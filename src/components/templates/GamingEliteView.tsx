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
        <div className="w-full h-full">
            {data.musicUrl && <MusicPlayer url={data.musicUrl} isMuted={isMuted} />}

            {yesPressed ? (
                <div className="flex flex-col items-center justify-center min-h-screen bg-[#0A0014] text-center p-6 overflow-hidden relative font-geist w-full">
                    {/* Animated Grid Background */}
                    <div className="absolute inset-0 opacity-20 pointer-events-none bg-[linear-gradient(to_right,#8B5CF6_1px,transparent_1px),linear-gradient(to_bottom,#8B5CF6_1px,transparent_1px)] bg-[size:40px_40px] animate-pulse" />

                    {/* Glowing Orbs */}
                    <div className="absolute inset-0 overflow-hidden pointer-events-none">
                        <div className="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-purple-600/30 blur-[120px] rounded-full animate-pulse" />
                        <div className="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-cyan-500/30 blur-[120px] rounded-full animate-pulse" style={{ animationDelay: '1s' }} />
                        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-pink-500/20 blur-[150px] rounded-full animate-pulse" style={{ animationDelay: '2s' }} />
                    </div>

                    <motion.div
                        initial={{ scale: 0, rotate: -20 }}
                        animate={{ scale: 1, rotate: 0 }}
                        transition={{ type: "spring", damping: 10, stiffness: 100 }}
                        className="relative z-10"
                    >
                        {/* Victory Badge */}
                        <motion.div
                            animate={{ rotate: [0, 5, -5, 0] }}
                            transition={{ duration: 2, repeat: Infinity }}
                            className="relative mb-8"
                        >
                            <div className="w-80 h-80 relative">
                                {/* Hexagon Badge */}
                                <div className="absolute inset-0 flex items-center justify-center">
                                    <svg viewBox="0 0 100 100" className="w-full h-full">
                                        <defs>
                                            <linearGradient id="badgeGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" stopColor="#8B5CF6" />
                                                <stop offset="50%" stopColor="#EC4899" />
                                                <stop offset="100%" stopColor="#06B6D4" />
                                            </linearGradient>
                                        </defs>
                                        <polygon
                                            points="50,5 90,25 90,75 50,95 10,75 10,25"
                                            fill="url(#badgeGradient)"
                                            stroke="#fff"
                                            strokeWidth="2"
                                            className="drop-shadow-[0_0_30px_rgba(139,92,246,0.8)]"
                                        />
                                    </svg>
                                </div>

                                {/* Trophy Icon */}
                                <div className="absolute inset-0 flex items-center justify-center">
                                    <Trophy className="w-32 h-32 text-yellow-300 fill-current drop-shadow-[0_0_20px_rgba(253,224,71,0.8)]" />
                                </div>

                                {/* Gamepad Controllers */}
                                <motion.div
                                    animate={{ y: [0, -10, 0] }}
                                    transition={{ duration: 2, repeat: Infinity }}
                                    className="absolute -left-8 top-1/2 -translate-y-1/2"
                                >
                                    <Gamepad2 className="w-20 h-20 text-purple-400 drop-shadow-[0_0_15px_rgba(167,139,250,0.8)]" />
                                </motion.div>
                                <motion.div
                                    animate={{ y: [0, -10, 0] }}
                                    transition={{ duration: 2, repeat: Infinity, delay: 1 }}
                                    className="absolute -right-8 top-1/2 -translate-y-1/2"
                                >
                                    <Gamepad2 className="w-20 h-20 text-cyan-400 drop-shadow-[0_0_15px_rgba(34,211,238,0.8)]" />
                                </motion.div>
                            </div>
                        </motion.div>
                    </motion.div>

                    <motion.div
                        initial={{ y: 30, opacity: 0 }}
                        animate={{ y: 0, opacity: 1 }}
                        transition={{ delay: 0.3 }}
                        className="space-y-6 relative z-10"
                    >
                        {/* Achievement Unlocked Banner */}
                        <motion.div
                            initial={{ x: -100, opacity: 0 }}
                            animate={{ x: 0, opacity: 1 }}
                            transition={{ delay: 0.5 }}
                            className="bg-gradient-to-r from-purple-600/20 via-pink-600/20 to-cyan-600/20 border-2 border-purple-500/50 rounded-2xl px-8 py-4 backdrop-blur-xl"
                        >
                            <div className="flex items-center gap-4 justify-center">
                                <Star className="w-8 h-8 text-yellow-300 fill-current animate-pulse" />
                                <div className="text-left">
                                    <p className="text-xs font-black uppercase tracking-widest text-purple-300">Achievement Unlocked</p>
                                    <p className="text-lg font-black text-white">LEGENDARY VALENTINE</p>
                                </div>
                                <Star className="w-8 h-8 text-yellow-300 fill-current animate-pulse" />
                            </div>
                        </motion.div>

                        <h1 className="text-6xl md:text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-400 to-cyan-400 tracking-tighter leading-none drop-shadow-[0_0_30px_rgba(139,92,246,0.5)]">
                            VICTORY!
                        </h1>

                        <p className="text-2xl md:text-4xl text-white/90 font-bold tracking-tight">
                            {data.recipient}, you just won my heart! 💜
                        </p>

                        <div className="flex items-center justify-center gap-6 text-white/60 font-mono text-sm">
                            <div className="flex items-center gap-2">
                                <Zap className="w-5 h-5 text-yellow-400" />
                                <span>SCORE: {score + 10000}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <Heart className="w-5 h-5 text-pink-400 fill-current" />
                                <span>LOVE LEVEL: MAX</span>
                            </div>
                        </div>

                        {data.whatsapp && (
                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.8 }}
                                className="pt-8"
                            >
                                <a
                                    href={`https://wa.me/${data.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(`GG ${data.sender}! I just accepted your legendary Valentine request! 💜🎮`)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-[0_20px_40px_rgba(139,92,246,0.4)] border-2 border-purple-400/50"
                                >
                                    <MessageCircle className="w-6 h-6 fill-current" />
                                    <span>Message {data.sender} on WhatsApp</span>
                                </a>
                            </motion.div>
                        )}
                    </motion.div>

                    {/* Celebration Particles */}
                    <div className="absolute inset-0 pointer-events-none overflow-hidden">
                        {[...Array(40)].map((_, i) => (
                            <motion.div
                                key={i}
                                initial={{
                                    top: "100%",
                                    left: `${Math.random() * 100}%`,
                                    scale: Math.random() * 0.8 + 0.4,
                                    opacity: 0
                                }}
                                animate={{
                                    top: "-20%",
                                    opacity: [0, 1, 1, 0],
                                    rotate: 360,
                                    x: (Math.random() - 0.5) * 300
                                }}
                                transition={{
                                    duration: Math.random() * 5 + 4,
                                    repeat: Infinity,
                                    delay: Math.random() * 3
                                }}
                                className="absolute"
                            >
                                {i % 3 === 0 ? (
                                    <Heart className="text-pink-500 fill-current w-8 h-8 drop-shadow-[0_0_10px_rgba(236,72,153,0.8)]" />
                                ) : i % 3 === 1 ? (
                                    <Star className="text-yellow-400 fill-current w-8 h-8 drop-shadow-[0_0_10px_rgba(250,204,21,0.8)]" />
                                ) : (
                                    <Gamepad2 className="text-purple-400 w-8 h-8 drop-shadow-[0_0_10px_rgba(167,139,250,0.8)]" />
                                )}
                            </motion.div>
                        ))}
                    </div>
                </div>
            ) : (
                <div className="flex flex-col items-center justify-center min-h-screen bg-[#0A0014] text-center p-6 overflow-hidden relative font-geist w-full">
                    {/* Animated Grid Background */}
                    <div className="absolute inset-0 opacity-10 pointer-events-none bg-[linear-gradient(to_right,#8B5CF6_1px,transparent_1px),linear-gradient(to_bottom,#8B5CF6_1px,transparent_1px)] bg-[size:40px_40px]" />

                    {/* Glowing Orbs */}
                    <div className="absolute inset-0 overflow-hidden pointer-events-none">
                        <div className="absolute top-1/4 -left-20 w-[400px] h-[400px] bg-purple-600/20 blur-[100px] rounded-full animate-pulse" />
                        <div className="absolute bottom-1/4 -right-20 w-[400px] h-[400px] bg-cyan-500/20 blur-[100px] rounded-full animate-pulse" style={{ animationDelay: '1s' }} />
                    </div>

                    <div className="relative z-10 max-w-3xl w-full flex flex-col items-center">
                        {/* Score Display */}
                        <motion.div
                            initial={{ y: -50, opacity: 0 }}
                            animate={{ y: 0, opacity: 1 }}
                            className="absolute top-0 left-1/2 -translate-x-1/2 bg-gradient-to-r from-purple-600/30 to-pink-600/30 backdrop-blur-xl border border-purple-500/30 rounded-2xl px-8 py-4 font-mono"
                        >
                            <div className="flex items-center gap-4">
                                <Zap className="w-6 h-6 text-yellow-400 animate-pulse" />
                                <span className="text-2xl font-black text-white">SCORE: {score}</span>
                            </div>
                        </motion.div>

                        {/* Gaming Controller Animation */}
                        <motion.div
                            key={noCount}
                            initial={{ scale: 0.8, opacity: 0, y: 30 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            className="mb-16 mt-24"
                        >
                            <div className="relative group">
                                {/* Glow Effect */}
                                <motion.div
                                    animate={{
                                        scale: [1, 1.2, 1],
                                        opacity: [0.3, 0.6, 0.3]
                                    }}
                                    transition={{ duration: 2, repeat: Infinity }}
                                    className="absolute -inset-8 bg-gradient-to-r from-purple-600 to-pink-600 blur-3xl rounded-full"
                                />

                                {/* Controller Display */}
                                <div className="relative w-80 h-80 flex items-center justify-center">
                                    {/* Main Gamepad */}
                                    <motion.div
                                        animate={{
                                            rotate: [0, -5, 5, 0],
                                            y: [0, -10, 0]
                                        }}
                                        transition={{ duration: 3, repeat: Infinity }}
                                        className="relative"
                                    >
                                        <Gamepad2 className="w-64 h-64 text-purple-400 drop-shadow-[0_0_40px_rgba(167,139,250,0.8)]" />

                                        {/* Heart in center */}
                                        <motion.div
                                            animate={{
                                                scale: [1, 1.3, 1],
                                            }}
                                            transition={{ duration: 1.5, repeat: Infinity }}
                                            className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
                                        >
                                            <Heart className="w-20 h-20 text-pink-500 fill-current drop-shadow-[0_0_30px_rgba(236,72,153,1)]" />
                                        </motion.div>
                                    </motion.div>
                                </div>
                            </div>
                        </motion.div>

                        <h1 className="text-5xl md:text-8xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-400 to-cyan-400 tracking-tighter leading-none mb-8 drop-shadow-[0_0_30px_rgba(139,92,246,0.5)]">
                            {data.recipient}, <br />
                            PLAYER 2 <span className="text-pink-400">READY?</span> 🎮💜
                        </h1>

                        <p className="text-xl md:text-2xl text-white/70 font-medium mb-16 max-w-2xl">
                            {data.message || "Let's team up for the ultimate co-op adventure... Will you be my Valentine?"}
                        </p>

                        <div className="flex flex-col items-center justify-center gap-8 w-full max-w-md">
                            <motion.button
                                onClick={() => setYesPressed(true)}
                                style={{ fontSize: yesButtonSize > 100 ? '3rem' : '1.5rem' }}
                                whileHover={{ scale: 1.05 }}
                                whileTap={{ scale: 0.95 }}
                                className="w-full bg-gradient-to-r from-purple-600 via-pink-600 to-cyan-600 hover:from-purple-500 hover:via-pink-500 hover:to-cyan-500 text-white font-black py-8 px-12 rounded-[32px] shadow-[0_20px_60px_-10px_rgba(139,92,246,0.5)] transition-all flex items-center justify-center gap-4 border-2 border-purple-400/50 relative overflow-hidden group"
                            >
                                <span className="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-200%] group-hover:translate-x-[200%] transition-transform duration-1000" />
                                <span className="relative">ACCEPT</span>
                                <Heart className="fill-current w-8 h-8 relative" />
                            </motion.button>

                            <motion.button
                                layout
                                onClick={handleNoClick}
                                whileHover={{ x: [0, -8, 8, -8, 8, 0] }}
                                className="text-white/50 hover:text-white/70 font-bold py-5 px-10 rounded-2xl border-2 border-white/10 hover:bg-white/5 hover:border-purple-500/30 transition-all text-sm uppercase tracking-widest backdrop-blur-sm"
                            >
                                {getNoButtonText()}
                            </motion.button>
                        </div>

                        {data.sender && (
                            <div className="mt-24 flex items-center gap-4 px-8 py-4 bg-white/5 rounded-2xl border border-white/10 backdrop-blur-sm">
                                <Gamepad2 className="w-5 h-5 text-purple-400" />
                                <span className="text-sm font-bold tracking-tight text-white/60">DEPLOYED BY {data.sender.toUpperCase()}</span>
                            </div>
                        )}
                    </div>

                    {/* Music Control */}
                    {data.musicUrl && (
                        <button
                            onClick={() => setIsMuted(!isMuted)}
                            className="fixed top-10 right-10 z-50 w-14 h-14 rounded-full bg-gradient-to-br from-purple-600/20 to-pink-600/20 border-2 border-purple-500/30 flex items-center justify-center group hover:from-purple-600/30 hover:to-pink-600/30 transition-all backdrop-blur-xl"
                        >
                            {isMuted ? <VolumeX className="w-6 h-6 text-white/50" /> : <Volume2 className="w-6 h-6 text-purple-400 animate-pulse" />}
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
