'use client';
import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Heart, ArrowRight, ArrowLeft, Send, User, MessageCircle, Sparkles, Phone, Music, Music2, Share2 } from 'lucide-react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { apiGet, apiPost, apiPut } from '@/lib/api';

interface MultiStepEditorProps {
    templateId: string;
    onClose: () => void;
    editId?: string;
}

import AmourView from './templates/AmourView';
import MinimalEliteView from './templates/MinimalEliteView';
import PremiumMotionView from './templates/PremiumMotionView';
import QuestValentineView from './templates/QuestValentineView';
import InteractiveDodgeView from './templates/InteractiveDodgeView';
import ClassicValentineView from './templates/ClassicValentineView';
import GamingEliteView from './templates/GamingEliteView';
import { useToast } from './Toast';

// Modular Step Components
import RecipientStep from './editor/steps/RecipientStep';
import ContentStep from './editor/steps/ContentStep';
import AttachmentStep from './editor/steps/AttachmentStep';
import MusicStep from './editor/steps/MusicStep';
import SignatureStep from './editor/steps/SignatureStep';
import GenderStep from './editor/steps/GenderStep';
import IntroMessagesStep from './editor/steps/IntroMessagesStep';
import IntroSequenceStep from './editor/steps/IntroSequenceStep';
import SuccessStep from './editor/steps/SuccessStep';

