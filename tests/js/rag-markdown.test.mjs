import { test } from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { readFile } from 'node:fs/promises';

const dom = new JSDOM('<!doctype html><html><body></body></html>');
globalThis.window = dom.window;
const { renderRagMarkdown } = await import('../../resources/js/rag-markdown.js');

test('removes executable HTML, unsafe links, forms and CSS from model output', () => {
    const attacks = [
        '<img src=x onerror="alert(1)">', '<script>alert(1)</script>',
        '<svg onload="alert(1)"><a href="javascript:alert(1)">x</a></svg>',
        '[klik](javascript:alert%281%29)', '<a href="java&#x73;cript:alert(1)">x</a>',
        '<iframe srcdoc="<script>alert(1)</script>"></iframe>',
        '<form id="chat-form"><input name="action"></form>',
        '<p style="position:fixed" onclick="alert(1)" id="chat-container">x</p>',
    ];
    for (const attack of attacks) {
        const output = new JSDOM(renderRagMarkdown(attack)).window.document;
        assert.equal(output.querySelector('script,iframe,img,svg,form,input'), null);
        for (const element of output.querySelectorAll('*')) {
            for (const attr of element.attributes) {
                assert.ok(!/^on|^style$|^id$/i.test(attr.name));
                if (attr.name === 'href') assert.ok(!/^javascript:/i.test(attr.value));
            }
        }
    }
});

test('retains learning content: tables, lists, code and normal links', () => {
    const result = renderRagMarkdown('**VLAN**\n\n- Guru\n- Siswa\n\n```text\ninterface vlan 10\n```\n\n| VLAN | Fungsi |\n| --- | --- |\n| 10 | Guru |\n\n[Modul](https://example.com/modul)');
    const doc = new JSDOM(result).window.document;
    assert.equal(doc.querySelector('strong').textContent, 'VLAN');
    assert.equal(doc.querySelectorAll('li').length, 2);
    assert.match(doc.querySelector('code').textContent, /interface vlan 10/);
    assert.equal(doc.querySelector('td').textContent, '10');
    assert.equal(doc.querySelector('a').getAttribute('href'), 'https://example.com/modul');
});

test('chat UI sends explicit quiz actions, shows sources safely, and allows cancellation', async () => {
    // Execute the actual inline chat script with server-side Blade values replaced by fixtures.
    const blade = await readFile(new URL('../../resources/views/siswa/dashboard.blade.php', import.meta.url), 'utf8');
    const html = blade
        .replace("{{ Illuminate\\Support\\Js::from(auth()->user()->avatar_url ?? '') }}", JSON.stringify('https://example.com/avatar.png'))
        .replace("{{ Illuminate\\Support\\Js::from(mb_strtoupper(mb_substr(auth()->user()->name, 0, 1))) }}", JSON.stringify('S'))
        .replace("{{ Illuminate\\Support\\Js::from(auth()->user()->name) }}", JSON.stringify('Student ` ${globalThis.auditMarker=123} <img src=x onerror=alert(1)>'))
        .replace(/@json\(\$activeQuiz\?->id\)/g, 'null').replace(/\{\{[\s\S]*?\}\}/g, 'fixture');
    const page = new JSDOM(html, { runScripts: 'outside-only', url: 'http://localhost/' });
    const { window } = page;
    await new Promise(resolve => window.document.addEventListener('DOMContentLoaded', resolve, { once: true }));
    window.HTMLElement.prototype.scrollIntoView = () => {};
    window.renderRagMarkdown = renderRagMarkdown;
    const calls = [];
    window.fetch = async (_url, options) => {
        const request = JSON.parse(options.body);
        calls.push(request);
        return { ok: true, status: 200, json: async () => ({ success: true, data: {
            jawaban: '<img src=x onerror="alert(1)">Soal VLAN', created_at: '10:00',
            quiz_id: request.action === 'quiz' ? 77 : null,
            sources: [{ judul: '<img src=x onerror=alert(1)>', mapel: 'Jaringan', kb_nomor: 'KB 1', text: 'Materi VLAN' }],
        } }) };
    };
    window.eval(window.document.querySelector('script').textContent);
    window.document.dispatchEvent(new window.Event('DOMContentLoaded'));
    const field = window.document.getElementById('pertanyaan');
    const form = window.document.getElementById('chat-form');
    const submit = async value => {
        field.value = value;
        form.dispatchEvent(new window.Event('submit', { cancelable: true }));
        await new Promise(resolve => setTimeout(resolve, 0));
    };
    await submit('[LATIHAN_SOAL]');
    assert.equal(calls.at(-1).action, 'quiz');
    assert.equal(window.document.getElementById('quiz-state').classList.contains('hidden'), false);
    await submit('Memisahkan jaringan');
    assert.equal(calls.at(-1).action, 'quiz_answer');
    assert.equal(calls.at(-1).quiz_id, 77);
    assert.equal(window.document.getElementById('quiz-state').classList.contains('hidden'), true);
    await submit('[LATIHAN_SOAL]');
    window.document.getElementById('cancel-quiz').click();
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(calls.at(-1).action, 'cancel_quiz');
    await submit('Apa itu VLAN?');
    assert.equal(calls.at(-1).action, 'ask');
    assert.equal(window.document.querySelector('[onerror]'), null);
    assert.equal(window.auditMarker, undefined);
    assert.ok([...window.document.querySelectorAll('img[alt]')].some(img => img.alt.includes('${globalThis.auditMarker=123}')));
    assert.match(window.document.body.textContent, /Materi VLAN/);
    page.window.close();
});
