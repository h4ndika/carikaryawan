## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

## Multi-Agent Git Workflow Rules
Setiap kali mendelegasikan tugas ke agent (Frontend, Backend, dll.):
1. **Awal Pekerjaan**: Agent WAJIB menjalankan `git pull origin main` terlebih dahulu sebelum mulai bekerja atau membuat branch.
2. **Branching & PR**:
   - Untuk fitur baru / perubahan besar: Pekerjaan dilakukan pada branch khusus (misal: `feat/backend-...`, `feat/frontend-...`), push branch, dan buat Pull Request (PR) ke branch `main`.
   - Untuk perbaikan kecil / bugfix minor: Boleh langsung commit dan `git push origin main` tanpa perlu membuat branch dan PR baru.
3. **Deployment**: Setelah ada update di `main`, lakukan sinkronisasi (`git pull origin main && php artisan optimize:clear`) di VPS.
