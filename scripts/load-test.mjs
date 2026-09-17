import { performance } from 'node:perf_hooks';
import { writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

const base = process.env.E2E_BASE_URL;
if (!base || process.env.APP_ENV !== 'e2e') throw new Error('Run through the isolated E2E launcher.');
async function student(i) {
    const cookies = new Map();
    async function request(path, options = {}) {
        const response = await fetch(base + path, { ...options, redirect: 'manual', headers: { Cookie: [...cookies].map(([name, value]) => `${name}=${value}`).join('; '), ...options.headers } });
        for (const cookie of response.headers.getSetCookie()) {
            const pair = cookie.split(';')[0];
            const index = pair.indexOf('=');
            cookies.set(pair.slice(0, index), pair.slice(index + 1));
        }
        return response;
    }
    const html = await (await request('/login')).text();
    const token = html.match(/name="_token" value="([^"]+)"/)[1];
    const login = await request('/login', { method: 'POST', body: new URLSearchParams({ _token: token, email: `siswa${i}@e2e.test`, password: 'E2e-password-2026' }) });
    if (login.status !== 302) throw new Error('Fixture login failed.');
    const dashboard = await (await request('/siswa/dashboard')).text();
    const csrf = dashboard.match(/name="csrf-token" content="([^"]+)"/)[1];
    return async () => {
        const started = performance.now();
        const response = await request('/siswa/chat/ask', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ pertanyaan: 'Apa fungsi VLAN?', mapel: 'Jaringan', module_id: 1 }) });
        const result = await response.json();
        return { duration_ms: Math.round(performance.now() - started), status: response.status, valid: response.ok && result.success && result.data.sources.length > 0 };
    };
}
const clients = [];
for (let i = 1; i <= 20; i++) clients.push(await student(i));
const rows = [];
for (let round = 0; round < 3; round++) rows.push(...await Promise.all(clients.map(client => client())));
const times = rows.map(row => row.duration_ms).sort((a, b) => a - b);
const report = { mode: 'offline fixture; single PHP development server', concurrency: 20, requests: rows.length, failures: rows.filter(row => !row.valid).length, p50_ms: times[Math.ceil(times.length * .5) - 1], p95_ms: times[Math.ceil(times.length * .95) - 1], p99_ms: times[Math.ceil(times.length * .99) - 1], rows };
writeFileSync(resolve(process.env.E2E_FIXTURE_DIR, 'load-report.json'), JSON.stringify(report, null, 2));
console.log(JSON.stringify({ ...report, rows: undefined }, null, 2));
if (report.failures > 0) process.exitCode = 1;
