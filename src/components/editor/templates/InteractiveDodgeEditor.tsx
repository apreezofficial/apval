
import { EditorData, EditorStep } from '../types';
import RecipientStep from '../steps/RecipientStep';
import GenderStep from '../steps/GenderStep';
import IntroMessagesStep from '../steps/IntroMessagesStep';
import MusicStep from '../steps/MusicStep';
import SignatureStep from '../steps/SignatureStep';

interface TemplateEditorProps {
    data: EditorData;
    step: number;
    features: EditorStep[];
    onUpdate: (data: Partial<EditorData>) => void;
    onNext: () => void;
    onBack: () => void;
    onFinish: () => void;
    loading: boolean;
}

export default function InteractiveDodgeEditor({
    data,
    step,
    features,
    onUpdate,
    onNext,
    onBack,
    onFinish,
    loading
}: TemplateEditorProps) {
    const currentFeature = features[step - 1];

    switch (currentFeature) {
        case 'recipient':
            return <RecipientStep data={data} onUpdate={onUpdate} onNext={onNext} showHeadline={false} title="Who's the Lucky One?" description="Who will you ask?" />;
        case 'gender':
            return <GenderStep data={data} onUpdate={onUpdate} onNext={onNext} onBack={onBack} />;
        case 'proposal_intro':
            return <IntroMessagesStep data={data} onUpdate={onUpdate} onNext={onNext} onBack={onBack} />;
        case 'audio':
            return <MusicStep data={data} onUpdate={onUpdate} onNext={onNext} onBack={onBack} />;
        case 'signature':
            return <SignatureStep data={data} onUpdate={onUpdate} onFinish={onFinish} onBack={onBack} loading={loading} />;
        default:
            return null;
    }
}
