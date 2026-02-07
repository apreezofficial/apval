
import { EditorData, EditorStep } from '../types';
import RecipientStep from '../steps/RecipientStep';
import ContentStep from '../steps/ContentStep';
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

export default function ClassicValentineEditor({
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
            return <RecipientStep data={data} onUpdate={onUpdate} onNext={onNext} showHeadline={false} title="Who's the Special One?" description="Who will you ask to be your Valentine?" />;
        case 'content':
            return <ContentStep data={data} onUpdate={onUpdate} onNext={onNext} onBack={onBack} title="The Question" description="Customize the proposal message." />;
        case 'audio':
            return <MusicStep data={data} onUpdate={onUpdate} onNext={onNext} onBack={onBack} />;
        case 'signature':
            return <SignatureStep data={data} onUpdate={onUpdate} onFinish={onFinish} onBack={onBack} loading={loading} />;
        default:
            return null;
    }
}
