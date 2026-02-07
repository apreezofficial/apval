
import { useState } from 'react';
import { motion } from 'framer-motion';
import { Music, Music2, ArrowRight, Search, Play, Check } from 'lucide-react';
import { EditorData } from '../types';

interface MusicStepProps {
    data: EditorData;
    onUpdate: (data: Partial<EditorData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export default function MusicStep({ data, onUpdate, onNext, onBack }: MusicStepProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<any[]>([]);
    const [isSearching, setIsSearching] = useState(false);

    const handleSearch = async (query: string) => {
        setSearchQuery(query);
        if (query.length < 2) {
            setSearchResults([]);
            return;
        }

        setIsSearching(true);
        try {
            const res = await fetch(`https://itunes.apple.com/search?term=${encodeURIComponent(query)}&entity=song&limit=6`);
            const result = await res.json();
            setSearchResults(result.results || []);
        } catch (err) {
            console.error('Music search failed:', err);
        } finally {
            setIsSearching(false);
        }
    };

    const moodTracks = [
        { name: 'Romantic Piano', url: 'https://www.youtube.com/embed/WJ3-F02-F_Y' },
        { name: 'Lofi Love', url: 'https://www.youtube.com/embed/5yx6BWlEVcY' },
        { name: 'Cinematic Strings', url: 'https://www.youtube.com/embed/B_mS_j8J0K0' },
        { name: 'Acoustic Guitar', url: 'https://www.youtube.com/embed/2mzX_7YhJ2g' }
    ];

    return (
        <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            className="space-y-8"
        >
            <div className="space-y-4">
                <h2 className="text-3xl font-medium tracking-tight">Audio Vibe</h2>
                <p className="text-white/40 font-medium italic">Search for your favorite song or pick a mood.</p>
            </div>

            {/* Music Search */}
            <div className="relative">
                <Search className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => handleSearch(e.target.value)}
                    placeholder="Search songs... (e.g. Ed Sheeran)"
                    className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium shadow-inner"
                />
                {isSearching && (
                    <div className="absolute right-6 top-1/2 -translate-y-1/2">
                        <div className="w-4 h-4 border-2 border-myRed border-t-transparent rounded-full animate-spin" />
                    </div>
                )}
            </div>

            {searchResults.length > 0 && (
                <div className="grid grid-cols-1 gap-2 max-h-[200px] overflow-y-auto pr-2 custom-scrollbar">
                    {searchResults.map((track: any) => (
                        <button
                            key={track.trackId}
                            onClick={() => onUpdate({ musicUrl: track.previewUrl })}
                            className={`flex items-center gap-4 p-3 rounded-xl border transition-all ${data.musicUrl === track.previewUrl
                                    ? 'bg-myRed/10 border-myRed/30'
                                    : 'bg-white/[0.02] border-white/5 hover:bg-white/[0.05]'
                                }`}
                        >
                            <img src={track.artworkUrl60} className="w-10 h-10 rounded-lg" alt="" />
                            <div className="flex-1 text-left">
                                <div className="text-sm font-bold text-white truncate">{track.trackName}</div>
                                <div className="text-[10px] text-white/40 truncate">{track.artistName}</div>
                            </div>
                            {data.musicUrl === track.previewUrl ? (
                                <Check className="w-4 h-4 text-myRed" />
                            ) : (
                                <Play className="w-4 h-4 text-white/20" />
                            )}
                        </button>
                    ))}
                </div>
            )}

            {!searchQuery && (
                <div className="grid grid-cols-2 gap-3">
                    {moodTracks.map((track) => (
                        <button
                            key={track.name}
                            onClick={() => onUpdate({ musicUrl: track.url })}
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
            )}

            <div className="relative">
                <div className="text-[10px] font-black uppercase tracking-[0.2em] text-white/20 mb-3 ml-2">Manual Override</div>
                <input
                    type="text"
                    value={data.musicUrl}
                    onChange={(e) => onUpdate({ musicUrl: e.target.value })}
                    placeholder="Paste a direct audio link or YouTube embed"
                    className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white text-[10px] font-mono opacity-60"
                />
            </div>

            <div className="flex gap-4">
                <button
                    onClick={onBack}
                    className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all text-xs uppercase tracking-widest"
                >
                    Back
                </button>
                <button
                    disabled={!data.musicUrl}
                    onClick={onNext}
                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 disabled:opacity-50 shadow-[0_10px_20px_-5px_rgba(252,65,0,0.4)]"
                >
                    <span className="text-sm uppercase tracking-widest">Set Soundtrack</span>
                    <ArrowRight className="w-5 h-5" />
                </button>
            </div>
        </motion.div>
    );
}
