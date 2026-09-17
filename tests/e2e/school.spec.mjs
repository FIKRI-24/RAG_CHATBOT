import { test, expect } from '@playwright/test';
import { spawnSync } from 'node:child_process';
import { resolve } from 'node:path';

async function login(page, email) {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('E2e-password-2026');
    await page.locator('button[type="submit"]').click();
    await page.waitForURL('**/dashboard');
}

test('registration policy and real CSRF protection', async ({ request }) => {
    expect((await request.get('/register')).status()).toBe(404);
    expect((await request.post('/login', { form: { email: 'guru@e2e.test', password: 'E2e-password-2026' } })).status()).toBe(419);
});

test('teacher modal clears credentials, account deactivation preserves history, and monitoring renders', async ({ page }) => {
    await login(page, 'guru@e2e.test');
    await page.goto('/guru/siswa');
    const buttons = page.locator('button[title="Edit & Reset Password"]');
    await buttons.nth(0).click();
    await page.locator('#edit_password').fill('Accidental-password');
    await page.locator('#editModal button').filter({ has: page.locator('i.fa-xmark') }).click();
    await buttons.nth(1).click();
    await expect(page.locator('#edit_password')).toHaveValue('');
    await page.locator('#editModal button').filter({ has: page.locator('i.fa-xmark') }).click();
    const row = page.locator('tbody tr').nth(0);
    await row.getByRole('button', { name: 'Nonaktifkan', exact: true }).click();
    await expect(page.getByRole('button', { name: 'Aktifkan', exact: true })).toBeVisible();
    await page.getByRole('button', { name: 'Aktifkan', exact: true }).click();
    await page.goto('/guru/system');
    await expect(page.getByRole('heading', { name: 'Status sistem lokal' })).toBeVisible();
    await page.screenshot({ path: resolve(process.env.E2E_FIXTURE_DIR, 'system.png'), fullPage: true });
});

test('student RAG, quiz persistence, teacher review, and access control', async ({ page, browser }) => {
    await login(page, 'siswa1@e2e.test');
    await page.locator('#mapel-select').selectOption('Jaringan');
    await page.waitForURL('**/dashboard?mapel=Jaringan');
    await page.locator('#module-select').selectOption('1');
    await page.waitForURL('**/dashboard?mapel=Jaringan&module_id=1');
    await page.locator('#chat-form input').fill('Apa itu VLAN?');
    await page.locator('#chat-form').evaluate(form => form.requestSubmit());
    await expect(page.getByText('VLAN memisahkan jaringan secara logis [1].', { exact: true })).toBeVisible();
    await page.locator('#btn-kuis').click();
    await expect(page.getByText('Apa fungsi VLAN?', { exact: true })).toBeVisible();
    await page.reload();
    await expect(page.locator('#quiz-state')).toBeVisible();
    await page.locator('#chat-form input').fill('Memisahkan jaringan secara logis');
    await page.locator('#chat-form').evaluate(form => form.requestSubmit());
    await expect(page.getByText('Nilai AI sementara: 100/100.', { exact: false })).toBeVisible();
    const teacherContext = await browser.newContext();
    const teacher = await teacherContext.newPage();
    await login(teacher, 'guru@e2e.test');
    await teacher.goto('/guru/quiz-reviews');
    await teacher.locator('input[name="score"]').fill('85');
    await teacher.locator('textarea[name="review_note"]').fill('Konsep benar; tambahkan contoh port access.');
    await teacher.getByRole('button', { name: 'Simpan penilaian' }).click();
    await expect(teacher.getByText('Penilaian guru tersimpan.')).toBeVisible();
    await page.reload();
    await expect(page.getByText('Nilai guru: 85/100', { exact: false })).toBeVisible();
    expect((await page.request.get('/guru/quiz-reviews')).status()).toBe(403);
    await teacherContext.close();
});

test('upload DOCX, process dedicated RAG queue, extraction preview and export', async ({ page }) => {
    await login(page, 'guru@e2e.test');
    await page.goto('/guru/modules/create');
    await page.locator('[name="judul"]').fill('Materi unggahan E2E');
    await page.locator('[name="mapel"]').fill('Jaringan');
    await page.locator('label').filter({ has: page.locator('[name="kb_nomor"][value="KB 2"]') }).click();
    await page.locator('[name="file"]').setInputFiles(resolve(process.env.E2E_FIXTURE_DIR, 'private/modules/fixture.docx'));
    await page.locator('#submitBtn').click();
    await page.waitForURL('**/guru/modules');
    const worker = spawnSync('php', ['tests/Support/e2e-worker.php'], { env: process.env, stdio: 'pipe' });
    expect(worker.status, worker.stderr.toString()).toBe(0);
    await page.goto('/guru/modules/2');
    await expect(page.getByText('Pratinjau teks hasil ekstraksi (1 chunk)', { exact: true })).toBeVisible();
    await expect(page.getByText('Tidak ada chunk yang ditemukan.')).toHaveCount(0);
    const download = await page.request.get('/guru/siswa/export-activity');
    expect(download.status()).toBe(200);
    expect(download.headers()['content-type']).toContain('spreadsheetml');
    const backup = spawnSync('php', ['tests/Support/e2e-backup.php'], { env: process.env, stdio: 'pipe' });
    expect(backup.status, backup.stdout.toString() + backup.stderr.toString()).toBe(0);
});