export default function MultiStepEditor({ templateId: initialTemplateId, onClose, editId }: MultiStepEditorProps) {
    const { showToast } = useToast();
    const [step, setStep] = useState(1);
    const [templateId, setTemplateId] = useState(initialTemplateId);
    const [fetchingData, setFetchingData] = useState(!!editId);
    const [data, setData] = useState({
        recipient: '',
        headline: '',
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
        // Interactive Dodge Specific
        gender: 'female',
        introMessages: ['Hey Beautiful...', "We've made so many memories...", "You make me smile every single day...", "So I have a question..."],
    });
    const [loading, setLoading] = useState(false);
    const [link, setLink] = useState('');
    const router = useRouter();

    useEffect(() => {
        if (editId) {
            setFetchingData(true);
            apiGet(`/valentines/${editId}`)
                .then(valData => {
                    if (valData) {
                        if (valData.templateId) {
                            setTemplateId(valData.templateId);
                        }
                        setData({
                            recipient: valData.recipient || '',
                            headline: valData.headline || '',
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
                            gender: valData.gender || 'female',
                            introMessages: valData.introMessages || ['Hey Beautiful...', "We've made so many memories...", "You make me smile every single day...", "So I have a question..."],
                        });
                    }
                })
                .finally(() => setFetchingData(false));
        }
    }, [editId]);



    const featureMap: Record<string, string[]> = {
        'valentine-motion-premium': ['recipient_headline', 'intro', 'content', 'attachment', 'audio', 'signature'],
        'amour': ['recipient_headline', 'content', 'attachment', 'audio', 'signature'],
        'minimal-elite-card': ['recipient_headline', 'content', 'signature'],
        'quest-valentine': ['recipient', 'content', 'audio', 'signature'],
        'interactive-dodge': ['recipient', 'gender', 'proposal_intro', 'audio', 'signature'],
        'classic-valentine': ['recipient', 'content', 'audio', 'signature'],
        'gaming-elite': ['recipient_headline', 'content', 'audio', 'signature'],
        'premium-mockup-card': ['recipient', 'content', 'attachment', 'audio', 'signature']
    };

    const features = featureMap[templateId] || ['recipient', 'signature'];
    const totalSteps = features.length;
    const currentFeature = features[step - 1];

    const handleFinish = async () => {
        setLoading(true);
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        try {
            const payload = { ...data, templateId, userId: user.id };
            const result = editId
                ? await apiPut('/valentines', { ...payload, id: editId })
                : await apiPost('/valentines', payload);

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

    const handleNext = () => setStep(step + 1);
    const handleBack = () => setStep(step - 1);


    const stepsForPreview = features.map(f => ({ type: f }));

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto pt-10 pb-10 px-4"
        >

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
                                    key={step} // Force internal step update
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
                            ) : templateId === 'interactive-dodge' ? (
                                <div className="flex-1 pointer-events-none scale-[0.7] md:scale-100 origin-center">
                                    <InteractiveDodgeView data={data} isPreview />
                                </div>
                            ) : templateId === 'classic-valentine' ? (
                                <div className="flex-1 pointer-events-none scale-[0.7] md:scale-100 origin-center">
                                    <ClassicValentineView data={data} isPreview />
                                </div>
                            ) : templateId === 'gaming-elite' ? (
                                <div className="flex-1 pointer-events-none scale-[0.7] md:scale-100 origin-center">
                                    <GamingEliteView data={data} isPreview />
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
                                                {data.headline || 'Your message for'} {data.recipient || '...'}
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
                                    {(currentFeature === 'recipient_headline' || currentFeature === 'recipient') && (
                                        <RecipientStep
                                            key="step-rec"
                                            data={data}
                                            onUpdate={(updates) => setData({ ...data, ...updates })}
                                            onNext={handleNext}
                                            showHeadline={currentFeature === 'recipient_headline'}
                                        />
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
                                                <button
                                                    disabled={!data.introQuote1Line1 || !data.introQuote1Line2 || !data.introQuote2Line1 || !data.introQuote2Line2}
                                                    onClick={handleNext}
                                                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 disabled:opacity-50"
                                                >
                                                    <span>Continue</span>
                                                    <ArrowRight className="w-5 h-5" />
                                                </button>
                                            </div>
                                        </motion.div>
                                    )}

                                    {currentFeature === 'gender' && (
                                        <GenderStep
                                            key="step-gender"
                                            data={data}
                                            onUpdate={(updates) => setData({ ...data, ...updates })}
                                            onNext={handleNext}
                                            onBack={handleBack}
                                        />
                                    )}

                                    {currentFeature === 'proposal_intro' && (
                                        <IntroMessagesStep
                                            key="step-proposal-intro"
                                            data={data}
                                            onUpdate={(updates) => setData({ ...data, ...updates })}
                                            onNext={handleNext}
                                            onBack={handleBack}
                                        />
                                    )}

                                    {currentFeature === 'content' && (
                                        <ContentStep
                                            key="step-content"
                                            data={data}
                                            onUpdate={(updates) => setData({ ...data, ...updates })}
                                            onNext={handleNext}
                                            onBack={handleBack}
                                            showExtraFields={templateId === 'valentine-motion-premium'}
                                        />
                                    )}



                                    {currentFeature === 'attachment' && (
                                        <AttachmentStep
                                            key="step-attachment"
                                            data={data}
                                            onUpdate={(updates) => setData({ ...data, ...updates })}
                                            onNext={handleNext}
                                            onBack={handleBack}
                                        />
                                    )}

                                    {currentFeature === 'audio' && (
                                        <MusicStep
                                            key="step-audio"
                                            data={data}
                                            onUpdate={(updates) => setData({ ...data, ...updates })}
                                            onNext={handleNext}
                                            onBack={handleBack}
                                        />
                                    )}

                                    {currentFeature === 'signature' && (
                                        <SignatureStep
                                            key="step-sig"
                                            data={data}
                                            onUpdate={(updates) => setData({ ...data, ...updates })}
                                            onFinish={handleFinish}
                                            onBack={handleBack}
                                            loading={loading}
                                            showButtonText={templateId === 'valentine-motion-premium'}
                                            showWhatsapp={templateId === 'amour' || templateId === 'quest-valentine' || templateId === 'interactive-dodge' || templateId === 'classic-valentine' || templateId === 'gaming-elite'}
                                        />
                                    )}

                                    {step === (totalSteps + 1) && (
                                        <SuccessStep
                                            key="success"
                                            templateId={templateId}
                                            link={link}
                                            onClose={() => {
                                                onClose();
                                                router.push('/dashboard');
                                            }}
                                            showToast={showToast}
                                        />
                                    )}
                                </>
                            )}
                        </AnimatePresence>
                    </div>
                </div>
            </motion.div>
        </motion.div >
    );
}
