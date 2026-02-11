import { useState, useEffect, useRef } from 'react';
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
    const [selectedTrackInfo, setSelectedTrackInfo] = useState<{ name: string, artist: string, artwork: string } | null>(null);
    const [isChanging, setIsChanging] = useState(false);

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

    const handleSelect = (track: any) => {
        onUpdate({ musicUrl: track.previewUrl });
        setSelectedTrackInfo({
            name: track.trackName,
            artist: track.artistName,
            artwork: track.artworkUrl60
        });
        setSearchResults([]);
        setSearchQuery('');
        setIsChanging(false);
    };

    const showSearch = !data.musicUrl || isChanging;

    return (
        <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            className="space-y-8"
        >
            <div className="space-y-4">
                <h2 className="text-3xl font-medium tracking-tight">Audio Vibe</h2>
                <p className="text-white/40 font-medium italic">
                    {data.musicUrl && !isChanging ? 'Soundtrack selected.' : 'Search for your favorite song or paste a link.'}
                </p>
            </div>

            {data.musicUrl && !isChanging && (
                <motion.div
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className="p-6 bg-myRed/10 border border-myRed/20 rounded-[32px] flex items-center gap-6"
                >
                    <div className="w-16 h-16 bg-myRed/20 rounded-2xl flex items-center justify-center overflow-hidden border border-myRed/30 relative group">
                        {selectedTrackInfo?.artwork ? (
                            <img src={selectedTrackInfo.artwork} className="w-full h-full object-cover" alt="" />
                        ) : (
                            <Music className="text-myRed w-6 h-6" />
                        )}
                        <Play className="absolute inset-0 m-auto text-white w-6 h-6 drop-shadow-lg" />
                    </div>
                    <div className="flex-1 min-w-0">
                        <div className="text-lg font-black text-white truncate">
                            {selectedTrackInfo?.name || 'Selected Soundtrack'}
                        </div>
                        <div className="text-xs text-myRed font-bold uppercase tracking-widest mt-1">
                            {selectedTrackInfo?.artist || 'Custom Audio Link'}
                        </div>
                    </div>
                    <button
                        onClick={() => setIsChanging(true)}
                        className="p-3 hover:bg-white/5 rounded-full transition-colors text-white/40 hover:text-white"
                    >
                        <Search className="w-5 h-5" />
                    </button>
                </motion.div>
            )}

            {showSearch && (
                <>
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
                        <div className="space-y-2">
                            <div className="text-[10px] font-black uppercase tracking-[0.2em] text-white/20 mb-3 ml-2">Search Results</div>
                            <div className="grid grid-cols-1 gap-2">
                                {searchResults.map((track: any) => (
                                    <button
                                        key={track.trackId}
                                        onClick={() => handleSelect(track)}
                                        className={`flex items-center gap-4 p-4 rounded-2xl border transition-all ${data.musicUrl === track.previewUrl
                                            ? 'bg-myRed/10 border-myRed/30'
                                            : 'bg-white/[0.02] border-white/5 hover:bg-white/[0.05]'
                                            }`}
                                    >
                                        <img src={track.artworkUrl60} className="w-12 h-12 rounded-xl" alt="" />
                                        <div className="flex-1 text-left min-w-0">
                                            <div className="text-sm font-bold text-white truncate">{track.trackName}</div>
                                            <div className="text-[10px] text-white/40 truncate">{track.artistName}</div>
                                        </div>
                                        {data.musicUrl === track.previewUrl ? (
                                            <div className="w-8 h-8 bg-myRed/20 rounded-full flex items-center justify-center">
                                                <Check className="w-4 h-4 text-myRed" />
                                            </div>
                                        ) : (
                                            <div className="w-8 h-8 bg-white/5 rounded-full flex items-center justify-center">
                                                <Play className="w-4 h-4 text-white/20" />
                                            </div>
                                        )}
                                    </button>
                                ))}
                            </div>
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
                </>
            )}

            <div className="flex gap-4 pt-4">
                <button
                    onClick={onBack}
                    className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all text-[10px] uppercase tracking-widest border border-white/5"
                >
                    Back
                </button>
                <button
                    onClick={onNext}
                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 shadow-[0_10px_20px_-5px_rgba(252,65,0,0.4)]"
                >
                    <span className="text-[10px] uppercase tracking-widest">{data.musicUrl ? 'Set Soundtrack' : 'Skip Music'}</span>
                    <ArrowRight className="w-5 h-5" />
                </button>
            </div>
        </motion.div>
    );
}

