/**
 * Inline header.html + footer.html into every page as static HTML, so
 * crawlers and link-preview bots see the navigation and footer without JS.
 *
 * Safe to re-run: replaces either the old JS placeholders or a previously
 * inlined block between the HEADER/FOOTER markers.
 *
 *   node tools/inline-components.js
 */
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const SKIP = new Set(['header.html', 'footer.html', '404.html', 'model_comparison.html']);

const header = fs.readFileSync(path.join(ROOT, 'header.html'), 'utf8').trim();
const footer = fs.readFileSync(path.join(ROOT, 'footer.html'), 'utf8').trim();

function block(name, html) {
    return `<!-- ${name}:START (generated from ${name.toLowerCase()}.html by tools/inline-components.js) -->\n${html}\n<!-- ${name}:END -->`;
}

function replaceSlot(page, name, placeholderId, html) {
    const marked = new RegExp(`<!-- ${name}:START[^>]*-->[\\s\\S]*?<!-- ${name}:END -->`);
    const placeholder = new RegExp(`<div id="${placeholderId}"></div>`);
    if (marked.test(page)) return page.replace(marked, () => block(name, html));
    if (placeholder.test(page)) return page.replace(placeholder, () => block(name, html));
    return null;
}

let changed = 0;
for (const file of fs.readdirSync(ROOT).filter(f => f.endsWith('.html') && !SKIP.has(f))) {
    const full = path.join(ROOT, file);
    const orig = fs.readFileSync(full, 'utf8');

    // Highlight the current page in the menu (blog posts highlight "Blog").
    let key = file.replace(/\.html$/, '');
    if (key.startsWith('blog-')) key = 'blog';
    const activeHeader = header.replace(
        new RegExp(`class="navbar__link" data-page="${key}"`),
        `class="navbar__link navbar__link--active" data-page="${key}" aria-current="page"`
    );

    let page = replaceSlot(orig, 'HEADER', 'header-placeholder', activeHeader);
    if (page === null) continue;
    page = replaceSlot(page, 'FOOTER', 'footer-placeholder', footer) ?? page;

    if (page !== orig) {
        fs.writeFileSync(full, page);
        changed++;
    }
}
console.log(`Inlined header/footer into ${changed} page(s).`);
