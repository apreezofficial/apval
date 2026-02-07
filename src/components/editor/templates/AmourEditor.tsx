
import { EditorData, EditorStep } from '../types';
import RecipientStep from '../steps/RecipientStep';
import ContentStep from '../steps/ContentStep';
import AttachmentStep from '../steps/AttachmentStep';
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

export default function AmourEditor({
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
        case 'recipient_headline':
            return <RecipientStep data={data} onUpdate={onUpdate} onNext={onNext} showHeadline={true} />;
        case 'content':
            return <ContentStep data={data} onUpdate={onUpdate} onNext={onNext} onBack={onBack} />;
        case 'attachment':
            return <AttachmentStep data={data} onUpdate={onUpdate} onNext={onNext} onBack={onBack} />;
        case 'audio':
            return <MusicStep data={data} onUpdate={onUpdate} onNext={onNext} onBack={onBack} />;
        case 'signature':
            return <SignatureStep data={data} onUpdate={onUpdate} onFinish={onFinish} onBack={onBack} loading={loading} showWhatsapp={true} />;
        default:
            return null;
    }
}
