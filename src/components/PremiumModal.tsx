'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Heart, Check, Copy, Sparkles, Zap, Upload } from 'lucide-react';
import { useState } from 'react';
import { useToast } from '@/components/Toast';

interface PremiumModalProps {
    isOpen: boolean;
    onClose: () => void;
    reason?: 'limit' | 'slug' | 'upgrade';
}

export default function PremiumModal({ isOpen, onClose, reason = 'upgrade' }: PremiumModalProps) {
    const [copied, setCopied] = useState(false);
    const [showPayment, setShowPayment] = useState(false);
    const [uploading, setUploading] = useState(false);
    const [file, setFile] = useState<File | null>(null);
    const { showToast } = useToast();

    const accountNumber = "9064779856";
    // ... (rest of constants)

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setFile(e.target.files[0]);
        }
    };

    const handleUpload = async () => {
        if (!file) return;

        setUploading(true);

        try {
            // Compress image
            const resizeImage = (file: File): Promise<string> => {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = (event) => {
                        const img = new Image();
                        img.src = event.target?.result as string;
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            let width = img.width;
                            let height = img.height;
                            const MAX_WIDTH = 1000;
                            const MAX_HEIGHT = 1000;

                            if (width > height) {
                                if (width > MAX_WIDTH) {
                                    height *= MAX_WIDTH / width;
                                    width = MAX_WIDTH;
                                }
                            } else {
                                if (height > MAX_HEIGHT) {
                                    width *= MAX_HEIGHT / height;
                                    height = MAX_HEIGHT;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx?.drawImage(img, 0, 0, width, height);

                            // Convert to JPEG with 0.7 quality (good balance)
                            resolve(canvas.toDataURL('image/jpeg', 0.7));
                        };
                        img.onerror = reject;
                    };
                    reader.onerror = reject;
                });
            };

            const base64 = await resizeImage(file);

            // Get user from localStorage
            const userStr = localStorage.getItem('user');
            if (!userStr) {
                alert("Please login first");
                return;
            }
            const user = JSON.parse(userStr);

            if (!user.email) {
                alert("Session incomplete. Please logout and login again to refresh your account details.");
                return;
            }

            const res = await fetch('/api/upgrade', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    userId: user.id,
                    userEmail: user.email,
                    receipt: base64,
                    timestamp: new Date().toISOString()
                })
            });

            if (res.ok) {
                alert("Receipt uploaded successfully! Pending admin approval."); // Replace with toast if available
                setShowPayment(false);
                onClose();
            } else {
                const errorData = await res.json();
                alert(`Failed to upload: ${errorData.error || 'Unknown error'}`);
            }
        } catch (error) {
            console.error("Upload error:", error);
            alert("An error occurred during upload.");
        } finally {
            setUploading(false);
        }
    };

    // ... (rest of component)

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
                return "You've reached the free limit. Upgrade to Premium to create more valentine magic! 💝";
            case 'slug':
                return "Custom URLs are a Premium feature. Make your love letters unforgettable with personalized links! 💌";
            default:
                return "Unlock the full power of digital romance and create something extraordinary! ❤️";
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
                        className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50"
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
                            className="bg-[#0A0A0A] border border-white/10 rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden relative"
                        >
                            {/* Close Button */}
                            <button
                                onClick={onClose}
                                className="absolute top-6 right-6 w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center transition-colors z-10"
                            >
                                <X size={20} className="text-white" />
                            </button>

                            {!showPayment ? (
                                // Premium Features Screen
                                <div className="p-8">
                                    <div className="text-center mb-8">
                                        <motion.div
                                            animate={{
                                                scale: [1, 1.1, 1],
                                            }}
                                            transition={{
                                                duration: 2,
                                                repeat: Infinity,
                                                ease: "easeInOut"
                                            }}
                                            className="w-20 h-20 bg-myRed/20 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-myRed/30 border border-myRed/20"
                                        >
                                            <Heart className="w-10 h-10 text-myRed fill-current" />
                                        </motion.div>
                                        <h2 className="text-3xl font-black tracking-tight text-white mb-2">
                                            Upgrade to <span className="text-myRed">Premium</span>
                                        </h2>
                                        <p className="text-white/60 font-medium">
                                            {getMessage()}
                                        </p>
                                    </div>

                                    {/* Comparison */}
                                    <div className="grid grid-cols-2 gap-4 mb-8">
                                        {/* Free Tier */}
                                        <div className="bg-white/5 border border-white/10 rounded-2xl p-6">
                                            <h3 className="font-bold text-lg mb-4 text-center text-white/80">Free</h3>
                                            <div className="space-y-3 text-sm text-white/60">
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-green-500 mt-0.5 flex-shrink-0" />
                                                    <span>3 Valcards</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <X size={16} className="text-red-500 mt-0.5 flex-shrink-0" />
                                                    <span>Random URLs only</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-green-500 mt-0.5 flex-shrink-0" />
                                                    <span>All templates</span>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Premium Tier */}
                                        <div className="bg-gradient-to-br from-myRed/10 to-myRed/5 rounded-2xl p-6 border-2 border-myRed/30 relative overflow-hidden">
                                            <div className="absolute top-2 right-2">
                                                <Sparkles size={16} className="text-myRed animate-pulse" />
                                            </div>
                                            <h3 className="font-bold text-lg mb-1 text-center text-white">Premium</h3>
                                            <p className="text-center text-myRed font-black text-2xl mb-3">{amount}</p>
                                            <div className="space-y-3 text-sm text-white/80">
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-myRed mt-0.5 flex-shrink-0" />
                                                    <span className="font-semibold">10 Valcards</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-myRed mt-0.5 flex-shrink-0" />
                                                    <span className="font-semibold">Custom URLs</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <Check size={16} className="text-myRed mt-0.5 flex-shrink-0" />
                                                    <span>All templates</span>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <Zap size={16} className="text-myRed mt-0.5 flex-shrink-0" />
                                                    <span className="font-semibold">Priority support</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        onClick={() => setShowPayment(true)}
                                        className="w-full bg-myRed hover:bg-myRed/90 text-white font-black text-lg py-4 px-8 rounded-2xl shadow-lg shadow-myRed/30 hover:shadow-xl hover:scale-105 transition-all flex items-center justify-center gap-2"
                                    >
                                        <Heart size={20} className="fill-current" />
                                        <span>Upgrade Now - {amount}</span>
                                    </button>
                                </div>
                            ) : (
                                // Payment Screen
                                <div className="p-8">
                                    <div className="text-center mb-8">
                                        <div className="w-20 h-20 bg-myRed/20 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-myRed/30 border border-myRed/20">
                                            <svg viewBox="0 0 24 24" className="w-10 h-10 text-myRed" fill="none" stroke="currentColor" strokeWidth="2">
                                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                                <line x1="2" y1="10" x2="22" y2="10" />
                                            </svg>
                                        </div>
                                        <h2 className="text-2xl font-black tracking-tight text-white mb-2">Complete Payment</h2>
                                        <p className="text-white/60 font-medium">Transfer to the account below</p>
                                    </div>

                                    <div className="bg-gradient-to-br from-myRed/10 to-myRed/5 rounded-2xl p-6 border border-myRed/20 mb-6">
                                        <div className="text-center mb-6">
                                            <p className="text-sm text-white/60 mb-2">Amount to pay</p>
                                            <p className="text-5xl font-black text-myRed">
                                                {amount}
                                            </p>
                                        </div>

                                        <div className="space-y-4 bg-[#0A0A0A] rounded-xl p-4 border border-white/10">
                                            <div>
                                                <p className="text-xs uppercase tracking-wider text-white/40 mb-1">Bank Name</p>
                                                <p className="text-lg font-bold text-white">{bankName}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs uppercase tracking-wider text-white/40 mb-1">Account Name</p>
                                                <p className="text-lg font-bold text-white">{accountName}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs uppercase tracking-wider text-white/40 mb-1">Account Number</p>
                                                <div className="flex items-center justify-between bg-white/5 rounded-lg p-3 border border-white/10">
                                                    <p className="text-2xl font-black tracking-wider text-white">{accountNumber}</p>
                                                    <button
                                                        onClick={() => copyToClipboard(accountNumber)}
                                                        className="p-2 hover:bg-myRed/10 rounded-lg transition-colors"
                                                    >
                                                        {copied ? (
                                                            <Check size={20} className="text-myRed" />
                                                        ) : (
                                                            <Copy size={20} className="text-white/60" />
                                                        )}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex flex-col gap-3">
                                        <div className="bg-white/5 border border-dashed border-white/20 rounded-xl p-4 text-center cursor-pointer hover:bg-white/10 transition-colors" onClick={() => document.getElementById('receipt-upload')?.click()}>
                                            <input
                                                type="file"
                                                id="receipt-upload"
                                                className="hidden"
                                                accept="image/*"
                                                onChange={handleFileChange}
                                            />
                                            <Upload className="mx-auto w-8 h-8 text-white/40 mb-2" />
                                            {file ? (
                                                <p className="text-sm font-medium text-myRed">{file.name}</p>
                                            ) : (
                                                <p className="text-sm text-white/60">Click to upload payment receipt</p>
                                            )}
                                        </div>

                                        <div className="flex gap-3">
                                            <button
                                                onClick={() => setShowPayment(false)}
                                                className="flex-1 bg-white/5 hover:bg-white/10 text-white font-bold py-3 px-6 rounded-xl transition-colors border border-white/10"
                                                disabled={uploading}
                                            >
                                                Back
                                            </button>
                                            <button
                                                onClick={handleUpload}
                                                disabled={!file || uploading}
                                                className={`flex-1 font-bold py-3 px-6 rounded-xl transition-colors text-center flex items-center justify-center gap-2 shadow-lg shadow-myRed/30 ${!file || uploading ? 'bg-white/10 text-white/40 cursor-not-allowed' : 'bg-myRed hover:bg-myRed/90 text-white'}`}
                                            >
                                                {uploading ? (
                                                    <Sparkles className="animate-spin w-5 h-5" />
                                                ) : (
                                                    <Heart size={16} className="fill-current" />
                                                )}
                                                {uploading ? 'Uploading...' : 'Submit Receipt'}
                                            </button>
                                        </div>
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
