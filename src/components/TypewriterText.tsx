"use client";

import React, { useState, useEffect, useRef } from "react";
import { motion, useInView } from "framer-motion";

interface TypewriterTextProps {
    text: string;
    className?: string;
    delay?: number;
    speed?: number;
    onComplete?: () => void;
    trigger?: boolean; // Manual trigger for chaining
    startOnView?: boolean;
}

const Bubble = ({ x, y, size, delay }: { x: number; y: number; size: number; delay: number }) => {
    return (
        <motion.span
            initial={{ scale: 0, opacity: 0, x, y, backgroundColor: "#fc4100" }}
            animate={{
                scale: [0, 1.2, 0],
                opacity: [0, 0.9, 0],
                y: [y, y + (Math.random() - 0.5) * 25, y + (Math.random() - 0.5) * 25], // Jiggle around text
                x: x + (Math.random() - 0.5) * 25,
                backgroundColor: [
                    "#fc4100",
                    "#ff00ff",
                    "#00ffff",
                    "#ffff00",
                    "#00ff00",
                    "#fc4100"
                ]
            }}
            transition={{
                duration: 2,
                delay,
                ease: "easeInOut"
            }}
            className="absolute pointer-events-none rounded-full backdrop-blur-sm shadow-[0_0_12px_rgba(255,255,255,0.4)]"
            style={{
                width: size,
                height: size,
                zIndex: 10,
            }}
        />
    );
};

export const TypewriterText: React.FC<TypewriterTextProps> = ({
    text,
    className = "",
    delay = 0,
    speed = 0.03,
    onComplete,
    trigger = true,
    startOnView = true
}) => {
    const [displayText, setDisplayText] = useState("");
    const [isDone, setIsDone] = useState(false);
    const [bubbles, setBubbles] = useState<{ id: string | number; x: number; y: number; size: number }[]>([]);
    const containerRef = useRef<HTMLDivElement>(null);
    const ghostRef = useRef<HTMLSpanElement>(null);
    const isInView = useInView(containerRef, { once: true, amount: 0.1 });
    const [started, setStarted] = useState(false);

    useEffect(() => {
        if (startOnView && isInView && trigger && !started) {
            setStarted(true);
        } else if (!startOnView && trigger && !started) {
            setStarted(true);
        }
    }, [isInView, trigger, startOnView, started]);

    useEffect(() => {
        if (!started) return;

        let timeout: NodeJS.Timeout;
        let currentText = "";
        let index = 0;

        // Auto-scale speed based on text length: shorter text = slower, longer text = faster
        // Baseline: 30ms per char. If text > 100 chars, scale it down.
        const effectiveSpeed = text.length > 50
            ? Math.max(0.005, speed * (50 / text.length))
            : speed;

        const startTyping = () => {
            if (index < text.length) {
                currentText += text[index];
                setDisplayText(currentText);

                // Measure exact width for bubble placement
                if (ghostRef.current) {
                    const currentWidth = ghostRef.current.offsetWidth;

                    if (Math.random() > 0.1) {
                        setBubbles(prev => [...prev.slice(-40), {
                            id: `${Date.now()}-${index}-${Math.random().toString(36).substr(2, 9)}`,
                            x: currentWidth,
                            y: (Math.random() - 0.5) * 20,
                            size: Math.random() * 6 + 3
                        }]);
                    }
                }

                index++;
                timeout = setTimeout(startTyping, effectiveSpeed * 1000);
            } else {
                setIsDone(true);
                if (onComplete) onComplete();
            }
        };

        const initialDelay = setTimeout(startTyping, delay * 1000);

        return () => {
            clearTimeout(timeout);
            clearTimeout(initialDelay);
        };
    }, [text, delay, speed, started, onComplete]);

    return (
        <div ref={containerRef} className={`relative inline-block ${className}`}>
            {/* Hidden copy to reserve size and prevent layout shifts */}
            <span className="opacity-0 pointer-events-none select-none" aria-hidden="true">
                {text}
            </span>

            {/* Actual typing text */}
            <span className="absolute left-0 top-0 w-full h-full">
                {displayText}
                {!isDone && (
                    <motion.span
                        animate={{ opacity: [1, 0] }}
                        transition={{ duration: 0.4, repeat: Infinity }}
                        className="inline-block w-[2px] h-[1em] bg-myRed ml-1 align-middle"
                    />
                )}
            </span>

            {/* Ghost span for bubble measurement (no wrapping restriction) */}
            <span ref={ghostRef} className="invisible absolute left-0 top-0 pointer-events-none">
                {displayText}
            </span>

            <div className="absolute inset-0 pointer-events-none">
                {bubbles.map(bubble => (
                    <Bubble
                        key={bubble.id}
                        x={bubble.x}
                        y={bubble.y}
                        size={bubble.size}
                        delay={0}
                    />
                ))}
            </div>
        </div>
    );
};

export const TypewriterStepWrapper: React.FC<{
    children: (step: number, setStep: React.Dispatch<React.SetStateAction<number>>) => React.ReactNode
}> = ({ children }) => {
    const [step, setStep] = useState(0);
    return <>{children(step, setStep)}</>;
};

export default TypewriterText;
