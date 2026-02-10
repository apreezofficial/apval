'use client';
import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Save, Share2, Heart, Type, Image as ImageIcon, Send } from 'lucide-react';
import { apiPost } from '@/lib/api';

interface EditorProps {
    templateId: string;
    onClose: () => void;
}

export default function Editor({ templateId, onClose }: EditorProps) {
    const [data, setData] = useState({
        recipient: '',
        message: '',
        sender: '',
    });

    const [isGenerating, setIsGenerating] = useState(false);
    const [generatedLink, setGeneratedLink] = useState('');

    const handleGenerate = async () => {
        setIsGenerating(true);
        try {
            const result = await apiPost('/valentines', { ...data, templateId });
            if (result.id) {
                const link = `${window.location.origin}/v/${result.id}`;
                setGeneratedLink(link);
            }
        } catch (error) {
            console.error('Failed to generate link', error);
        } finally {
            setIsGenerating(false);
        }
    };

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-10 bg-brown-dark/40 backdrop-blur-md"
        >
            <motion.div
                initial={{ scale: 0.9, y: 20 }}
                animate={{ scale: 1, y: 0 }}
                className="bg-cream-light w-full max-w-6xl h-full max-h-[900px] rounded-[40px] shadow-2xl overflow-hidden flex flex-col md:flex-row shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)]"
            >
                {/* Preview Side */}
                <div className="flex-1 bg-brown-dark relative overflow-hidden flex items-center justify-center p-12">
                    <div className="absolute inset-0 bg-gradient-to-br from-brown-light/20 to-accent/10" />

                    {/* Live Preview Render */}
                    <div className="w-full max-w-sm aspect-[3/4] bg-white rounded-3xl shadow-2xl p-10 flex flex-col items-center justify-center text-center space-y-6 relative group">
                        <div className="w-16 h-16 bg-accent/10 rounded-full flex items-center justify-center">
                            <Heart className="w-8 h-8 text-accent fill-current" />
                        </div>
                        <h4 className="text-sm font-bold text-accent uppercase tracking-widest leading-none">To my dearest</h4>
                        <h2 className="text-3xl font-black text-brown-dark leading-tight break-words w-full">
                            {data.recipient || 'Recipient Name'}
                        </h2>
                        <p className="text-brown-dark/60 font-medium text-lg italic italic">
                            "{data.message || 'Your beautiful message goes here...'}"
                        </p>
                        <div className="pt-4">
                            <p className="text-xs font-bold text-brown-dark/40 uppercase tracking-[0.2em]">With love from</p>
                            <p className="text-lg font-bold text-brown-dark">{data.sender || 'Your Name'}</p>
                        </div>
                    </div>

                    <button
                        onClick={onClose}
                        className="absolute top-8 left-8 p-3 bg-white/10 hover:bg-white/20 rounded-2xl transition-colors group"
                    >
                        <X className="w-6 h-6 text-white" />
                    </button>
                </div>

                {/* Controls Side */}
                <div className="w-full md:w-[450px] bg-white p-10 md:p-16 flex flex-col gap-10 overflow-y-auto">
                    <div className="space-y-2">
                        <h2 className="text-3xl font-black text-brown-dark tracking-tight">Personalize</h2>
                        <p className="text-brown-dark/50 font-medium">Create your unique Valentine's link</p>
                    </div>

                    <div className="space-y-8 flex-1">
                        <div className="space-y-3">
                            <label className="text-xs font-black text-brown-dark/40 uppercase tracking-widest flex items-center gap-2">
                                <Type className="w-4 h-4" /> Recipient Name
                            </label>
                            <input
                                type="text"
                                value={data.recipient}
                                onChange={(e) => setData({ ...data, recipient: e.target.value })}
                                placeholder="e.g. Sarah J. Parker"
                                className="w-full px-6 py-4 bg-brown-dark/5 rounded-2xl border border-transparent focus:border-accent/30 focus:bg-white outline-none transition-all font-bold text-brown-dark text-lg"
                            />
                        </div>

                        <div className="space-y-3">
                            <label className="text-xs font-black text-brown-dark/40 uppercase tracking-widest flex items-center gap-2">
                                <Send className="w-4 h-4" /> Your Message
                            </label>
                            <textarea
                                value={data.message}
                                onChange={(e) => setData({ ...data, message: e.target.value })}
                                placeholder="The words of your heart..."
                                rows={4}
                                className="w-full px-6 py-4 bg-brown-dark/5 rounded-2xl border border-transparent focus:border-accent/30 focus:bg-white outline-none transition-all font-bold text-brown-dark text-lg resize-none"
                            />
                        </div>

                        <div className="space-y-3">
                            <label className="text-xs font-black text-brown-dark/40 uppercase tracking-widest flex items-center gap-2">
                                <Heart className="w-4 h-4" /> Your Name
                            </label>
                            <input
                                type="text"
                                value={data.sender}
                                onChange={(e) => setData({ ...data, sender: e.target.value })}
                                placeholder="e.g. John Doe"
                                className="w-full px-6 py-4 bg-brown-dark/5 rounded-2xl border border-transparent focus:border-accent/30 focus:bg-white outline-none transition-all font-bold text-brown-dark text-lg"
                            />
                        </div>
                    </div>

                    <div className="pt-6 space-y-4">
                        <button
                            onClick={handleGenerate}
                            disabled={isGenerating || !data.recipient || !data.sender}
                            className="w-full py-5 bg-brown-dark text-white font-black rounded-[24px] shadow-2xl hover:bg-accent transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3 text-lg uppercase tracking-widest"
                        >
                            {isGenerating ? (
                                <>
                                    <div className="w-5 h-5 border-4 border-white/20 border-t-white rounded-full animate-spin" />
                                    <span>Generating...</span>
                                </>
                            ) : (
                                <>
                                    <Share2 className="w-6 h-6" />
                                    <span>Generate Link</span>
                                </>
                            )}
                        </button>

                        <AnimatePresence>
                            {generatedLink && (
                                <motion.div
                                    initial={{ opacity: 0, y: 10 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className="p-4 bg-accent/10 border border-accent/20 rounded-2xl space-y-3"
                                >
                                    <p className="text-[10px] font-black text-accent uppercase tracking-widest">Share this unique link:</p>
                                    <div className="flex items-center gap-2">
                                        <input
                                            readOnly
                                            value={generatedLink}
                                            className="flex-1 bg-white/50 border-none outline-none text-xs font-bold text-brown-dark py-2 px-3 rounded-lg overflow-hidden whitespace-nowrap"
                                        />
                                        <button
                                            onClick={() => navigator.clipboard.writeText(generatedLink)}
                                            className="p-2 bg-accent text-white rounded-xl hover:bg-brown-dark transition-colors"
                                        >
                                            <Save className="w-4 h-4" />
                                        </button>
                                    </div>
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </div>
                </div>
            </motion.div>
        </motion.div>
    );
}
