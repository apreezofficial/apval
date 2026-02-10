'use client';
import { motion } from 'framer-motion';
import { Link2, Crown, Sparkles } from 'lucide-react';
import { EditorData } from './types';

interface CustomSlugStepProps {
    data: EditorData & { customSlug?: string };
    onUpdate: (field: string, value: string) => void;
    onNext: () => void;
    isPremium: boolean;
    onUpgrade: () => void;
}

export default function CustomSlugStep({ data, onUpdate, onNext, isPremium, onUpgrade }: CustomSlugStepProps) {
    const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';
    const previewUrl = data.customSlug ? `${baseUrl}/v/${data.customSlug}` : `${baseUrl}/v/random-id`;

    const handleSlugChange = (value: string) => {
        // Convert to URL-friendly slug
        const slug = value
            .toLowerCase()
            .replace(/[^a-z0-9-]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        onUpdate('customSlug', slug);
    };

    return (
        <div className="w-full max-w-2xl mx-auto">
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="space-y-6"
            >
                <div className="text-center space-y-2">
                    <h2 className="text-3xl md:text-4xl font-black tracking-tight">
                        Custom URL <span className="text-myRed">Slug</span>
                    </h2>
                    <p className="text-black/60 font-medium">
                        Make your valentine card easy to remember and share
                    </p>
                </div>

                {!isPremium ? (
                    // Premium Upgrade Prompt
                    <motion.div
                        initial={{ scale: 0.95 }}
                        animate={{ scale: 1 }}
                        className="bg-gradient-to-br from-yellow-50 to-orange-50 border-2 border-yellow-300 rounded-3xl p-8 text-center space-y-6"
                    >
                        <div className="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-yellow-500/30">
                            <Crown className="w-8 h-8 text-white" />
                        </div>

                        <div className="space-y-2">
                            <h3 className="text-2xl font-black">Premium Feature</h3>
                            <p className="text-black/70">
                                Custom URL slugs are exclusive to Premium members. Upgrade for just ₦1,000 to unlock:
                            </p>
                        </div>

                        <div className="space-y-3 text-left max-w-sm mx-auto">
                            <div className="flex items-center gap-3">
                                <Sparkles className="w-5 h-5 text-yellow-600 flex-shrink-0" />
                                <span className="font-medium">Personalized URLs (e.g., /v/mylove)</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <Sparkles className="w-5 h-5 text-yellow-600 flex-shrink-0" />
                                <span className="font-medium">Up to 10 valentine cards (vs 3 free)</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <Sparkles className="w-5 h-5 text-yellow-600 flex-shrink-0" />
                                <span className="font-medium">Priority support</span>
                            </div>
                        </div>

                        <div className="flex gap-3 pt-4">
                            <button
                                onClick={onNext}
                                className="flex-1 bg-black/10 hover:bg-black/20 text-black font-bold py-4 px-8 rounded-2xl transition-colors"
                            >
                                Skip for Now
                            </button>
                            <button
                                onClick={onUpgrade}
                                className="flex-1 bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-yellow-500/30 transition-all flex items-center justify-center gap-2"
                            >
                                <Crown size={20} />
                                <span>Upgrade Now</span>
                            </button>
                        </div>
                    </motion.div>
                ) : (
                    // Custom Slug Input (Premium Only)
                    <div className="space-y-6">
                        <div className="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-4">
                            <div className="flex items-center gap-2 text-yellow-900">
                                <Crown className="w-5 h-5 text-yellow-600" />
                                <span className="font-bold text-sm">Premium Feature Active</span>
                            </div>
                        </div>

                        <div className="space-y-3">
                            <label className="block text-sm font-bold text-black/70">
                                Your Custom Slug
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                    <Link2 className="w-5 h-5 text-black/40" />
                                </div>
                                <input
                                    type="text"
                                    value={data.customSlug || ''}
                                    onChange={(e) => handleSlugChange(e.target.value)}
                                    placeholder="my-special-valentine"
                                    className="w-full pl-14 pr-6 py-5 bg-white border-2 border-black/10 rounded-2xl focus:border-myRed focus:outline-none transition-colors text-lg font-medium"
                                    maxLength={50}
                                />
                            </div>
                            <p className="text-sm text-black/50 italic">
                                Use lowercase letters, numbers, and hyphens only
                            </p>
                        </div>

                        {data.customSlug && (
                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                className="bg-black/5 rounded-2xl p-4"
                            >
                                <p className="text-xs uppercase tracking-wider text-black/40 mb-1">Preview URL</p>
                                <p className="text-lg font-mono font-bold text-myRed break-all">
                                    {previewUrl}
                                </p>
                            </motion.div>
                        )}

                        <button
                            onClick={onNext}
                            disabled={!data.customSlug || data.customSlug.length < 3}
                            className="w-full bg-black hover:bg-black/90 disabled:bg-black/30 text-white font-bold py-5 px-8 rounded-2xl transition-colors text-lg disabled:cursor-not-allowed"
                        >
                            Continue
                        </button>

                        <button
                            onClick={() => {
                                onUpdate('customSlug', '');
                                onNext();
                            }}
                            className="w-full text-black/40 hover:text-black/60 font-medium py-3 transition-colors text-sm"
                        >
                            Skip (Use Random URL)
                        </button>
                    </div>
                )}
            </motion.div>
        </div>
    );
}
