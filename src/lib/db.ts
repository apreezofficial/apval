import fs from 'fs/promises';
import path from 'path';

const DATA_DIR = path.join(process.cwd(), 'data');
const DB_PATH = path.join(DATA_DIR, 'valentines.json');
const USERS_PATH = path.join(DATA_DIR, 'users.json');

async function ensureDataDir() {
    try {
        await fs.access(DATA_DIR);
    } catch {
        await fs.mkdir(DATA_DIR, { recursive: true });
    }
}

async function readJson(filePath: string) {
    await ensureDataDir();
    try {
        const data = await fs.readFile(filePath, 'utf-8');
        return JSON.parse(data);
    } catch (error) {
        return {};
    }
}

async function writeJson(filePath: string, data: any) {
    await ensureDataDir();
    await fs.writeFile(filePath, JSON.stringify(data, null, 2), 'utf-8');
}

export async function getValentines() {
    return await readJson(DB_PATH);
}

export async function saveValentine(id: string, data: any) {
    const currentData = await getValentines();
    currentData[id] = { ...data, createdAt: new Date().toISOString() };
    await writeJson(DB_PATH, currentData);
}

export async function getUsers() {
    return await readJson(USERS_PATH);
}

export async function saveUser(userId: string, data: any) {
    const currentUsers = await getUsers();
    currentUsers[userId] = { ...data, createdAt: new Date().toISOString() };
    await writeJson(USERS_PATH, currentUsers);
}

export async function getUserByEmail(email: string) {
    const users = await getUsers();
    return Object.values(users).find((u: any) => u.email === email);
}

export async function getUserById(id: string) {
    const users = await getUsers();
    return users[id] || null;
}

export async function getValentinesByUser(userId: string) {
    const valentines = await getValentines();
    return Object.entries(valentines)
        .filter(([_, v]: [string, any]) => v.userId === userId)
        .map(([id, v]: [string, any]) => ({ ...v, id }));
}

export async function deleteValentine(id: string) {
    const currentData = await getValentines();
    if (currentData[id]) {
        delete currentData[id];
        await writeJson(DB_PATH, currentData);
        return true;
    }
    return false;
}

export async function getValentineBySlug(slug: string) {
    const valentines = await getValentines();
    return Object.values(valentines).find((v: any) => v.customSlug === slug);
}

export async function isSlugTaken(slug: string, excludeId?: string) {
    const valentines = await getValentines();
    return Object.entries(valentines).some(([id, v]: [string, any]) =>
        v.customSlug === slug && id !== excludeId
    );
}
