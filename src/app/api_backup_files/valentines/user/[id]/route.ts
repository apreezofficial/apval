import { NextResponse } from 'next/server';
import { getValentinesByUser } from '@/lib/db';

export async function GET(
    request: Request,
    { params }: { params: Promise<{ id: string }> }
) {
    try {
        const { id } = await params;
        const data = await getValentinesByUser(id);
        return NextResponse.json(data);
    } catch (error) {
        return NextResponse.json({ error: 'Failed to fetch user valentines' }, { status: 500 });
    }
}
