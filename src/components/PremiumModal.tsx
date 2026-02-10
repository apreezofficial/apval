'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Crown, Check, Copy, Sparkles, Zap } from 'lucide-react';
import { useState } from 'react';

interface PremiumModalProps {
    isOpen: boolean;
    onClose: () => void;
    reason?: 'limit' | 'slug' | 'upgrade';
}

export default function PremiumModal({ isOpen, onClose, reason = 'upgrade' }: PremiumModalProps) {
    const [copied, setCopied] = useState(false);
    const [showPayment, setShowPayment] = useState(false);

    const accountNumber = "9064779856";
    const accountName = "Precious Adedokun";
    const bankName = "Moniepoint";
    const amount = "₦1,000";

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const getMessage = () => {
        switch (reason) {
            case 'limit':
                return "You've reached your free tier limit of 3 valcards. Upgrade to Premium to create up to 10!";
            case 'slug':
                return "Custom slugs are a Premium feature. Upgrade to create personalized URLs!";
            default:
                return "Unlock Premium features and create more amazing valentines!";
        }
    };

    return (
        <AnimatePresence>
            {isOpen && (
                <>
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50"
                        onClick={onClose}
                    />
                    <motion.div
                        initial={{ opacity: 0, scale: 0.9, y: 20 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.9, y: 20 }}
                        className="fixed inset-0 z-50 flex items-center justify-center p-4"
                        onClick={onClose}
                    >
                        <motion.div
                            onClick={(e) => e.stopPropagation()}
                            className="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden relative"
                        >
                            {/* Close Button */}
                            <button
                                onClick={onClose}
                                className="absolute top-6 right-6 w-10 h-10 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center transition-colors z-10"
                            >
                                <X size={20} />
                            </button>

                            {!showPayment ? (
                                // Premium Features Screen
                                <div className="p-8">
                                    <div className="text-center mb-8">
                                        <div className="w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-yellow-500/30">
                                            <Crown className="w-10 h-10 text-white" />
                                        </div>
                                        <h2 className="text-3xl font-black tracking-tight mb-2">
                                            Upgrade to <span className="bg-gradient-to-r from-yellow-500 to-orange-600 bg-clip-text text-transparent">Premium</span>
                                        </h2>
                                        <p className="text-black/60 font-medium">
                                            {getMessage()}
                                        </p>
                                    </div>

                                    {/* Comparison */}
                                    <div className="grid grid-cols-2 gap-4 mb-8">
                                        {/* Free Tier */}
                                        <div className="bg-gray-50 rounded-2xl p-6 border-2 border-gray-200">
                                            <h3 className="font-bold text-lg mb-4 text-center">Free</h3>
                                            <div className="space-y-3 text-sm">
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-green-600 mt-0.5 flex-shrink-0" />
                                                    <span>3 Valcards</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <X size={16} className="text-red-500 mt-0.5 flex-shrink-0" />
                                                    <span>Random URLs only</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-green-600 mt-0.5 flex-shrink-0" />
                                                    <span>All templates</span>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Premium Tier */}
                                        <div className="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl p-6 border-2 border-yellow-400 relative overflow-hidden">
                                            <div className="absolute top-2 right-2">
                                                <Sparkles size={16} className="text-yellow-600" />
                                            </div>
                                            <h3 className="font-bold text-lg mb-1 text-center">Premium</h3>
                                            <p className="text-center text-yellow-700 font-black text-2xl mb-3">₦1,000</p>
                                            <div className="space-y-3 text-sm">
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-green-600 mt-0.5 flex-shrink-0" />
                                                    <span className="font-semibold">10 Valcards</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-green-600 mt-0.5 flex-shrink-0" />
                                                    <span className="font-semibold">Custom URLs</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-green-600 mt-0.5 flex-shrink-0" />
                                                    <span>All templates</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <Zap size={16} className="text-yellow-600 mt-0.5 flex-shrink-0" />
                                                    <span className="font-semibold">Priority support</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        onClick={() => setShowPayment(true)}
                                        className="w-full bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-white font-black text-lg py-4 px-8 rounded-2xl shadow-lg shadow-yellow-500/30 hover:shadow-xl transition-all flex items-center justify-center gap-2"
                                    >
                                        <Crown size={20} />
                                        <span>Upgrade Now - ₦1,000</span>
                                    </button>
                                </div>
                            ) : (
                                // Payment Screen (Monnify-style)
                                <div className="p-8">
                                    <div className="text-center mb-8">
                                        <div className="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/30">
                                            <svg viewBox="0 0 24 24" className="w-10 h-10 text-white" fill="none" stroke="currentColor" strokeWidth="2">
                                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                                <line x1="2" y1="10" x2="22" y2="10" />
                                            </svg>
                                        </div>
                                        <h2 className="text-2xl font-black tracking-tight mb-2">Complete Payment</h2>
                                        <p className="text-black/60 font-medium">Transfer to the account below</p>
                                    </div>

                                    <div className="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 border-2 border-blue-200 mb-6">
                                        <div className="text-center mb-6">
                                            <p className="text-sm text-black/60 mb-2">Amount to pay</p>
                                            <p className="text-5xl font-black bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                                {amount}
                                            </p>
                                        </div>

                                        <div className="space-y-4 bg-white rounded-xl p-4">
                                            <div>
                                                <p className="text-xs uppercase tracking-wider text-black/40 mb-1">Bank Name</p>
                                                <p className="text-lg font-bold">{bankName}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs uppercase tracking-wider text-black/40 mb-1">Account Name</p>
                                                <p className="text-lg font-bold">{accountName}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs uppercase tracking-wider text-black/40 mb-1">Account Number</p>
                                                <div className="flex items-center justify-between bg-black/5 rounded-lg p-3">
                                                    <p className="text-2xl font-black tracking-wider">{accountNumber}</p>
                                                    <button
                                                        onClick={() => copyToClipboard(accountNumber)}
                                                        className="p-2 hover:bg-white rounded-lg transition-colors"
                                                    >
                                                        {copied ? (
                                                            <Check size={20} className="text-green-600" />
                                                        ) : (
                                                            <Copy size={20} className="text-black/60" />
                                                        )}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                                        <p className="text-sm text-yellow-900 font-medium">
                                            ⚡ After payment, send proof (screenshot) to <span className="font-bold">+2349064779856</span> on WhatsApp for instant activation!
                                        </p>
                                    </div>

                                    <div className="flex gap-3">
                                        <button
                                            onClick={() => setShowPayment(false)}
                                            className="flex-1 bg-black/5 hover:bg-black/10 text-black font-bold py-3 px-6 rounded-xl transition-colors"
                                        >
                                            Back
                                        </button>
                                        <a
                                            href={`https://wa.me/2349064779856?text=${encodeURIComponent('Hi! I just made a payment of ₦1,000 for Apval Premium. Here is my proof of payment.')}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="flex-1 bg-[#25D366] hover:bg-[#20BA5A] text-white font-bold py-3 px-6 rounded-xl transition-colors text-center"
                                        >
                                            Send Proof
                                        </a>
                                    </div>
                                </div>
                            )}
                        </motion.div>
                    </motion.div>
                </>
            )}
        </AnimatePresence>
    );
}
