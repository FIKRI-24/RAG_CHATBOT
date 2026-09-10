import { existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const localNode = resolve(root, '.tools/node/node.exe');
const [major, minor] = process.versions.node.split('.').map(Number);
const supported = (major === 20 && minor >= 19) || (major === 22 && minor >= 12) || major > 22;
const runtime = process.platform === 'win32' && existsSync(localNode) ? localNode : process.execPath;
if (runtime === process.execPath && !supported) {
    console.error('Node 20.19+ atau 22.12+ diperlukan. Di Windows, jalankan powershell -File scripts/setup-node.ps1 untuk memasang runtime khusus proyek.');
    process.exit(1);
}
const action = process.argv[2];
const tasks = {
    build: [resolve(root, 'node_modules/vite/bin/vite.js'), 'build'],
    dev: [resolve(root, 'node_modules/vite/bin/vite.js')],
    test: ['--test', resolve(root, 'tests/js/rag-markdown.test.mjs')],
};
if (!Object.hasOwn(tasks, action)) {
    console.error('Gunakan build, dev, atau test.');
    process.exit(1);
}
const result = spawnSync(runtime, [...tasks[action], ...process.argv.slice(3)], { cwd: root, stdio: 'inherit' });
if (result.error) console.error(result.error.message);
process.exit(result.status ?? 1);
