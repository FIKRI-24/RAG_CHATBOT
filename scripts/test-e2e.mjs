import { spawn, spawnSync } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { createServer } from 'node:net';

const root = process.cwd();
const directory = resolve(root, '.tools/e2e', `run-${Date.now()}-${process.pid}`);
mkdirSync(directory, { recursive: true });
const database = resolve(directory, 'database.sqlite');
writeFileSync(database, '', { flag: 'wx' });
const port = process.env.E2E_PORT ? Number(process.env.E2E_PORT) : await new Promise((resolve, reject) => {
    const probe = createServer();
    probe.on('error', reject);
    probe.listen(0, '127.0.0.1', () => {
        const freePort = probe.address().port;
        probe.close(() => resolve(freePort));
    });
});
const env = { ...process.env, APP_ENV: 'e2e', APP_DEBUG: 'false', APP_URL: `http://127.0.0.1:${port}`, APP_KEY: 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=', DB_CONNECTION: 'sqlite', DB_DATABASE: database, DB_URL: '', CACHE_STORE: 'database', SESSION_DRIVER: 'database', MAIL_MAILER: 'array', QUEUE_CONNECTION: 'database', GEMINI_API_KEY: '', REGISTRATION_ENABLED: 'false', REQUIRE_VERIFIED_EMAIL: 'false', BCRYPT_ROUNDS: '4', E2E_BASE_URL: `http://127.0.0.1:${port}`, E2E_FIXTURE_DIR: directory };
const prepare = spawnSync('php', ['tests/Support/prepare-e2e.php'], { cwd: root, env, stdio: 'inherit' });
if (prepare.status !== 0) process.exit(prepare.status ?? 1);
const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', 'public', 'tests/Support/e2e-router.php'], { cwd: root, env, stdio: ['ignore', 'ignore', 'pipe'], windowsHide: true });
let serverErrors = '';
server.stderr.on('data', chunk => { serverErrors = (serverErrors + chunk).slice(-4000); });
try {
    let ready = false;
    for (let i = 0; i < 60; i++) {
        try { ready = (await fetch(env.E2E_BASE_URL + '/login')).ok; } catch { /* startup */ }
        if (ready) break;
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    if (!ready) throw new Error('E2E server failed to start. ' + serverErrors);
    const args = process.argv.includes('--load') ? ['scripts/load-test.mjs'] : ['node_modules/playwright/cli.js', 'test'];
    const runner = spawn(process.execPath, args, { cwd: root, env, stdio: 'inherit', windowsHide: true });
    process.exitCode = await new Promise(resolve => runner.on('exit', code => resolve(code ?? 1)));
} finally {
    server.kill();
}
