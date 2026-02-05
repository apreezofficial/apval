import { NextResponse } from 'next/server';
import { getValentines } from '@/lib/db';

export async function GET(
    request: Request,
    { params }: { params: Promise<{ id: string }> }
) {
    try {
        const { id } = await params;
        const valentines = await getValentines();
        const data = valentines[id];

        if (!data) {
            return NextResponse.json({ error: 'Valentine not found' }, { status: 404 });
        }

        return NextResponse.json({ ...data, id });
    } catch (error) {
        return NextResponse.json({ error: 'Failed to fetch Valentine' }, { status: 500 });
    }
}
