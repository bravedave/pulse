# The Pulse
A tiny, zero‑friction release blog/changelog you can drop into any app. Log what you shipped, embed it anywhere, and keep your users in the loop.

- Lightweight, self‑hostable
- Markdown entries
- Simple JSON API
- Copy‑paste embed

## Why
You’re iterating fast and want a super simple place to post updates—without standing up a full CMS or blog. The Pulse is a minimal release log designed to live inside your product (or docs), not next to it.

## Features
- Minimal JSON storage (file or SQLite—implementation agnostic)
- REST API for listing and posting entries
- Markdown body with optional tags and links
- Pinned entries for “big releases”
- Copy‑paste embed (vanilla JS) or use from any framework
- Token-based admin auth

## Quick start (spec-first)
This README defines the spec so you can implement it in any stack. A reference Node/Express + JSON-file implementation is suggested below, but optional.

1) Decide storage:
- JSON file at .data/pulse.json (recommended to start)
- SQLite (schema below)

2) Implement the API (endpoints below).

3) Drop the embed snippet in your app to render the feed.

4) Automate posting from CI/CD (e.g., on release).

## Data model

Minimum fields:
```json
{
  "id": "ulid-or-uuid",
  "title": "Short title",
  "body": "Markdown content",
  "tags": ["release", "fix"],
  "version": "v1.2.3",
  "links": [
    {"label": "PR #123", "href": "https://github.com/..."},
    {"label": "Docs", "href": "https://..."}
  ],
  "pinned": false,
  "createdAt": "2025-08-08T07:08:17Z",
  "visibility": "public"
}
```

JSON-file storage layout (default):
```json
{
  "entries": [ /* array of objects as above, newest first */ ],
  "lastModified": "2025-08-08T07:08:17Z",
  "version": 1
}
```

SQLite schema (optional):
```sql
CREATE TABLE entries (
  id TEXT PRIMARY KEY,
  title TEXT NOT NULL,
  body TEXT NOT NULL,
  tags TEXT NOT NULL,         -- JSON string array
  version TEXT,               -- nullable
  links TEXT NOT NULL,        -- JSON string array [{label,href}]
  pinned INTEGER NOT NULL,    -- 0/1
  visibility TEXT NOT NULL,   -- 'public'
  createdAt TEXT NOT NULL     -- ISO8601
);
CREATE INDEX idx_entries_createdAt ON entries(createdAt DESC);
CREATE INDEX idx_entries_tags ON entries(tags);
```

## API

Base URL (example): https://yourdomain.com

- GET /api/pulse
  - Query params:
    - limit: number (default 20, max 100)
    - tag: string (filter entries that include the tag)
    - include_pinned: boolean (default true; pinned always appear first)
  - Response: 200 application/json
    ```json
    { "entries": [/* Entry[] */] }
    ```

- POST /api/pulse
  - Auth: X-Pulse-Token: <ADMIN_TOKEN>
  - Body: Partial<Entry> (title, body required; server sets id/createdAt)
  - Response: 201 with created entry
  - Example:
    ```json
    {
      "title": "Dark mode",
      "body": "Added dark mode toggle.\n\n- Auto-detects OS\n- Keyboard shortcut: `D`",
      "tags": ["ui"],
      "version": "v0.5.0",
      "links": [{"label":"PR #45","href":"https://github.com/.../pull/45"}],
      "pinned": false,
      "visibility": "public"
    }
    ```

- DELETE /api/pulse/:id
  - Auth: X-Pulse-Token: <ADMIN_TOKEN>
  - Response: 204

- PUT /api/pulse/:id/pin
  - Auth: X-Pulse-Token: <ADMIN_TOKEN>
  - Body: { "pinned": true }
  - Response: 200 with updated entry

Notes:
- All timestamps are ISO8601 UTC.
- Rate-limit as needed.
- CORS: enable GET for embed origins; lock down POST/DELETE/PUT.

## Example requests (cURL)

Create an entry:
```bash
curl -X POST https://yourdomain.com/api/pulse \
  -H "Content-Type: application/json" \
  -H "X-Pulse-Token: $PULSE_ADMIN_TOKEN" \
  -d '{
    "title":"First public beta",
    "body":"We opened the beta to early users.\n\n- Invitations sent\n- Feedback board live",
    "tags":["beta","announcement"],
    "version":"v0.9.0",
    "links":[{"label":"Docs","href":"https://docs.example.com/beta"}],
    "pinned":true
  }'
```

List entries:
```bash
curl "https://yourdomain.com/api/pulse?limit=10&tag=announcement"
```

Delete:
```bash
curl -X DELETE https://yourdomain.com/api/pulse/01J3W5W2C2Y9E7CJ2A3M8M2X6Z \
  -H "X-Pulse-Token: $PULSE_ADMIN_TOKEN"
```

## Embed (vanilla JS)

Drop this wherever you want the feed to show (product dashboard, docs, landing page).

