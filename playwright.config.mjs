import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    workers: 1,
    timeout: 30000,
    use: { baseURL: process.env.E2E_BASE_URL, headless: true, screenshot: 'only-on-failure', trace: 'retain-on-failure' },
    reporter: [['list'], ['html', { open: 'never' }]],
});
