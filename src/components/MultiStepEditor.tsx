'use client';
import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Heart, ArrowRight, ArrowLeft, Send, User, MessageCircle, Sparkles, Phone, Music, Music2, Share2 } from 'lucide-react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';

interface MultiStepEditorProps {
    templateId: string;
    onClose: () => void;
    editId?: string;
}

import AmourView from './templates/AmourView';
import MinimalEliteView from './templates/MinimalEliteView';
import PremiumMotionView from './templates/PremiumMotionView';
import QuestValentineView from './templates/QuestValentineView';
import { useToast } from './Toast';

export default function MultiStepEditor({ templateId: initialTemplateId, onClose, editId }: MultiStepEditorProps) {
    const { showToast } = useToast();
    const [step, setStep] = useState(1);
    const [templateId, setTemplateId] = useState(initialTemplateId);
    const [fetchingData, setFetchingData] = useState(!!editId);
    const [data, setData] = useState({
        recipient: '',
        headline: 'We offer the best moments to',
        message: '',
        sender: '',
        imageUrl: '',
        whatsapp: '',
        musicUrl: '',
        // Premium Motion Specific
        introQuote1Line1: '',
        introQuote1Line2: '',
        introQuote2Line1: '',
        introQuote2Line2: '',
        happyText: '',
        valentineText: '',
        buttonText: '',
    });
    const [loading, setLoading] = useState(false);
    const [link, setLink] = useState('');
    const router = useRouter();

    useEffect(() => {
        if (editId) {
            setFetchingData(true);
            fetch(`/api/valentines/${editId}`)
                .then(res => res.json())
                .then(valData => {
                    if (valData) {
                        if (valData.templateId) {
                            setTemplateId(valData.templateId);
                        }
                        setData({
                            recipient: valData.recipient || '',
                            headline: valData.headline || 'We offer the best moments to',
                            message: valData.message || '',
                            sender: valData.sender || '',
                            imageUrl: valData.imageUrl || '',
                            whatsapp: valData.whatsapp || '',
                            musicUrl: valData.musicUrl || '',
                            introQuote1Line1: valData.introQuote1Line1 || '',
                            introQuote1Line2: valData.introQuote1Line2 || '',
                            introQuote2Line1: valData.introQuote2Line1 || '',
                            introQuote2Line2: valData.introQuote2Line2 || '',
                            happyText: valData.happyText || '',
                            valentineText: valData.valentineText || '',
                            buttonText: valData.buttonText || '',
                        });
                    }
                })
                .finally(() => setFetchingData(false));
        }
    }, [editId]);

    const stepsForPreview = [
        { type: 'intro' },
        { type: 'message' },
        { type: 'image' },
        { type: 'outro' },
    ];

    // Map editor step to preview step (step 1-4 maps to preview index 0-3)
    const currentPreviewStepIndex = Math.min(step - 1, 3);

    const handleNext = () => setStep(step + 1);
    const handleBack = () => setStep(step - 1);

    const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => {
                setData(prev => ({ ...prev, imageUrl: reader.result as string }));
            };
            reader.readAsDataURL(file);
        }
    };

    const getStepsConfig = () => {
        switch (templateId) {
            case 'valentine-motion-premium':
                return [
                    { id: 1, label: 'Recipient & Headline' },
                    { id: 2, label: 'Intro Sequence' },
                    { id: 3, label: 'The Content' },
                    { id: 4, label: 'Attachment' },
                    { id: 5, label: 'Audio Vibe' },
                    { id: 6, label: 'Signature' }
                ];
            case 'amour':
                return [
                    { id: 1, label: 'Recipient & Headline' },
                    { id: 2, label: 'The Content' },
                    { id: 3, label: 'Attachment' },
                    { id: 4, label: 'Audio Vibe' },
                    { id: 5, label: 'Signature' }
                ];
            case 'minimal-elite-card':
                return [
                    { id: 1, label: 'Recipient' },
                    { id: 2, label: 'The Content' },
                    { id: 3, label: 'Signature' }
                ];
            case 'quest-valentine':
                return [
                    { id: 1, label: 'Recipient' },
                    { id: 2, label: 'Audio Vibe' },
                    { id: 3, label: 'Signature' }
                ];
            default:
                return [{ id: 1, label: 'Recipient' }, { id: 2, label: 'Signature' }];
        }
    };

    const stepsConfig = getStepsConfig();
    const totalSteps = stepsConfig.length;

    const handleFinish = async () => {
        setLoading(true);
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        try {
            const method = editId ? 'PUT' : 'POST';
            const body = editId
                ? JSON.stringify({ ...data, id: editId, templateId, userId: user.id })
                : JSON.stringify({ ...data, templateId, userId: user.id });

            const res = await fetch('/api/valentines', {
                method,
                headers: { 'Content-Type': 'application/json' },
                body,
            });
            const result = await res.json();
            const finalId = editId || result.id;

            if (finalId) {
                const successStep = totalSteps + 1;
                setLink(`${window.location.origin}/v/${finalId}`);
                setStep(successStep);
                showToast('Asset deployed successfully', 'success');
            } else {
                showToast('Generation failed: ID missing', 'error');
            }
        } catch (err) {
            showToast('Failed to save asset', 'error');
        } finally {
            setLoading(false);
        }
    };

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[100] flex items-center justify-center bg-[#050505]/90 backdrop-blur-3xl overflow-y-auto pt-10 pb-10 px-4"
        >
            {/* Animated bubbles in background */}
            <div className="absolute inset-0 pointer-events-none overflow-hidden">
                {[...Array(20)].map((_, i) => (
                    <motion.div
                        key={i}
                        animate={{
                            y: [-20, -1200],
                            x: Math.random() * 400 - 200,
                            opacity: [0, 1, 0],
                        }}
                        transition={{
                            duration: Math.random() * 10 + 10,
                            repeat: Infinity,
                            delay: Math.random() * 20,
                        }}
                        className="absolute bottom-0 text-white/5"
                        style={{ left: `${Math.random() * 100}%` }}
                    >
                        <Heart size={Math.random() * 40 + 10} />
                    </motion.div>
                ))}
            </div>

            <motion.div
                initial={{ scale: 0.9, y: 20 }}
                animate={{ scale: 1, y: 0 }}
                className="w-full max-w-6xl bg-[#0A0A0A] border border-white/5 rounded-[40px] shadow-2xl overflow-hidden relative z-10 flex flex-col md:flex-row h-full max-h-[850px]"
            >
                {/* Left Side: Live Preview */}
                <div className="flex-1 bg-[#111111] p-12 flex items-center justify-center border-r border-white/5 relative overflow-hidden hidden md:flex">
                    <div className="absolute inset-0 opacity-20 pointer-events-none bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:40px_40px]" />
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] bg-myRed/10 rounded-full blur-[100px]" />

                    {templateId === 'amour' ? (
                        /* Browser Mockup for WYSIWYG Website Preview */
                        <div className="relative w-full h-full max-h-[500px] bg-white rounded-2xl border-[8px] border-black shadow-2xl overflow-hidden flex flex-col">
                            <div className="h-6 bg-black flex items-center px-4 gap-1.5">
                                <div className="w-1.5 h-1.5 rounded-full bg-red-500" />
                                <div className="w-1.5 h-1.5 rounded-full bg-yellow-500" />
                                <div className="w-1.5 h-1.5 rounded-full bg-green-500" />
                                <div className="ml-4 h-3 flex-1 bg-white/10 rounded-sm" />
                            </div>
                            <div className="flex-1 pointer-events-none">
                                <AmourView
                                    data={data}
                                    steps={stepsForPreview}
                                    isPreview
                                    key={currentPreviewStepIndex} // Force internal step update
                                />
                            </div>
                        </div>
                    ) : (
                        /* iPhone Mockup Preview for Cards */
                        <div className="relative w-full max-w-[320px] aspect-[9/19] bg-white rounded-[50px] border-[10px] border-black shadow-2xl overflow-hidden flex flex-col">
                            <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[100px] h-[28px] bg-black rounded-b-2xl z-50 px-4 pt-1">
                                <div className="w-12 h-1 bg-white/10 mx-auto rounded-full" />
                            </div>

                            {templateId === 'minimal-elite-card' ? (
                                <div className="flex-1 pointer-events-none">
                                    <MinimalEliteView data={data} isPreview />
                                </div>
                            ) : templateId === 'valentine-motion-premium' ? (
                                <div className="flex-1">
                                    <PremiumMotionView
                                        data={data}
                                        isPreview
                                        onUpdateData={(newData) => setData(prev => ({ ...prev, ...newData }))}
                                    />
                                </div>
                            ) : templateId === 'quest-valentine' ? (
                                <div className="flex-1 pointer-events-none scale-[0.7] md:scale-100 origin-center">
                                    <QuestValentineView data={data} />
                                </div>
                            ) : (
                                <div className="flex-1 p-6 pt-12 flex flex-col items-center justify-between bg-[#FF99F1] text-black">
                                    <div className="w-full space-y-6">
                                        <div className="relative h-40 flex items-center justify-center">
                                            {/* Original Premium Mockup visuals */}
                                            <div className="absolute -translate-x-8 w-24 h-32 bg-red-500 rounded-3xl border-4 border-white rotate-[-12deg] shadow-lg">
                                                <div className="w-12 h-16 border-[8px] border-gray-300 rounded-t-full absolute -top-10 left-1/2 -translate-x-1/2" />
                                                <div className="w-full h-full flex items-center justify-center">
                                                    <Heart className="text-white fill-current w-8 h-8" />
                                                </div>
                                            </div>
                                            <div className="absolute translate-x-8 w-24 h-32 bg-pink-100 rounded-3xl border-4 border-white rotate-[12deg] z-10 shadow-lg">
                                                <div className="w-12 h-16 border-[8px] border-gray-300 rounded-t-full absolute -top-10 left-1/2 -translate-x-1/2" />
                                                <div className="w-full h-full flex items-center justify-center">
                                                    <Heart className="text-pink-400 fill-current w-8 h-8" />
                                                </div>
                                            </div>
                                        </div>
                                        <div className="space-y-4">
                                            <h2 className="text-2xl font-black leading-tight tracking-tighter">
                                                {data.headline} {data.recipient || '...'}
                                            </h2>
                                            {step === 2 && (
                                                <p className="text-[10px] font-bold leading-tight opacity-70 line-clamp-6 bg-white/40 p-3 rounded-2xl border border-white/20">
                                                    {data.message || 'The content of your beautiful message...'}
                                                </p>
                                            )}
                                            {step === 3 && data.imageUrl && (
                                                <div className="w-full aspect-square rounded-2xl overflow-hidden border-2 border-white/50 shadow-xl">
                                                    <img src={data.imageUrl} className="w-full h-full object-cover" alt="Preview" />
                                                </div>
                                            )}
                                            {step >= 4 && (
                                                <div className="pt-4 text-center">
                                                    <p className="text-[8px] font-black uppercase opacity-40">With eternal love,</p>
                                                    <p className="font-bold text-lg">{data.sender || '...'}</p>
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    <div className="w-full space-y-3">
                                        <div className="w-full h-10 bg-black rounded-full flex items-center justify-center text-white text-[10px] font-black uppercase tracking-widest shadow-lg">
                                            Continue
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Right Side: Form */}
                <div className="w-full md:w-[500px] flex flex-col bg-[#0A0A0A]">
                    {/* Header */}
                    <div className="p-8 border-b border-white/5 flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-8 h-8 bg-myRed rounded-lg flex items-center justify-center">
                                <Heart className="text-white w-4 h-4 fill-current" />
                            </div>
                            <span className="text-xs font-bold uppercase tracking-widest text-white/40">Step {step} of {totalSteps}</span>
                        </div>
                        <button onClick={onClose} className="p-2 hover:bg-white/5 rounded-full transition-colors">
                            <X className="w-5 h-5 text-white/40" />
                        </button>
                    </div>

                    <div className="p-10 flex-1 overflow-y-auto">
                        <AnimatePresence mode="wait">
                            {fetchingData ? (
                                <div key="fetching" className="flex flex-col items-center justify-center py-20 space-y-4">
                                    <motion.div
                                        animate={{ rotate: 360 }}
                                        transition={{ repeat: Infinity, duration: 2, ease: "linear" }}
                                        className="w-10 h-10 border-2 border-myRed border-t-transparent rounded-full"
                                    />
                                    <p className="text-white/40 text-[10px] font-black uppercase tracking-[0.2em] text-center">Decrypting stored<br />heart data...</p>
                                </div>
                            ) : (
                                <>
                                    {currentFeature === 'recipient_headline' || currentFeature === 'recipient' && (
                                        <motion.div
                                            key="step-rec"
                                            initial={{ opacity: 0, x: 20 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            exit={{ opacity: 0, x: -20 }}
                                            className="space-y-8"
                                        >
                                            <div className="space-y-4">
                                                <h2 className="text-3xl font-medium tracking-tight">
                                                    {currentFeature === 'recipient_headline' ? 'Recipient & Headline' : 'Recipient'}
                                                </h2>
                                                <p className="text-white/40 font-medium">To whom is this message assigned?</p>
                                            </div>
                                            <div className="space-y-4">
                                                {currentFeature === 'recipient_headline' && (
                                                    <div className="relative">
                                                        <Sparkles className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                                                        <input
                                                            type="text"
                                                            value={data.headline}
                                                            onChange={(e) => setData({ ...data, headline: e.target.value })}
                                                            placeholder="Headline (e.g. For my love)"
                                                            className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium"
                                                        />
                                                    </div>
                                                )}
                                                <div className="relative">
                                                    <User className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                                                    <input
                                                        type="text"
                                                        value={data.recipient}
                                                        onChange={(e) => setData({ ...data, recipient: e.target.value })}
                                                        placeholder="Recipient Name"
                                                        className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium"
                                                    />
                                                </div>
                                            </div>
                                            <button
                                                disabled={!data.recipient || (currentFeature === 'recipient_headline' && !data.headline)}
                                                onClick={handleNext}
                                                className="w-full py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 disabled:opacity-50"
                                            >
                                                <span>Continue</span>
                                                <ArrowRight className="w-5 h-5" />
                                            </button>
                                        </motion.div>
                                    )}

                                    {currentFeature === 'intro' && (
                                        <motion.div
                                            key="step-intro"
                                            initial={{ opacity: 0, x: 20 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            exit={{ opacity: 0, x: -20 }}
                                            className="space-y-8"
                                        >
                                            <div className="space-y-4">
                                                <h2 className="text-3xl font-medium tracking-tight">Intro Sequence</h2>
                                                <p className="text-white/40 font-medium">Customize the cinematic opening quotes.</p>
                                            </div>
                                            <div className="space-y-4">
                                                <div className="grid grid-cols-1 gap-4">
                                                    <div className="space-y-2">
                                                        <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Quote 1 (Main / Sub)</label>
                                                        <input
                                                            type="text"
                                                            value={data.introQuote1Line1}
                                                            onChange={(e) => setData({ ...data, introQuote1Line1: e.target.value })}
                                                            placeholder="Love is not just a feeling..."
                                                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm"
                                                        />
                                                        <input
                                                            type="text"
                                                            value={data.introQuote1Line2}
                                                            onChange={(e) => setData({ ...data, introQuote1Line2: e.target.value })}
                                                            placeholder="it's a journey of the soul."
                                                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm italic opacity-60"
                                                        />
                                                    </div>
                                                    <div className="space-y-2">
                                                        <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Quote 2 (Main / Sub)</label>
                                                        <input
                                                            type="text"
                                                            value={data.introQuote2Line1}
                                                            onChange={(e) => setData({ ...data, introQuote2Line1: e.target.value })}
                                                            placeholder="To bring"
                                                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm"
                                                        />
                                                        <input
                                                            type="text"
                                                            value={data.introQuote2Line2}
                                                            onChange={(e) => setData({ ...data, introQuote2Line2: e.target.value })}
                                                            placeholder="someone special into your life."
                                                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm italic opacity-60"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex gap-4">
                                                <button onClick={handleBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                                                <button onClick={handleNext} className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3">
                                                    <span>Continue</span>
                                                    <ArrowRight className="w-5 h-5" />
                                                </button>
                                            </div>
                                        </motion.div>
                                    )}

                                    {currentFeature === 'content' && (
                                        <motion.div
                                            key="step-content"
                                            initial={{ opacity: 0, x: 20 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            exit={{ opacity: 0, x: -20 }}
                                            className="space-y-8"
                                        >
                                            <div className="space-y-4">
                                                <h2 className="text-3xl font-medium tracking-tight">The Content</h2>
                                                <p className="text-white/40 font-medium">Write something unforgettable.</p>
                                            </div>
                                            <div className="space-y-4">
                                                <div className="relative">
                                                    <MessageCircle className="absolute left-6 top-6 w-5 h-5 text-white/20" />
                                                    <textarea
                                                        rows={5}
                                                        value={data.message}
                                                        onChange={(e) => setData({ ...data, message: e.target.value })}
                                                        placeholder="My heart belongs strictly to you..."
                                                        className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium resize-none"
                                                    />
                                                </div>
                                                {templateId === 'valentine-motion-premium' && (
                                                    <div className="grid grid-cols-2 gap-4">
                                                        <div className="space-y-2">
                                                            <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Happy Text</label>
                                                            <input
                                                                type="text"
                                                                value={data.happyText}
                                                                onChange={(e) => setData({ ...data, happyText: e.target.value })}
                                                                placeholder="Happy"
                                                                className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm"
                                                            />
                                                        </div>
                                                        <div className="space-y-2">
                                                            <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Valentine Text</label>
                                                            <input
                                                                type="text"
                                                                value={data.valentineText}
                                                                onChange={(e) => setData({ ...data, valentineText: e.target.value })}
                                                                placeholder="Valentine's"
                                                                className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm"
                                                            />
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                            <div className="flex gap-4">
                                                <button onClick={handleBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                                                <button
                                                    disabled={!data.message}
                                                    onClick={handleNext}
                                                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3"
                                                >
                                                    <span>Continue</span>
                                                    <ArrowRight className="w-5 h-5" />
                                                </button>
                                            </div>
                                        </motion.div>
                                    )}

                                    {currentFeature === 'attachment' && (
                                        <motion.div
                                            key="step-attachment"
                                            initial={{ opacity: 0, x: 20 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            exit={{ opacity: 0, x: -20 }}
                                            className="space-y-8"
                                        >
                                            <div className="space-y-4">
                                                <h2 className="text-3xl font-medium tracking-tight">Attachment</h2>
                                                <p className="text-white/40 font-medium">Upload a photo to seal the memory (Optional).</p>
                                            </div>
                                            <div className="space-y-4">
                                                <label className="flex flex-col items-center justify-center w-full aspect-video bg-white/[0.03] rounded-[32px] border-2 border-dashed border-white/5 hover:border-myRed/30 cursor-pointer transition-all group overflow-hidden">
                                                    {data.imageUrl ? (
                                                        <img src={data.imageUrl} className="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity" alt="Selected" />
                                                    ) : (
                                                        <div className="flex flex-col items-center gap-4">
                                                            <div className="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center group-hover:bg-myRed/20 transition-colors">
                                                                <Sparkles className="w-8 h-8 text-white/20 group-hover:text-myRed" />
                                                            </div>
                                                            <span className="text-xs font-bold text-white/20 uppercase tracking-widest">Select Image</span>
                                                        </div>
                                                    )}
                                                    <input type="file" className="hidden" accept="image/*" onChange={handleImageUpload} />
                                                </label>
                                            </div>
                                            <div className="flex gap-4">
                                                <button onClick={handleBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                                                <button
                                                    onClick={handleNext}
                                                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3"
                                                >
                                                    <span>{data.imageUrl ? 'Confirm Image' : 'Skip Step'}</span>
                                                    <ArrowRight className="w-5 h-5" />
                                                </button>
                                            </div>
                                        </motion.div>
                                    )}

                                    {currentFeature === 'audio' && (
                                        <motion.div
                                            key="step-audio"
                                            initial={{ opacity: 0, x: 20 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            exit={{ opacity: 0, x: -20 }}
                                            className="space-y-8"
                                        >
                                            <div className="space-y-4">
                                                <h2 className="text-3xl font-medium tracking-tight">Audio Vibe</h2>
                                                <p className="text-white/40 font-medium">Select a soundtrack or paste a YouTube/Spotify link.</p>
                                            </div>

                                            <div className="grid grid-cols-2 gap-3">
                                                {[
                                                    { name: 'Romantic Piano', url: 'https://www.youtube.com/embed/WJ3-F02-F_Y' },
                                                    { name: 'Lofi Love', url: 'https://www.youtube.com/embed/5yx6BWlEVcY' },
                                                    { name: 'Cinematic Strings', url: 'https://www.youtube.com/embed/B_mS_j8J0K0' },
                                                    { name: 'Acoustic Guitar', url: 'https://www.youtube.com/embed/2mzX_7YhJ2g' }
                                                ].map((track) => (
                                                    <button
                                                        key={track.name}
                                                        onClick={() => setData({ ...data, musicUrl: track.url })}
                                                        className={`p-4 rounded-2xl border transition-all text-left group ${data.musicUrl === track.url
                                                            ? 'bg-myRed/20 border-myRed text-white'
                                                            : 'bg-white/5 border-white/5 text-white/40 hover:border-white/20'
                                                            }`}
                                                    >
                                                        <Music2 className={`w-5 h-5 mb-2 ${data.musicUrl === track.url ? 'text-myRed' : 'text-white/20'}`} />
                                                        <div className="text-xs font-bold leading-tight uppercase tracking-widest">{track.name}</div>
                                                    </button>
                                                ))}
                                            </div>

                                            <div className="relative">
                                                <Music className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                                                <input
                                                    type="text"
                                                    value={data.musicUrl}
                                                    onChange={(e) => setData({ ...data, musicUrl: e.target.value })}
                                                    placeholder="Or paste Embed URL (YouTube/Spotify)"
                                                    className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium"
                                                />
                                            </div>

                                            <div className="flex gap-4">
                                                <button onClick={handleBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                                                <button
                                                    onClick={handleNext}
                                                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3"
                                                >
                                                    <span>{data.musicUrl ? 'Track Set' : 'Skip Audio'}</span>
                                                    <ArrowRight className="w-5 h-5" />
                                                </button>
                                            </div>
                                        </motion.div>
                                    )}

                                    {currentFeature === 'signature' && (
                                        <motion.div
                                            key="step-sig"
                                            initial={{ opacity: 0, x: 20 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            exit={{ opacity: 0, x: -20 }}
                                            className="space-y-8"
                                        >
                                            <div className="space-y-4">
                                                <h2 className="text-3xl font-medium tracking-tight">Signature</h2>
                                                <p className="text-white/40 font-medium">Whom shall we say sent this?</p>
                                            </div>
                                            <div className="space-y-4">
                                                <div className="relative">
                                                    <Send className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                                                    <input
                                                        type="text"
                                                        value={data.sender}
                                                        onChange={(e) => setData({ ...data, sender: e.target.value })}
                                                        placeholder="Your Name / Nickname"
                                                        className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium"
                                                    />
                                                </div>
                                                {templateId === 'valentine-motion-premium' && (
                                                    <div className="space-y-2">
                                                        <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Button Text</label>
                                                        <input
                                                            type="text"
                                                            value={data.buttonText}
                                                            onChange={(e) => setData({ ...data, buttonText: e.target.value })}
                                                            placeholder="Send Your Wishes"
                                                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm"
                                                        />
                                                    </div>
                                                )}
                                                {(templateId === 'amour' || templateId === 'quest-valentine') && (
                                                    <div className="relative">
                                                        <Phone className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                                                        <input
                                                            type="text"
                                                            value={data.whatsapp}
                                                            onChange={(e) => setData({ ...data, whatsapp: e.target.value })}
                                                            placeholder="WhatsApp Number (e.g. +234...)"
                                                            className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium"
                                                        />
                                                    </div>
                                                )}
                                            </div>
                                            <div className="flex gap-4">
                                                <button onClick={handleBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                                                <button
                                                    disabled={!data.sender || loading}
                                                    onClick={handleFinish}
                                                    className={`flex-[2] py-5 ${templateId === 'valentine-motion-premium' ? 'bg-[#A82424] hover:bg-[#7A1B1B]' : 'bg-myRed hover:bg-myRed/90'} text-white font-bold rounded-2xl transition-all flex items-center justify-center gap-3 relative overflow-hidden`}
                                                >
                                                    {loading && templateId === 'valentine-motion-premium' && (
                                                        <motion.div
                                                            className="absolute inset-0 bg-white/10"
                                                            initial={{ x: '-100%' }}
                                                            animate={{ x: '100%' }}
                                                            transition={{ repeat: Infinity, duration: 1, ease: 'linear' }}
                                                        />
                                                    )}
                                                    <span>{loading ? (templateId === 'valentine-motion-premium' ? 'Encrypting Heart...' : 'Processing...') : 'Deploy Asset'}</span>
                                                    <Sparkles className="w-5 h-5" />
                                                </button>
                                            </div>
                                        </motion.div>
                                    )}

                                    {step === (templateId === 'valentine-motion-premium' ? 7 : 6) && (
                                        <motion.div
                                            key="success"
                                            initial={{ opacity: 0 }}
                                            animate={{ opacity: 1 }}
                                            className="absolute inset-0 z-[100] flex flex-col items-center justify-center p-8 bg-black/95 backdrop-blur-3xl"
                                        >
                                            {/* Global Close to Dashboard */}
                                            <button
                                                onClick={() => {
                                                    onClose();
                                                    router.push('/dashboard');
                                                }}
                                                className="absolute top-10 right-10 p-3 bg-white/5 hover:bg-white/10 rounded-full transition-all border border-white/10 group"
                                            >
                                                <X className="w-6 h-6 text-white/40 group-hover:text-white" />
                                            </button>
                                            {/* Confetti Effect */}
                                            <div className="absolute inset-0 pointer-events-none overflow-hidden">
                                                {[...Array(24)].map((_, i) => (
                                                    <motion.div
                                                        key={i}
                                                        initial={{
                                                            top: "100%",
                                                            left: `${Math.random() * 100}%`,
                                                            scale: Math.random() * 0.5 + 0.5,
                                                            opacity: 0,
                                                            rotate: Math.random() * 360
                                                        }}
                                                        animate={{
                                                            top: "-10%",
                                                            opacity: [0, 1, 1, 0],
                                                            rotate: 360 + (Math.random() * 360),
                                                            x: (Math.random() - 0.5) * 300
                                                        }}
                                                        transition={{
                                                            duration: Math.random() * 4 + 3,
                                                            repeat: Infinity,
                                                            delay: Math.random() * 3,
                                                            ease: "linear"
                                                        }}
                                                        className="absolute"
                                                    >
                                                        <Heart className={`${i % 2 === 0 ? 'text-myRed/40' : 'text-[#D4AF37]/40'} fill-current w-6 h-6`} />
                                                    </motion.div>
                                                ))}
                                            </div>

                                            <motion.div
                                                initial={{ scale: 0.8, y: 30, opacity: 0 }}
                                                animate={{ scale: 1, y: 0, opacity: 1 }}
                                                transition={{ type: "spring", damping: 15, stiffness: 100 }}
                                                className="w-full max-w-md space-y-8 text-center relative z-10"
                                            >
                                                <div className="relative mx-auto w-32 h-32 mb-8">
                                                    <motion.div
                                                        animate={{
                                                            scale: [1, 1.1, 1],
                                                            rotate: [0, 5, -5, 0]
                                                        }}
                                                        transition={{ duration: 4, repeat: Infinity }}
                                                        className={`w-full h-full ${templateId === 'valentine-motion-premium' ? 'bg-[#D4AF37]/10' : 'bg-myRed/10'} rounded-[40px] flex items-center justify-center border border-white/5 relative z-10 shadow-2xl`}
                                                    >
                                                        <Heart className={`${templateId === 'valentine-motion-premium' ? 'text-[#D4AF37]' : 'text-myRed'} w-14 h-14 fill-current`} />
                                                    </motion.div>
                                                    <div className="absolute -top-4 -right-4 w-12 h-12 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl flex items-center justify-center animate-bounce">
                                                        <Sparkles className="w-6 h-6 text-myRed" />
                                                    </div>
                                                </div>

                                                <div className="space-y-3">
                                                    <h2 className="text-4xl font-black tracking-tight italic uppercase">
                                                        {templateId === 'valentine-motion-premium' ? 'Masterpiece Deployed!' : 'Asset Deployed!'}
                                                    </h2>
                                                    <p className="text-white/40 font-medium italic max-w-[300px] mx-auto leading-tight">
                                                        {templateId === 'valentine-motion-premium'
                                                            ? 'Your premium cinematic experience is now live on the decentralized web.'
                                                            : 'Your interactive card is now live and ready to be received.'}
                                                    </p>
                                                </div>

                                                <div className="p-8 bg-white/[0.03] rounded-[32px] border border-white/5 space-y-6 relative overflow-hidden group">
                                                    <div className="space-y-3">
                                                        <p className="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] text-left ml-2">Secure Discovery URL</p>
                                                        <div className="flex items-center gap-3 text-white font-medium bg-black/60 p-4 rounded-2xl border border-white/10 overflow-hidden text-sm group-hover:border-myRed/30 transition-colors">
                                                            <span className="truncate flex-1 text-left font-mono opacity-60 italic">{link}</span>
                                                            <button
                                                                onClick={() => {
                                                                    navigator.clipboard.writeText(link);
                                                                    showToast('Secure link copied!', 'success');
                                                                }}
                                                                className={`${templateId === 'valentine-motion-premium' ? 'bg-[#D4AF37] text-black' : 'bg-myRed text-white'} px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-lg`}
                                                            >
                                                                Copy
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div className="space-y-4">
                                                        <p className="text-[10px] font-black text-white/20 uppercase tracking-[0.4em]">Transmit Love via</p>
                                                        <div className="grid grid-cols-2 gap-4">
                                                            <button
                                                                onClick={() => window.open(`https://wa.me/?text=${encodeURIComponent(`Hey! I created a cinematic valentine experience for you. View it here: ${link}`)}`, '_blank')}
                                                                className="flex flex-col items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-green-500/10 hover:border-green-500/30 transition-all group/btn"
                                                            >
                                                                <div className="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center text-green-500 group-hover/btn:scale-110 transition-transform">
                                                                    <MessageCircle className="w-5 h-5 fill-current" />
                                                                </div>
                                                                <span className="text-[8px] font-black uppercase tracking-widest text-white/40 group-hover/btn:text-white">WhatsApp</span>
                                                            </button>
                                                            <button
                                                                onClick={() => window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(`Check out this cinematic valentine I created with @Apval!`)}&url=${encodeURIComponent(link)}`, '_blank')}
                                                                className="flex flex-col items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-blue-400/10 hover:border-blue-400/30 transition-all group/btn"
                                                            >
                                                                <div className="w-10 h-10 bg-blue-400/20 rounded-full flex items-center justify-center text-blue-400 group-hover/btn:scale-110 transition-transform">
                                                                    <svg className="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                                                                </div>
                                                                <span className="text-[8px] font-black uppercase tracking-widest text-white/40 group-hover/btn:text-white">X / Twitter</span>
                                                            </button>
                                                            <button
                                                                onClick={() => window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(link)}`, '_blank')}
                                                                className="flex flex-col items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-blue-600/10 hover:border-blue-600/30 transition-all group/btn"
                                                            >
                                                                <div className="w-10 h-10 bg-blue-600/20 rounded-full flex items-center justify-center text-blue-600 group-hover/btn:scale-110 transition-transform">
                                                                    <svg className="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.248h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                                                                </div>
                                                                <span className="text-[8px] font-black uppercase tracking-widest text-white/40 group-hover/btn:text-white">Facebook</span>
                                                            </button>
                                                            <button
                                                                onClick={async () => {
                                                                    if (navigator.share) {
                                                                        try {
                                                                            await navigator.share({
                                                                                title: 'Cinematic Valentine',
                                                                                text: 'I created a cinematic valentine experience for you!',
                                                                                url: link,
                                                                            });
                                                                        } catch (err) { }
                                                                    } else {
                                                                        navigator.clipboard.writeText(link);
                                                                        showToast('Discovery URL Copied!', 'success');
                                                                    }
                                                                }}
                                                                className="flex flex-col items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-myRed/10 hover:border-myRed/30 transition-all group/btn"
                                                            >
                                                                <div className="w-10 h-10 bg-myRed/20 rounded-full flex items-center justify-center text-myRed group-hover/btn:scale-110 transition-transform">
                                                                    <Share2 className="w-5 h-5" />
                                                                </div>
                                                                <span className="text-[8px] font-black uppercase tracking-widest text-white/40 group-hover/btn:text-white">Universal</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div className="flex gap-4 pt-6">
                                                    <button
                                                        onClick={() => {
                                                            onClose();
                                                            router.push('/dashboard');
                                                        }}
                                                        className="flex-1 py-5 bg-white/5 text-white/40 font-black uppercase tracking-widest text-[10px] rounded-2xl hover:bg-white/10 transition-all border border-white/5"
                                                    >
                                                        Exit to Dashboard
                                                    </button>
                                                    <Link href="/dashboard" className={`flex-1 py-5 ${templateId === 'valentine-motion-premium' ? 'bg-[#A82424]' : 'bg-myRed'} text-white font-black uppercase tracking-widest text-[10px] rounded-2xl transition-all flex items-center justify-center gap-2 shadow-2xl hover:scale-105 active:scale-95`}>
                                                        <span>View All Assets</span>
                                                        <ArrowRight className="w-4 h-4" />
                                                    </Link>
                                                </div>
                                            </motion.div>
                                        </motion.div>
                                    )}
                                </>
                            )}
                        </AnimatePresence>
                    </div>
                </div>
            </motion.div>
        </motion.div>
    );
}
