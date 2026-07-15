
<!-- taskflow:comms -->
## TaskFlow — comunicação de tasks (agentes) — OBRIGATÓRIO

Este projeto está ligado ao **TaskFlow**, o quadro central de tasks em
`/var/www/html/taskflow`. O server MCP `taskflow` está registrado no `.mcp.json`
deste projeto — o Claude Code o inicia sozinho (stdio).

> ⚠️ **OBRIGATÓRIO — não é opcional.** Toda unidade de trabalho neste projeto **DEVE**
> ter uma task correspondente no TaskFlow, criada **antes** de começar e mantida
> atualizada (status + checklist) **enquanto** avança. Trabalho sem task não existe no
> quadro. Isto vale para qualquer agente que atue aqui.

**Ferramentas MCP do TaskFlow:**

- `create_task` — uma por unidade de trabalho. Args: `project: "payment-api-sdk"`, `title`,
  `category` (ex: backend/frontend/infra/bug/docs/feature), `subtasks[]` (checklist inicial).
- `update_task_status` — mova a task conforme avança: `backlog → todo → doing → review → done`.
- `toggle_subtask` — marque itens do checklist (dirige a barra de progresso).
- `list_tasks` / `list_projects` — consulte antes de criar duplicatas.

Use sempre `project: "payment-api-sdk"` para este projeto. O progresso de uma task é
`subtasks feitas / total` (100% sem subtasks só quando `done`).

Ver o quadro: `cd /var/www/html/taskflow && php artisan serve` → http://127.0.0.1:8000
<!-- taskflow:comms -->
