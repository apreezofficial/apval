import { Template } from '@/data/templates';
import { motion } from 'framer-motion';
import { Heart, CreditCard, Layout, Lock as LucideLock } from 'lucide-react';

interface TemplateCardProps {
    template: Template;
}

export default function TemplateCard({ template }: TemplateCardProps) {
    const isWebsite = template.category === 'website';

    return (
        <motion.div
            whileHover={{ y: -10 }}
            className="group relative overflow-hidden bg-[#0A0A0A] border border-white/5 rounded-[40px] aspect-[4/5] flex flex-col p-8 transition-all hover:border-myRed/30 hover:shadow-[0_20px_40px_-15px_rgba(252,65,0,0.1)] cursor-pointer"
        >
            {/* Background Glow */}
            <div
                className="absolute top-0 right-0 w-32 h-32 blur-[80px] opacity-20 transition-opacity group-hover:opacity-40"
                style={{ backgroundColor: template.previewColor }}
            />

            {/* Icon Group */}
            <div className="flex items-center justify-between mb-8">
                <div className="w-14 h-14 bg-white/[0.03] border border-white/5 rounded-2xl flex items-center justify-center transition-colors group-hover:bg-myRed/10 group-hover:border-myRed/20">
                    {isWebsite ? (
                        <Layout className="w-6 h-6 text-white/40 group-hover:text-myRed transition-colors" />
                    ) : (
                        <CreditCard className="w-6 h-6 text-white/40 group-hover:text-myRed transition-colors" />
                    )}
                </div>
                <div className="px-4 py-1.5 rounded-full bg-white/[0.03] border border-white/5 text-[10px] font-bold text-white/30 uppercase tracking-[0.2em] group-hover:text-myRed group-hover:border-myRed/20 transition-all">
                    {isWebsite ? 'Website Engine' : 'Card Asset'}
                </div>
            </div>

            {/* Graphic Preview */}
            <div className="flex-1 flex items-center justify-center py-4 perspective-1000">
                <div className="relative w-full h-full max-h-40 flex items-center justify-center transition-all duration-700 group-hover:scale-110">
                    {template.id === 'amour' ? (
                        /* Website / Amour Preview (Browser Mock) */
                        <div className="w-full max-w-[200px] aspect-video bg-[#050505] rounded-xl border border-white/10 shadow-2xl overflow-hidden relative group-hover:-rotate-2 transition-transform">
                            <div className="h-3 border-b border-white/5 bg-white/5 flex items-center px-2 gap-1">
                                <div className="w-1 h-1 rounded-full bg-white/20" />
                                <div className="w-1 h-1 rounded-full bg-white/20" />
                                <div className="w-1 h-1 rounded-full bg-white/20" />
                            </div>
                            <div className="p-2 space-y-2 opacity-40">
                                <div className="h-1 w-1/2 bg-myRed/50 rounded-full" />
                                <div className="h-8 w-full bg-white/5 rounded-lg flex items-center justify-center">
                                    <Heart size={12} className="text-myRed fill-current" />
                                </div>
                                <div className="grid grid-cols-2 gap-1">
                                    <div className="h-4 bg-white/5 rounded-md" />
                                    <div className="h-4 bg-white/5 rounded-md" />
                                </div>
                            </div>
                        </div>
                    ) : template.id === 'minimal-elite-card' ? (
                        /* Minimal Elite Preview (Phone Mock - Champagne Royale Redesign) */
                        <div className="w-[120px] aspect-[9/16] bg-[#FFF9F2] rounded-[24px] border-[4px] border-[#C5A059]/30 shadow-2xl p-4 flex flex-col items-center justify-center space-y-3 relative group-hover:rotate-3 transition-transform">
                            {/* Royal Lock Motif for gallery */}
                            <div className="relative">
                                <div className="w-10 h-9 bg-gradient-to-br from-[#C5A059] to-[#8E6E3E] rounded-lg flex items-center justify-center shadow-lg border border-white/20">
                                    <LucideLock size={12} className="text-[#3D2B1F]" />
                                </div>
                                <div className="absolute -top-4 left-1/2 -translate-x-1/2 w-6 h-6 border-[4px] border-[#3D2B1F] rounded-t-full" />
                            </div>
                            <div className="space-y-1 w-full flex flex-col items-center">
                                <div className="h-1.5 w-3/4 bg-[#3D2B1F]/20 rounded-full" />
                                <div className="h-1.5 w-1/2 bg-[#3D2B1F]/10 rounded-full" />
                            </div>
                            <div className="absolute bottom-4 w-5 h-5 bg-[#3D2B1F] rounded-full flex items-center justify-center border-2 border-[#C5A059] shadow-md">
                                <Heart size={8} className="text-[#C5A059] fill-current" />
                            </div>
                        </div>
                    ) : template.id === 'valentine-motion-premium' ? (
                        /* Premium Motion Preview (Luxury Gold/Red Theme) */
                        <div className="w-[120px] aspect-[9/16] bg-[#050505] rounded-[24px] border-[4px] border-white/10 shadow-2xl p-4 flex flex-col items-center justify-center space-y-3 relative group-hover:scale-105 transition-transform overflow-hidden">
                            {/* Animated Heart Canvas Mockup */}
                            <div className="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-red-500/20 via-transparent to-transparent" />
                            <div className="relative z-10 w-full flex flex-col items-center gap-2">
                                <div className="text-[8px] font-playfair italic text-[#D4AF37] opacity-60">Happy</div>
                                <div className="text-xl font-great-vibes text-white leading-none">Valentine's</div>
                                <div className="w-10 h-10 mt-4 rounded-full bg-gradient-to-tr from-red-600 to-red-400 flex items-center justify-center border border-white/20 shadow-[0_0_15px_rgba(220,38,38,0.5)]">
                                    <Heart size={10} className="text-white fill-current" />
                                </div>
                            </div>
                            {/* Mini "Floating Hearts" */}
                            <div className="absolute top-4 left-4 w-1.5 h-1.5 rounded-full bg-[#D4AF37] opacity-40 blur-[1px]" />
                            <div className="absolute bottom-8 right-6 w-1 h-1 rounded-full bg-red-500 opacity-60 blur-[1px]" />
                        </div>
                    ) : template.id === 'quest-valentine' ? (
                        /* Quest Valentine Preview (Interactive Prank Theme) */
                        <div className="w-[120px] aspect-[9/16] bg-[#fff5f5] rounded-[24px] border-[4px] border-[#ff4b4b]/20 shadow-2xl p-4 flex flex-col items-center justify-center space-y-3 relative group-hover:scale-105 transition-transform overflow-hidden">
                            <div className="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_#ff4b4b_0%,_transparent_70%)]" />
                            <div className="w-16 h-16 bg-white rounded-xl shadow-lg border border-[#ff4b4b]/10 flex items-center justify-center overflow-hidden">
                                <img src="https://media.tenor.com/IC92C6pE88AAAAAi/be-my-valentine.gif" className="w-full h-full object-contain" alt="Bear Preview" />
                            </div>
                            <div className="space-y-1.5 w-full flex flex-col items-center">
                                <div className="h-1.5 w-3/4 bg-[#ff4b4b]/40 rounded-full" />
                                <div className="h-1.5 w-1/2 bg-[#ff4b4b]/20 rounded-full" />
                            </div>
                            <div className="flex gap-2 mt-2">
                                <div className="w-8 h-4 bg-[#32cd32]/30 rounded-full" />
                                <div className="w-8 h-4 bg-[#ff4b4b]/30 rounded-full" />
                            </div>
                        </div>
                    ) : template.id === 'interactive-dodge' ? (
                        /* Interactive Dodge Preview (Prank/Choice UI) */
                        <div className="w-[120px] aspect-[9/16] bg-gradient-to-br from-[#FFF0F7] to-[#FFE4F0] rounded-[24px] border-[4px] border-[#FF4D8D]/20 shadow-2xl p-4 flex flex-col items-center justify-center space-y-3 relative group-hover:scale-105 transition-transform overflow-hidden">
                            <div className="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_#FF4D8D_0%,_transparent_70%)]" />
                            <div className="flex flex-col items-center mb-1">
                                <motion.div
                                    animate={{ scale: [1, 1.2, 1] }}
                                    transition={{ repeat: Infinity, duration: 2 }}
                                    className="text-3xl mb-1"
                                >
                                    ❤️
                                </motion.div>
                            </div>
                            <div className="space-y-1.5 w-full flex flex-col items-center mb-2">
                                <div className="h-1.5 w-[85%] bg-[#FF4D8D]/40 rounded-full" />
                                <div className="h-1.5 w-1/2 bg-[#FF4D8D]/20 rounded-full" />
                            </div>
                            <div className="flex flex-col gap-2 w-full px-1">
                                <div className="w-full h-8 bg-green-500 rounded-xl shadow-[0_4px_10px_rgba(34,197,94,0.3)] flex items-center justify-center">
                                    <span className="text-[8px] font-black text-white italic">YES!</span>
                                </div>
                                <div className="w-2/3 h-6 bg-black/5 border border-black/10 rounded-lg flex items-center justify-center self-center rotate-6 translate-x-2">
                                    <span className="text-[7px] font-bold text-black/20">No</span>
                                </div>
                            </div>
                            <div className="absolute top-4 left-4 w-1.5 h-1.5 rounded-full bg-blue-400 opacity-40" />
                            <div className="absolute bottom-10 right-4 w-2 h-2 rounded-full bg-pink-400 opacity-40 blur-[1px]" />
                        </div>
                    ) : template.id === 'classic-valentine' ? (
                        /* Classic Proposal Preview (Cute Bear UI) */
                        <div className="w-[120px] aspect-[9/16] bg-[#FFB6C1] rounded-[24px] border-[4px] border-white shadow-2xl p-4 flex flex-col items-center justify-center space-y-3 relative group-hover:scale-105 transition-transform overflow-hidden">
                            <div className="w-14 h-14 bg-white rounded-xl shadow-lg border border-pink-200 overflow-hidden mb-2">
                                <img src="https://media1.tenor.com/m/al4a1pG1fScAAAAC/jump-bear.gif" className="w-full h-full object-cover" alt="Bear" />
                            </div>
                            <div className="h-1.5 w-full bg-white/40 rounded-full" />
                            <div className="h-1.5 w-2/3 bg-white/40 rounded-full" />
                            <div className="flex flex-col gap-1 w-full mt-2">
                                <div className="h-6 w-full bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <span className="text-[6px] font-black text-white italic">YES</span>
                                </div>
                                <div className="h-4 w-1/2 bg-white/30 rounded-lg self-center" />
                            </div>
                        </div>
                    ) : (
                        /* Card / Premium Preview (Phone Mock - Color Scheme from image) */
                        <div className="w-[120px] aspect-[9/16] bg-[#FFECF8] rounded-[24px] border-[4px] border-black shadow-2xl p-4 flex flex-col items-center justify-between relative group-hover:rotate-[-3deg] transition-transform overflow-hidden">
                            <div className="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-2 bg-black rounded-b-lg" />
                            <div className="mt-4 space-y-2">
                                <div className="flex -space-x-2">
                                    <div className="w-10 h-10 bg-red-500 rounded-xl border-2 border-white flex items-center justify-center -rotate-12 shadow-md">
                                        <Heart size={12} className="text-white fill-current" />
                                    </div>
                                    <div className="w-10 h-10 bg-pink-100 rounded-xl border-2 border-white flex items-center justify-center rotate-12 shadow-md">
                                        <Heart size={12} className="text-pink-400 fill-current" />
                                    </div>
                                </div>
                                <div className="h-2 w-full bg-black/10 rounded-full" />
                                <div className="h-2 w-2/3 bg-black/10 rounded-full" />
                            </div>
                            <div className="w-full h-6 bg-black rounded-lg flex items-center justify-center">
                                <div className="w-2 h-2 bg-white/20 rounded-full" />
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Content */}
            <div className="flex flex-col justify-end gap-2 relative z-10 pt-6">
                <h3 className="text-2xl font-medium tracking-tight text-white group-hover:text-myRed transition-colors">
                    {template.name}
                </h3>
                <p className="text-white/30 text-sm font-medium leading-relaxed line-clamp-2">
                    {template.description}
                </p>
            </div>

            {/* Selection Overlay */}
            <div className="absolute inset-x-0 bottom-0 p-8 transform translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out">
                <div className="w-full py-4 bg-myRed text-white font-bold rounded-2xl shadow-xl flex items-center justify-center gap-2">
                    <span>Deploy Asset</span>
                    <Heart className="w-4 h-4 fill-current" />
                </div>
            </div>
        </motion.div >
    );
}
