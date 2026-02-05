import { NextResponse } from 'next/server';
import { saveValentine, deleteValentine } from '@/lib/db';
import { v4 as uuidv4 } from 'uuid';

export async function POST(request: Request) {
    try {
        const { userId, ...data } = await request.json();
        const id = uuidv4();
        await saveValentine(id, { ...data, userId });
        return NextResponse.json({ id });
    } catch (error) {
        return NextResponse.json({ error: 'Failed to save Valentine' }, { status: 500 });
    }
}

export async function PUT(request: Request) {
    try {
        const { id, userId, ...data } = await request.json();
        if (!id) return NextResponse.json({ error: 'ID required' }, { status: 400 });
        await saveValentine(id, { ...data, userId });
        return NextResponse.json({ success: true });
    } catch (error) {
        return NextResponse.json({ error: 'Failed to update Valentine' }, { status: 500 });
    }
}

export async function DELETE(request: Request) {
    try {
        const { searchParams } = new URL(request.url);
        const id = searchParams.get('id');
        if (!id) return NextResponse.json({ error: 'ID required' }, { status: 400 });

        const success = await deleteValentine(id);
        if (success) {
            return NextResponse.json({ success: true });
        } else {
            return NextResponse.json({ error: 'Valentine not found' }, { status: 404 });
        }
    } catch (error) {
        return NextResponse.json({ error: 'Failed to delete Valentine' }, { status: 500 });
    }
}