```html
<div id="pulse-feed"></div>
<script>
(async function () {
  const res = await fetch('/api/pulse?limit=20');
  const { entries } = await res.json();
  const root = document.getElementById('pulse-feed');

  const tpl = (e) => `
    <article class="pulse-entry">
      <header>
        <h3>${e.title}</h3>
        <time datetime="${e.createdAt}">
          ${new Date(e.createdAt).toLocaleDateString()}
        </time>
        ${e.version ? `<span class="pulse-version">${e.version}</span>` : ''}
        ${e.pinned ? `<span class="pulse-pin">Pinned</span>` : ''}
      </header>
      <div class="pulse-body">${marked ? marked.parse(e.body) : e.body}</div>
      ${Array.isArray(e.tags) && e.tags.length ? `
        <ul class="pulse-tags">
          ${e.tags.map(t => `<li>#${t}</li>`).join('')}
        </ul>` : ''
      }
      ${Array.isArray(e.links) && e.links.length ? `
        <ul class="pulse-links">
          ${e.links.map(l => `<li><a href="${l.href}" target="_blank" rel="noreferrer">${l.label}</a></li>`).join('')}
        </ul>` : ''
      }
    </article>
  `;

  root.innerHTML = entries.map(tpl).join('');

  // Optional: load a markdown parser like marked.js for nicer rendering.
  // <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
})();
</script>

<style>
#pulse-feed { --fg:#1f2937; --muted:#6b7280; --bg:#fff; --accent:#22c55e; font: 14px/1.5 system-ui, sans-serif; color:var(--fg); }
.pulse-entry { border:1px solid #e5e7eb; border-radius:10px; padding:14px 16px; margin:12px 0; background:var(--bg); }
.pulse-entry header { display:flex; gap:8px; align-items:baseline; flex-wrap:wrap; }
.pulse-entry h3 { margin:0; font-size:15px; }
.pulse-entry time { color:var(--muted); font-size:12px; }
.pulse-version { color:#0ea5e9; font-weight:600; font-size:12px; }
.pulse-pin { background:var(--accent); color:#053; border-radius:6px; padding:2px 6px; font-size:11px; }
.pulse-body { margin-top:8px; white-space:pre-wrap; }
.pulse-tags, .pulse-links { display:flex; gap:8px; list-style:none; padding:0; margin:8px 0 0; flex-wrap:wrap; }
.pulse-tags li { color:#16a34a; background:#ecfdf5; border:1px solid #bbf7d0; padding:2px 6px; border-radius:6px; font-size:11px; }
</style>
```

Tip: If you need to embed cross-origin (e.g., from a marketing site), expose CORS for GET or host a static JSON at /pulse.json that mirrors GET /api/pulse.

## CI-friendly: post from your pipeline

Example GitHub Actions step that posts a new entry on release (pseudo-command):

```yaml
- name: Post release notes to The Pulse
  run: |
    curl -X POST "$PULSE_BASE_URL/api/pulse" \
      -H "Content-Type: application/json" \
      -H "X-Pulse-Token: $PULSE_ADMIN_TOKEN" \
      -d @- <<'JSON'
      {
        "title": "Release $GITHUB_REF_NAME",
        "body": "See changelog: $GITHUB_SERVER_URL/$GITHUB_REPOSITORY/releases/tag/$GITHUB_REF_NAME",
        "tags": ["release"],
        "version": "$GITHUB_REF_NAME",
        "links": [{"label":"Release","href":"$GITHUB_SERVER_URL/$GITHUB_REPOSITORY/releases/tag/$GITHUB_REF_NAME"}],
        "pinned": false
      }
JSON
  env:
    PULSE_BASE_URL: https://yourdomain.com
    PULSE_ADMIN_TOKEN: ${{ secrets.PULSE_ADMIN_TOKEN }}
```

## Configuration

Environment variables (suggested):
- PULSE_ADMIN_TOKEN: required for write endpoints (generate a long random string)
- PULSE_STORAGE_PATH: path to JSON file (default ./.data/pulse.json)
- PULSE_BASE_URL: used in emails or links (optional)
- PULSE_CORS_ORIGINS: comma-separated origins allowed for GET (optional)

Operational notes:
- Persist .data/ in your deployment (Docker volume or bind mount).
- Back up the JSON file or DB.
- Consider basic rate limiting and request size limits.

## Reference implementation (Node + JSON file)
If you choose Node:
- Node 20+, npm/yarn/pnpm
- Scripts you might add:
  - dev: ts-node-dev src/server.ts
  - start: node dist/server.js
  - build: tsc -p .
- Minimal endpoints (Express/Fastify) to match the API above.
- File operations are append-preferential; write the whole JSON safely (temp file + atomic rename).

Directory suggestion:
```
/src
  server.ts
  storage/
    fileStore.ts
  api/
    pulse.ts
  domain/
    types.ts
/data (gitignored)
/public (optional embed assets)
```

## Roadmap
- [ ] Optional web admin UI (create/edit/pin)
- [ ] RSS/Atom feed
- [ ] Import from GitHub releases
- [ ] Web Component <the-pulse> widget
- [ ] Search and tag filters in embed
- [ ] Draft/private visibility
- [ ] Rate limiting and audit log

## Contributing
PRs and issues welcome. Keep it tiny and pragmatic. Please discuss non-trivial additions first to stay within scope.

## License
MIT