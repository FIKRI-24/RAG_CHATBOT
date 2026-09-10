import { marked } from 'marked';
import DOMPurify from 'dompurify';

export function renderRagMarkdown(text) {
    return DOMPurify.sanitize(marked.parse(String(text ?? '')), {
        ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'del', 'ul', 'ol', 'li', 'blockquote',
            'pre', 'code', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'h1', 'h2', 'h3', 'h4', 'hr', 'a'],
        ALLOWED_ATTR: ['href', 'title', 'start'],
        ALLOW_DATA_ATTR: false,
    });
}
