'use client';
import { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, MessageCircle, Sparkles, Volume2, VolumeX } from 'lucide-react';
import confetti from 'canvas-confetti';
import MusicPlayer from '../MusicPlayer';

interface InteractiveDodgeViewProps {
    data: any;
    isPreview?: boolean;
}

export default function InteractiveDodgeView({ data, isPreview }: InteractiveDodgeViewProps) {
    const [step, setStep] = useState(0);
    const [yesPressed, setYesPressed] = useState(false);
    const [noBtnPos, setNoBtnPos] = useState({ x: 0, y: 0, isAbsolute: false });
    const [yesScale, setYesScale] = useState(1);
    const [isMuted, setIsMuted] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

    const messages = data.introMessages || [
        "Hey Beautiful...",
        "We've made so many memories...",
        "You make me smile every single day...",
        "So I have a question...",
    ];

    const isMale = data.gender === 'male';
    const themeColor = isMale ? '#00A3FF' : '#FF4D8D';
    const bgColor = isMale ? 'from-blue-50 via-indigo-50 to-blue-100' : 'from-pink-50 via-red-50 to-pink-100';

    const handleNextIntro = () => {
        if (step < messages.length - 1) {
            setStep(step + 1);
        } else {
            setStep(messages.length); // Activate Question card
        }
    };

    const dodgeButton = () => {
        setYesScale(prev => prev + 0.15);
        if (containerRef.current) {
            const rect = containerRef.current.getBoundingClientRect();
            const padding = 60;
            const maxX = rect.width - 120;
            const maxY = rect.height - 60;

            const randomX = Math.random() * maxX;
            const randomY = Math.random() * maxY;

            setNoBtnPos({
                x: randomX,
                y: randomY,
                isAbsolute: true
            });
        }
    };

    const handleYes = () => {
        setYesPressed(true);
        const duration = 5 * 1000;
        const animationEnd = Date.now() + duration;
        const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

        const randomInRange = (min: number, max: number) => Math.random() * (max - min) + min;

        const interval: any = setInterval(function () {
            const timeLeft = animationEnd - Date.now();
            if (timeLeft <= 0) return clearInterval(interval);

            const particleCount = 50 * (timeLeft / duration);
            confetti({ ...defaults, particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } });
            confetti({ ...defaults, particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } });
        }, 250);
    };

    return (
        <div
            ref={containerRef}
            className={`w-full h-full ${isPreview ? 'relative' : 'fixed inset-0'} bg-gradient-to-br ${bgColor} flex items-center justify-center p-6 overflow-hidden select-none font-sans`}
        >
            {data.musicUrl && <MusicPlayer url={data.musicUrl} isMuted={isMuted} />}

            {data.musicUrl && (
                <button
                    onClick={() => setIsMuted(!isMuted)}
                    className="absolute top-6 right-6 z-50 w-10 h-10 rounded-full bg-white/40 border border-white/40 flex items-center justify-center hover:bg-white/60 transition-all backdrop-blur-md shadow-sm"
                    style={{ color: themeColor }}
                >
                    {isMuted ? <VolumeX className="w-5 h-5 opacity-60" /> : <Volume2 className="w-5 h-5 animate-pulse" />}
                </button>
            )}

            {/* Background Floating Elements */}
            <div className="absolute inset-0 pointer-events-none opacity-20">
                {[...Array(7)].map((_, i) => (
                    <motion.div
                        key={i}
                        initial={{ y: '110vh', x: `${Math.random() * 100}vw`, rotate: 0 }}
                        animate={{ y: '-10vh', rotate: 360 }}
                        transition={{ duration: 10 + Math.random() * 10, repeat: Infinity, delay: Math.random() * 10, ease: "linear" }}
                        className="absolute text-2xl"
                        style={{ color: themeColor }}
                    >
                        {isMale ? '❄️' : '❤️'}
                    </motion.div>
                ))}
            </div>

            <AnimatePresence mode="wait">
                {step < messages.length && !yesPressed && (
                    <motion.div
                        key={`intro-${step}`}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -20 }}
                        className="z-10 text-center max-w-lg p-10 bg-white/40 backdrop-blur-xl rounded-[40px] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.1)] border border-white/40 flex flex-col items-center gap-8"
                    >
                        <h1 className="text-3xl md:text-4xl font-bold tracking-tight leading-tight" style={{ color: themeColor }}>
                            {messages[step]}
                        </h1>
                        <button
                            onClick={handleNextIntro}
                            className="px-10 py-4 bg-white text-black font-bold rounded-full shadow-xl hover:scale-105 transition-all text-sm uppercase tracking-widest border border-black/5"
                        >
                            Continue
                        </button>
                    </motion.div>
                )}

                {step === messages.length && !yesPressed && (
                    <motion.div
                        key="question"
                        initial={{ opacity: 0, scale: 0.9 }}
                        animate={{ opacity: 1, scale: 1 }}
                        className="z-10 text-center max-w-2xl flex flex-col items-center"
                    >
                        <motion.div
                            animate={{ scale: [1, 1.1, 1] }}
                            transition={{ repeat: Infinity, duration: 1.5 }}
                            className="text-7xl md:text-9xl mb-8 drop-shadow-2xl"
                        >
                            {isMale ? '👑' : '❤️'}
                        </motion.div>

                        <h1 className="text-5xl md:text-8xl font-black tracking-tighter leading-none mb-4" style={{ color: themeColor }}>
                            {data.headline && (
                                <span className="block text-2xl md:text-3xl mb-4 italic font-medium opacity-60 text-black">{data.headline}</span>
                            )}
                            Will you be my <br />
                            <span className="italic">Valentine?</span>
                        </h1>

                        <p className="text-black/40 font-medium text-lg md:text-xl mb-12">
                            {isMale ? "(I'll be your best teammate 🎮)" : "(I promise to buy you chocolate 🍫)"}
                        </p>

                        <div className="flex flex-col md:flex-row items-center justify-center gap-6 md:gap-12 min-h-[100px] w-full">
                            <motion.button
                                onClick={handleYes}
                                style={{ scale: yesScale }}
                                className="bg-green-500 text-white font-black py-5 px-12 rounded-full shadow-[0_20px_40px_-10px_rgba(34,197,94,0.4)] text-2xl tracking-tight z-20"
                            >
                                YES!
                            </motion.button>

                            <motion.button
                                layout
                                onPointerOver={dodgeButton}
                                onClick={dodgeButton}
                                className={`py-4 px-10 rounded-full font-bold text-xl border transition-all ${noBtnPos.isAbsolute ? 'absolute z-50 bg-white/80 backdrop-blur-md shadow-2xl' : 'bg-black/5 border-black/5 text-black/40'}`}
                                style={noBtnPos.isAbsolute ? {
                                    left: noBtnPos.x,
                                    top: noBtnPos.y,
                                    transition: 'all 0.15s ease-out'
                                } : {}}
                            >
                                No
                            </motion.button>
                        </div>
                    </motion.div>
                )}

                {yesPressed && (
                    <motion.div
                        key="success"
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        className="z-10 text-center max-w-xl p-12 bg-white/60 backdrop-blur-2xl rounded-[50px] shadow-[0_40px_80px_-20px_rgba(0,0,0,0.15)] border-4 border-white flex flex-col items-center gap-6"
                    >
                        <div className="text-7xl md:text-8xl mb-4">🥰🎉</div>
                        <h2 className="text-4xl md:text-6xl font-black tracking-tighter leading-tight" style={{ color: themeColor }}>
                            {isMale ? "HE SAID YES!" : "SHE SAID YES!"}
                        </h2>
                        <p className="text-xl md:text-2xl text-black/60 font-medium leading-tight">
                            You just made me the happiest person in the world. <br />
                            I love you! ❤️
                        </p>

                        {data.whatsapp && (
                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.6 }}
                                className="pt-6"
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

                        <div className="mt-4 text-[10px] font-black uppercase tracking-[0.3em] text-black/20">
                            Capture this moment
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
