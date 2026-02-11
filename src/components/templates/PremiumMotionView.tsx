'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, Music, Volume2, VolumeX, Send } from 'lucide-react';
import { useState, useEffect, useRef } from 'react';

interface PremiumMotionViewProps {
    data: any;
    isPreview?: boolean;
    onUpdateData?: (newData: any) => void;
}

export default function PremiumMotionView({ data, isPreview, onUpdateData }: PremiumMotionViewProps) {
    const [phase, setPhase] = useState<'intro' | 'card'>(isPreview ? 'card' : 'intro');
    const [introStep, setIntroStep] = useState(0);
    const [isMuted, setIsMuted] = useState(true);
    const audioRef = useRef<HTMLAudioElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);

    // Default values if not provided
    const introQuote1Line1 = data.introQuote1Line1 || "Love is not just a feeling...";
    const introQuote1Line2 = data.introQuote1Line2 || "it's a journey of the soul.";
    const introQuote2Line1 = data.introQuote2Line1 || "To bring";
    const introQuote2Line2 = data.introQuote2Line2 || "someone special into your life.";
    const happyText = data.happyText || "Happy";
    const valentineText = data.valentineText || "Valentine's";
    const buttonText = data.buttonText || "Send Your Wishes";

    const introQuotes = [
        { text: introQuote1Line1, sub: introQuote1Line2 },
        { text: introQuote2Line1, sub: introQuote2Line2 }
    ];

    useEffect(() => {
        if (!isPreview && phase === 'intro') {
            const timer = setTimeout(() => {
                if (introStep < introQuotes.length - 1) {
                    setIntroStep(introStep + 1);
                } else {
                    setPhase('card');
                }
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [introStep, phase, isPreview]);

    // Simplified effect
    useEffect(() => {
        // Heart logic handled in its own effect below
    }, [phase]);

    const handleTextChange = (field: string, e: React.FormEvent<HTMLSpanElement>) => {
        if (onUpdateData) {
            onUpdateData({ ...data, [field]: e.currentTarget.innerText });
        }
    };

    const toggleMute = () => {
        if (audioRef.current) {
            if (isMuted) {
                audioRef.current.play().catch(() => { });
            } else {
                audioRef.current.pause();
            }
            setIsMuted(!isMuted);
        }
    };

    // ... (rest of the initHearts and animation logic remains the same)
    useEffect(() => {
        if (phase === 'card' && canvasRef.current) {
            const canvas = canvasRef.current;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            let hearts: any[] = [];
            let animationFrameId: number;

            const resize = () => {
                const parent = canvas.parentElement;
                if (parent) {
                    canvas.width = parent.clientWidth;
                    canvas.height = parent.clientHeight;
                }
            };

            window.addEventListener('resize', resize);
            resize();

            class HeartParticle {
                x: number; y: number; size: number; speed: number; opacity: number; color: string; wobble: number; wobbleSpeed: number;
                constructor() {
                    this.x = Math.random() * canvas.width;
                    this.y = canvas.height + 20;
                    this.size = Math.random() * 8 + 4; // Larger hearts
                    this.speed = Math.random() * 1.5 + 0.5;
                    this.opacity = Math.random() * 0.4 + 0.4;
                    this.color = Math.random() > 0.5 ? '#FF4D4D' : '#D4AF37';
                    this.wobble = Math.random() * Math.PI * 2;
                    this.wobbleSpeed = Math.random() * 0.03;
                }
                draw() {
                    if (!ctx) return;
                    ctx.save();
                    ctx.translate(this.x + Math.sin(this.wobble) * 30, this.y);
                    ctx.globalAlpha = this.opacity;
                    ctx.fillStyle = this.color;

                    // Improved heart shape logic
                    ctx.beginPath();
                    ctx.moveTo(0, 0);
                    ctx.bezierCurveTo(-this.size, -this.size, -this.size * 2, this.size / 3, 0, this.size * 2.5);
                    ctx.bezierCurveTo(this.size * 2, this.size / 3, this.size, -this.size, 0, 0);
                    ctx.fill();
                    ctx.restore();
                }
                update() {
                    this.y -= this.speed;
                    this.wobble += this.wobbleSpeed;
                    this.opacity -= 0.001;
                }
            }

            const animate = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                // Reduced frequency and count for performance
                if (hearts.length < 30 && Math.random() < 0.1) {
                    hearts.push(new HeartParticle());
                }
                hearts.forEach((h, i) => {
                    h.update();
                    h.draw();
                    if (h.y < -50 || h.opacity <= 0) hearts.splice(i, 1);
                });
                animationFrameId = requestAnimationFrame(animate);
            };

            animate();

            return () => {
                window.removeEventListener('resize', resize);
                cancelAnimationFrame(animationFrameId);
            };
        }
    }, [phase]);

    return (
        <div className={`fixed inset-0 bg-black text-white font-sans overflow-hidden ${isPreview ? 'relative w-full h-full min-h-[500px]' : 'fixed'}`}>
            <style jsx global>{`
                @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap');
                .font-great-vibes { font-family: 'Great Vibes', cursive; }
                .font-playfair { font-family: 'Playfair Display', serif; }
                [contenteditable]:focus { outline: none; background: rgba(255, 255, 255, 0.05); border-radius: 4px; }
            `}</style>

            {!isPreview && <audio ref={audioRef} loop src="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3" />}

            {!isPreview && (
                <button
                    onClick={toggleMute}
                    className="fixed bottom-8 right-8 z-[100] flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-[10px] uppercase tracking-widest hover:bg-white/20 transition-all"
                >
                    {isMuted ? <VolumeX size={14} /> : <Volume2 size={14} />}
                    <span>{isMuted ? 'Sound On' : 'Sound Off'}</span>
                </button>
            )}

            <AnimatePresence mode="wait">
                {phase === 'intro' ? (
                    <motion.div
                        key="intro"
                        exit={{ opacity: 0 }}
                        transition={{ duration: 1.5 }}
                        className="absolute inset-0 flex flex-col items-center justify-center p-6 text-center"
                    >
                        <AnimatePresence mode="wait">
                            <motion.div
                                key={introStep}
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, y: -20 }}
                                transition={{ duration: 1 }}
                                className="space-y-4"
                            >
                                <p className="text-2xl md:text-4xl font-playfair">{introQuotes[introStep].text}</p>
                                <p className="text-xl md:text-2xl font-playfair italic text-white/60">{introQuotes[introStep].sub}</p>
                            </motion.div>
                        </AnimatePresence>
                    </motion.div>
                ) : (
                    <motion.div
                        key="card"
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ duration: 1.5 }}
                        className="absolute inset-0 flex items-center justify-center p-6"
                    >
                        <canvas ref={canvasRef} className="absolute inset-0 pointer-events-none" />

                        <motion.div
                            initial={isPreview ? {} : { scale: 0.9, y: 30, opacity: 0 }}
                            animate={isPreview ? {} : { scale: 1, y: 0, opacity: 1 }}
                            transition={{ duration: 1.5, ease: [0.34, 1.56, 0.64, 1] }}
                            className="relative w-[90%] max-w-[500px] aspect-[4/5] bg-white/[0.15] backdrop-blur-[20px] border border-white/20 rounded-[24px] p-10 flex flex-col items-center justify-center shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] overflow-hidden"
                        >
                            {/* Card Shine */}
                            <div className="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent pointer-events-none" />

                            {/* Decoration Dots */}
                            <div className="absolute top-[20px] flex gap-[15px] opacity-60">
                                <div className="w-2 h-2 rounded-full bg-[#D4AF37]" />
                                <div className="w-2 h-2 rounded-full bg-[#D4AF37]" />
                                <div className="w-2 h-2 rounded-full bg-[#D4AF37]" />
                            </div>

                            <motion.span
                                initial={isPreview ? {} : { opacity: 0, y: 20 }}
                                animate={isPreview ? {} : { opacity: 1, y: 0 }}
                                transition={{ delay: 1.5, duration: 1 }}
                                contentEditable={isPreview}
                                onBlur={(e) => handleTextChange('happyText', e)}
                                suppressContentEditableWarning
                                className="text-[2.5rem] font-playfair italic text-[#D4AF37] tracking-[2px] mb-[-10px] cursor-text"
                            >
                                {happyText}
                            </motion.span>

                            <motion.h1
                                initial={isPreview ? {} : { opacity: 0, y: 20 }}
                                animate={isPreview ? {} : { opacity: 1, y: 0 }}
                                transition={{ delay: 1.8, duration: 1 }}
                                contentEditable={isPreview}
                                onBlur={(e) => handleTextChange('valentineText', e)}
                                suppressContentEditableWarning
                                className="text-[clamp(4rem,15vw,7rem)] font-great-vibes text-white mb-5 text-center drop-shadow-[0_0_20px_rgba(168,36,36,0.5)] cursor-text bg-gradient-to-r from-white to-[#f0f0f0] bg-clip-text text-transparent"
                            >
                                {valentineText}
                            </motion.h1>

                            <motion.div
                                initial={isPreview ? {} : { opacity: 0, y: 20 }}
                                animate={isPreview ? {} : { opacity: 1, y: 0 }}
                                transition={{ delay: 2.1, duration: 1 }}
                                className="text-center space-y-2 mb-10 text-white/80 font-light leading-[1.6] max-w-[80%]"
                            >
                                <p className="text-sm md:text-base">
                                    To my dearest <span
                                        contentEditable={isPreview}
                                        onBlur={(e) => handleTextChange('recipient', e)}
                                        suppressContentEditableWarning
                                        className="text-white font-semibold underline decoration-[#D4AF37]/50 cursor-text"
                                    >{data.recipient || '[Name]'}</span>,
                                </p>
                                <p
                                    contentEditable={isPreview}
                                    onBlur={(e) => handleTextChange('message', e)}
                                    suppressContentEditableWarning
                                    className="text-sm md:text-base italic cursor-text"
                                >
                                    {data.message || 'May this day be as beautiful and radiant as your love.'}
                                </p>
                            </motion.div>

                            <motion.button
                                initial={isPreview ? {} : { opacity: 0, y: 20 }}
                                animate={isPreview ? {} : { opacity: 1, y: 0 }}
                                transition={{ delay: 2.4, duration: 1 }}
                                whileHover={{ scale: 1.05 }}
                                whileTap={{ scale: 0.95 }}
                                className="px-8 py-3 bg-gradient-to-r from-[#A82424] to-[#7A1B1B] text-white text-[11px] font-bold uppercase tracking-[3px] rounded-full shadow-[0_10px_20px_rgba(168,36,36,0.3)] border-none relative overflow-hidden"
                            >
                                <span
                                    contentEditable={isPreview}
                                    onBlur={(e) => handleTextChange('buttonText', e)}
                                    suppressContentEditableWarning
                                    className="cursor-text relative z-10"
                                >{buttonText}</span>
                            </motion.button>

                            {/* Floating Illustration Background */}
                            <img
                                src="https://illustrations.popsy.co/pink/couple-on-a-date.svg"
                                className="absolute bottom-[-10%] left-1/2 -translate-x-1/2 w-64 opacity-20 pointer-events-none -z-10 grayscale invert"
                                alt=""
                            />
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
