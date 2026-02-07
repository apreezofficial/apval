
import { Heart } from 'lucide-react';
import { ReactNode } from 'react';

interface EditorPreviewProps {
    templateId: string;
    children: ReactNode;
}

export default function EditorPreview({ templateId, children }: EditorPreviewProps) {
    const isWebsite = templateId === 'amour';

    return (
        <div className="flex-1 bg-[#111111] p-12 flex items-center justify-center border-r border-white/5 relative overflow-hidden hidden md:flex">
            <div className="absolute inset-0 opacity-20 pointer-events-none bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:40px_40px]" />
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] bg-myRed/10 rounded-full blur-[100px]" />

            {isWebsite ? (
                /* Browser Mockup */
                <div className="relative w-full h-full max-h-[500px] bg-white rounded-2xl border-[8px] border-black shadow-2xl overflow-hidden flex flex-col">
                    <div className="h-6 bg-black flex items-center px-4 gap-1.5">
                        <div className="w-1.5 h-1.5 rounded-full bg-red-500" />
                        <div className="w-1.5 h-1.5 rounded-full bg-yellow-500" />
                        <div className="w-1.5 h-1.5 rounded-full bg-green-500" />
                        <div className="ml-4 h-3 flex-1 bg-white/10 rounded-sm" />
                    </div>
                    {children}
                </div>
            ) : (
                /* iPhone Mockup */
                <div className="relative w-full max-w-[320px] aspect-[9/19] bg-white rounded-[50px] border-[10px] border-black shadow-2xl overflow-hidden flex flex-col">
                    <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[100px] h-[28px] bg-black rounded-b-2xl z-50 px-4 pt-1">
                        <div className="w-12 h-1 bg-white/10 mx-auto rounded-full" />
                    </div>
                    {children}
                </div>
            )}
        </div>
    );
}
