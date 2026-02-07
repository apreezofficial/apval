'use client';

import { useEffect, useRef } from 'react';

interface MusicPlayerProps {
    url: string;
    isMuted: boolean;
}

export default function MusicPlayer({ url, isMuted }: MusicPlayerProps) {
    const audioRef = useRef<HTMLAudioElement>(null);

    const isDirectFile = (url || '').match(/\.(mp3|m4a|wav|ogg)(\?|$)/i);

    useEffect(() => {
        if (audioRef.current) {
            if (isMuted) {
                audioRef.current.muted = true;
            } else {
                audioRef.current.muted = false;
                audioRef.current.play().catch(e => console.log("Autoplay blocked usually:", e));
            }
        }
    }, [isMuted, url]);

    if (!url) return null;

    if (isDirectFile) {
        return (
            <audio
                ref={audioRef}
                src={url}
                loop
                autoPlay
                muted={isMuted}
                className="hidden"
            />
        );
    }

    // Fallback for YouTube/Spotify embeds
    // Note: YouTube embeds usually don't support autoplay if hidden or without interaction, 
    // but the iframe approach is what was used.
    // We add 'autoplay=1' and 'mute=1' if isMuted, but YouTube API varies.
    // For now, adhering to previous iframe pattern but adding mute control if possible?
    // Iframe mute is hard without API.

    // If it's a YouTube embed, we can try adding &mute=1
    const separator = url.includes('?') ? '&' : '?';
    const embedUrl = `${url}${separator}autoplay=1${isMuted ? '&mute=1' : ''}`;

    return (
        <iframe
            src={embedUrl}
            className="hidden"
            allow="autoplay"
        />
    );
}
