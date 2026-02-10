import { NextResponse } from 'next/server';
import { getValentines } from '@/lib/db';

export async function GET(
    request: Request,
    { params }: { params: Promise<{ id: string }> }
) {
    try {
        const { id } = await params;
        const valentines = await getValentines();

        // Try direct ID match first
        let data = valentines[id];
        let foundId = id;

        // If not found, try finding by slug
        if (!data) {
            const entry = Object.entries(valentines).find(([key, v]: [string, any]) => v.customSlug === id);
            if (entry) {
                foundId = entry[0];
                data = entry[1];
            }
        }

        if (!data) {
            return NextResponse.json({ error: 'Valentine not found' }, { status: 404 });
        }

        return NextResponse.json({ ...data, id: foundId });
    } catch (error) {
        return NextResponse.json({ error: 'Failed to fetch Valentine' }, { status: 500 });
    }
}
