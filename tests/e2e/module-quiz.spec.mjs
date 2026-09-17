import { test, expect } from '@playwright/test';
import { resolve } from 'node:path';

async function login(page, email) {
    await page.goto('/login');
    await page.locator('[name="email"]').fill(email);
    await page.locator('[name="password"]').fill('E2e-password-2026');
    await page.locator('button[type="submit"]').click();
    await page.waitForURL('**/dashboard');
}

test('teacher creates objective quiz, student takes it, results persist after deletion', async ({ page, browser }) => {
    test.setTimeout(60000);
    await login(page, 'guru@e2e.test');
    await page.goto('/guru/modules');
    await page.getByRole('link', { name: 'Kelola Kuis', exact: true }).first().click();
    await page.locator('#quiz-title').fill('Latihan VLAN pilihan ganda');
    await page.locator('#question-0').fill('Apa fungsi VLAN?');
    for (const [letter, option] of Object.entries({ A: 'Memisahkan jaringan secara logis', B: 'Mencetak dokumen', C: 'Menambah RAM', D: 'Menampilkan gambar' })) {
        await page.locator(`#option-0-${letter}`).fill(option);
    }
    await page.getByRole('button', { name: 'Tambah Soal', exact: true }).click();
    await page.locator('#question-1').fill('Port yang membawa beberapa VLAN?');
    for (const [letter, option] of Object.entries({ A: 'Access', B: 'Trunk', C: 'USB', D: 'HDMI' })) {
        await page.locator(`#option-1-${letter}`).fill(option);
    }
    await page.locator('[name="questions[1][correct_answer]"]').selectOption('B');
    await page.getByRole('button', { name: 'Tambah Soal', exact: true }).click();
    await page.getByRole('button', { name: 'Hapus Soal', exact: true }).last().click();
    await expect(page.locator('fieldset')).toHaveCount(2);
    await page.getByRole('checkbox', { name: 'Terbitkan kuis untuk siswa' }).check();
    await page.getByRole('button', { name: 'Simpan Kuis', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Kuis diterbitkan');
    await expect(page.locator('#question-1')).toHaveValue('Port yang membawa beberapa VLAN?');
    await expect(page.locator('[name="questions[1][correct_answer]"]')).toHaveValue('B');
    await page.screenshot({ path: resolve(process.env.E2E_FIXTURE_DIR, 'objective-quiz-editor.png'), fullPage: true });

    const studentContext = await browser.newContext();
    try {
        const student = await studentContext.newPage();
        await login(student, 'siswa1@e2e.test');
        await student.goto('/siswa/modules/1');
        await student.getByRole('link', { name: 'Kerjakan Kuis Objektif' }).click();
        expect(await student.content()).not.toContain('correct_answer');
        await student.setViewportSize({ width: 390, height: 844 });
        await student.getByRole('button', { name: 'Buka menu' }).click();
        await expect(student.getByRole('link', { name: 'Petunjuk Siswa', exact: true })).toBeInViewport();
        await student.getByRole('button', { name: 'Tutup menu' }).click();
        await expect(student.getByRole('button', { name: 'Buka menu' })).toHaveAttribute('aria-expanded', 'false');
        await student.setViewportSize({ width: 1280, height: 720 });
        await student.locator('[name="answers[0]"][value="A"]').check();
        await student.locator('[name="answers[1]"][value="D"]').check();
        await student.getByRole('button', { name: 'Kirim Jawaban' }).click();
        await expect(student.getByText('Nilai: 50/100', { exact: true })).toBeVisible();
        await expect(student.getByText('Jawaban benar: B. Trunk', { exact: true })).toBeVisible();
        const resultUrl = student.url();
        await student.reload();
        await expect(student.getByText('Nilai: 50/100', { exact: true })).toBeVisible();
        await student.screenshot({ path: resolve(process.env.E2E_FIXTURE_DIR, 'objective-quiz-result.png'), fullPage: true });

        await page.getByRole('link', { name: 'Lihat Hasil Siswa' }).click();
        await expect(page.getByRole('cell', { name: 'Siswa E2E 1', exact: true })).toBeVisible();
        await expect(page.getByRole('cell', { name: '50/100', exact: true })).toBeVisible();
        await page.getByRole('link', { name: 'Kelola Kuis' }).click();
        await page.getByRole('checkbox', { name: 'Terbitkan kuis untuk siswa' }).uncheck();
        await page.getByRole('button', { name: 'Simpan Kuis', exact: true }).click();
        await expect(page.getByRole('status')).toContainText('draf');
        expect((await student.request.get('/siswa/modules/1/quiz')).status()).toBe(404);
        page.once('dialog', dialog => dialog.accept());
        await page.getByRole('button', { name: 'Hapus Kuis', exact: true }).click();
        await expect(page.getByRole('status')).toContainText('Kuis dihapus');
        await student.goto(resultUrl);
        await expect(student.getByText('Nilai: 50/100', { exact: true })).toBeVisible();
        expect((await student.request.get('/guru/modules/1/quiz')).status()).toBe(403);
    } finally {
        await studentContext.close();
    }
});
