export interface Template {
    id: string;
    name: string;
    description: string;
    theme: 'vintage' | 'cream' | 'playful' | 'elegant' | 'dark';
    previewColor: string;
    category: 'card' | 'website';
}

export const templates: Template[] = [
    {
        id: 'premium-mockup-card',
        name: 'Premium Interactive Card',
        description: 'Multi-step animated story with 3D heart locks.',
        theme: 'playful',
        previewColor: '#FF99F1',
        category: 'card',
    },
    {
        id: 'amour',
        name: 'Amour Cinematic',
        description: 'Fullscreen glassmorphic layout with bold cinematic typography.',
        theme: 'elegant',
        previewColor: '#8BB0C1',
        category: 'website',
    },
    {
        id: 'minimal-elite-card',
        name: 'Minimal Elite',
        description: 'A masterpiece of restraint. Pure focus on your message with obsidian-glass aesthetics.',
        theme: 'dark',
        previewColor: '#ffffff',
        category: 'card'
    },
    {
        id: 'valentine-motion-premium',
        name: 'Premium Motion Card',
        description: 'Cinematic intro sequence, luxury gold accents, and a reactive heart canvas.',
        theme: 'elegant',
        previewColor: '#A82424',
        category: 'card'
    },
    {
        id: 'quest-valentine',
        name: 'Quest Valentine',
        description: 'A playful "Will you be my Valentine?" question with a interactive buttons.',
        theme: 'playful',
        previewColor: '#FF4D4D',
        category: 'website'
    },
    {
        id: 'interactive-dodge',
        name: 'Interactive Proposal',
        description: 'The ultimate dodge-the-no game. Multi-stage intro with a gender-themed UI and confetti celebration.',
        theme: 'playful',
        previewColor: '#FF4D8D',
        category: 'website'
    },
    {
        id: 'classic-valentine',
        name: 'The Classic Proposal',
        description: 'The viral "Yes/No" interaction with cute bears and an unstoppable "Yes" button.',
        theme: 'playful',
        previewColor: '#FFB6C1',
        category: 'website'
    },
    {
        id: 'interactive-dodge',
        name: 'Interactive Proposal',
        description: 'The ultimate dodge-the-no game. Multi-stage intro with a gender-themed UI and confetti celebration.',
        theme: 'playful',
        previewColor: '#FF4D8D',
        category: 'website'
    },
    {
        id: 'classic-valentine',
        name: 'The Classic Proposal',
        description: 'The viral "Yes/No" interaction with cute bears and an unstoppable "Yes" button.',
        theme: 'playful',
        previewColor: '#FFB6C1',
        category: 'website'
    }
];
