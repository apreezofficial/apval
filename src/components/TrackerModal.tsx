'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Smartphone, Monitor, Globe, Clock, ShieldCheck, MapPin, Search } from 'lucide-react';
import { useState, useEffect } from 'react';
import { apiGet } from '@/lib/api';

interface ViewStats {
    id: string;
    viewer_ip: string;
    user_agent: string;
    device: string;
    browser: string;
    viewed_at: string;
    city?: string;
    country?: string;
}

export default function TrackerModal({ valentine, onClose }: { valentine: any; onClose: () => void }) {
    const [stats, setStats] = useState<ViewStats[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        apiGet(`/valentines/stats?id=${valentine.id}`)
            .then(data => {
                setStats(Array.isArray(data) ? data : []);
                setIsLoading(false);
            })
            .catch(() => setIsLoading(false));
    }, [valentine.id]);

    const formatDate = (dateStr: string) => {
        return new Date(dateStr).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[300] flex items-center justify-center p-4 md:p-6 bg-black/90 backdrop-blur-3xl"
        >
            <motion.div
                initial={{ scale: 0.9, y: 30 }}
                animate={{ scale: 1, y: 0 }}
                className="w-full max-w-4xl max-h-[90vh] bg-[#0A0A0A] border border-white/10 rounded-[40px] flex flex-col overflow-hidden shadow-2xl"
            >
                {/* Header */}
                <div className="p-8 border-b border-white/5 flex items-center justify-between bg-gradient-to-r from-myRed/10 to-transparent">
                    <div className="space-y-1">
                        <div className="flex items-center gap-3">
                            <ShieldCheck className="text-myRed w-6 h-6" />
                            <h2 className="text-2xl font-bold tracking-tight">Vibe Tracker</h2>
                        </div>
                        <p className="text-white/40 text-xs uppercase tracking-widest font-black">
                            Analytics for: <span className="text-white">{valentine.recipient}</span>
                        </p>
                    </div>
                    <button
                        onClick={onClose}
                        className="p-3 hover:bg-white/5 rounded-full transition-colors text-white/40 hover:text-white"
                    >
                        <X size={24} />
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto p-8 custom-scrollbar">
                    {isLoading ? (
                        <div className="flex flex-col items-center justify-center py-20 space-y-4">
                            <div className="w-12 h-12 border-2 border-myRed border-t-transparent rounded-full animate-spin" />
                            <p className="text-white/20 text-xs font-black uppercase tracking-widest">Intercepting Data...</p>
                        </div>
                    ) : stats.length === 0 ? (
                        <div className="text-center py-20 bg-white/[0.02] border border-dashed border-white/5 rounded-[32px]">
                            <Search className="w-12 h-12 text-white/10 mx-auto mb-4" />
                            <p className="text-white/40 font-medium">No views detected yet.</p>
                            <p className="text-white/20 text-xs mt-2">The person hasn't opened your link yet.</p>
                        </div>
                    ) : (
                        <div className="space-y-6">
                            {/* Summary Cards */}
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <SummaryBadge label="Total Views" value={stats.length.toString()} icon={<Globe className="w-4 h-4" />} />
                                <SummaryBadge label="Mobile Users" value={stats.filter(s => s.device === 'Mobile').length.toString()} icon={<Smartphone className="w-4 h-4" />} />
                                <SummaryBadge label="Desktop Users" value={stats.filter(s => s.device === 'Desktop').length.toString()} icon={<Monitor className="w-4 h-4" />} />
                                <SummaryBadge label="Latest Activity" value={formatDate(stats[0].viewed_at)} icon={<Clock className="w-4 h-4" />} />
                            </div>

                            {/* Stats Table */}
                            <div className="mt-10 overflow-x-auto">
                                <table className="w-full text-left border-collapse">
                                    <thead>
                                        <tr className="text-[10px] font-black uppercase tracking-widest text-white/20 border-b border-white/5">
                                            <th className="pb-4 pl-4">Viewer Profile</th>
                                            <th className="pb-4">Device / Engine</th>
                                            <th className="pb-4">Location (IP)</th>
                                            <th className="pb-4 pr-4 text-right">Accessed At</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-white/[0.03]">
                                        {stats.map((view) => (
                                            <tr key={view.id} className="group hover:bg-white/[0.02] transition-colors">
                                                <td className="py-6 pl-4">
                                                    <div className="flex items-center gap-3">
                                                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${view.device === 'Mobile' ? 'bg-blue-500/10 text-blue-400' : 'bg-purple-500/10 text-purple-400'}`}>
                                                            {view.device === 'Mobile' ? <Smartphone size={18} /> : <Monitor size={18} />}
                                                        </div>
                                                        <div>
                                                            <p className="text-sm font-bold text-white group-hover:text-myRed transition-colors">Recipient View</p>
                                                            <p className="text-[10px] text-white/40 uppercase tracking-widest">{view.browser}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-6">
                                                    <div className="max-w-[150px] truncate text-[10px] text-white/30 font-mono italic" title={view.user_agent}>
                                                        {view.user_agent}
                                                    </div>
                                                </td>
                                                <td className="py-6">
                                                    <div className="flex items-center gap-2">
                                                        <MapPin size={12} className="text-white/20" />
                                                        <div>
                                                            <p className="text-xs font-bold text-white/80">{view.viewer_ip}</p>
                                                            <p className="text-[9px] text-white/20 uppercase tracking-tighter">
                                                                {view.city && view.country ? `${view.city}, ${view.country}` : 'Scanning Location...'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-6 pr-4 text-right">
                                                    <p className="text-xs font-bold text-white/60">{formatDate(view.viewed_at)}</p>
                                                    <p className="text-[9px] text-white/20 uppercase">Sync Complete</p>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-6 bg-white/[0.02] border-t border-white/5 flex items-center justify-center">
                    <p className="text-[10px] font-black uppercase tracking-[0.3em] text-white/10">
                        End-to-End Encrypted Delivery Verification
                    </p>
                </div>
            </motion.div>
        </motion.div>
    );
}

function SummaryBadge({ label, value, icon }: { label: string, value: string, icon: React.ReactNode }) {
    return (
        <div className="p-5 bg-white/[0.03] border border-white/5 rounded-3xl space-y-2 group hover:border-myRed/20 transition-all">
            <div className="flex justify-between items-center">
                <div className="p-2 bg-white/5 rounded-lg text-white/40 group-hover:text-myRed transition-colors">
                    {icon}
                </div>
                <div className="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse" />
            </div>
            <div>
                <p className="text-[9px] font-black uppercase tracking-widest text-white/20">{label}</p>
                <p className="text-lg font-black text-white">{value}</p>
            </div>
        </div>
    );
}
