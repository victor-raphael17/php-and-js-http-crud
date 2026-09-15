# Relatório de correções

Este relatório lista tudo que foi corrigido no código do projeto em setembro de 2026, depois de uma revisão completa do repositório. Todas as correções também foram aplicadas ao material do tutorial, então o que você lê nos módulos é o código já corrigido. A ordem dentro de cada grupo é da falha mais grave para a mais sutil.

## Backend — a API PHP

### `src/controllers.php + src/services.php`

**O que mudou.** O `PUT` voltou a funcionar: `editUser()` tem de novo o valor padrão em `$partial` e o `handlePatch` passa `partial: true` como argumento nomeado.

**Por quê.** **Toda** requisição `PUT` respondia `500`. A função exigia 4 argumentos e o `handlePut` passava 3, o que faz o PHP lançar `ArgumentCountError` — capturado pelo `catch (\Throwable)` e transformado em erro interno. Na interface, editar os três campos de uma vez nunca salvava.

### `public/index.php`

**O que mudou.** Passou a enviar `header('Content-Type: application/json')` em todas as respostas.

**Por quê.** A API devolvia JSON anunciado como `text/html`. O Axios continuava entendendo, mas qualquer outro cliente (curl, Insomnia, outro serviço) recebia a informação errada sobre o que estava lendo.

### `src/controllers.php`

**O que mudou.** A mensagem da exceção parou de ir na resposta. Agora `respondServerError()` grava o detalhe com `error_log()` e devolve só `"Internal server error"`.

**Por quê.** O cliente recebia coisas como `Internal server errorToo few arguments to function editUser(), 3 passed in /app/src/controllers.php on line 43` — caminhos internos e estrutura do código expostos a quem chamasse a API.

### `src/validation.php`

**O que mudou.** `is_string()` antes do `trim()` (no `name`) e do `filter_var()` (no `email`).

**Por quê.** Um corpo como `{"name": ["a"]}` fazia o `trim()` receber um array, lançar `TypeError` e a API responder `500`. Erro do cliente tem de virar `400` com explicação, não erro do servidor.

### `src/validation.php + src/services.php`

**O que mudou.** Novo `validateUserId()`: o `id` da query string é validado com `ctype_digit()` antes de virar inteiro. O controller repassa `$_GET['id'] ?? null` cru.

**Por quê.** `(int) 'abc'` é `0`, então `?id=abc` virava uma busca pelo usuário 0 e respondia `404 User not found`. O certo é `400`: o problema não é o usuário não existir, é o id ser inválido.

### `src/data.php`

**O que mudou.** `loadData()` devolve o estado vazio quando o arquivo não existe, não pode ser lido ou tem JSON inválido.

**Por quê.** No tutorial a função era `return json_decode(file_get_contents(DATA_FILE), true);` com tipo de retorno `: array`. Com o arquivo ausente ou corrompido, o `json_decode` devolve `null`, o tipo não aceita e **toda** rota passa a responder `500` — inclusive o GET.

### `src/data.php`

**O que mudou.** Novo `withDataLock()`: as escritas passam por `flock($lock, LOCK_EX)`.

**Por quê.** Cada escrita é um ciclo ler → alterar → gravar. Duas requisições simultâneas liam o mesmo arquivo e a segunda gravação apagava a primeira — dois cadastros ao mesmo tempo, um usuário salvo. Verificado com 30 POSTs em paralelo: antes havia perda, depois os 30 são gravados com ids únicos.

### `src/validation.php`

**O que mudou.** `mb_strlen()` no lugar de `strlen()` no limite de 100 caracteres do nome.

**Por quê.** `strlen()` conta bytes. Em UTF-8 um caractere acentuado ocupa 2, então nomes com acento eram rejeitados antes de chegar aos 100 caracteres que a mensagem promete.

### `src/services.php`

**O que mudou.** Um `PATCH` cujo corpo não traz nenhum campo conhecido agora responde `400`.

**Por quê.** `PATCH ?id=1` com `{}` respondia `200` e devolvia o usuário intacto, como se algo tivesse sido atualizado. Silêncio em cima de um pedido sem sentido esconde o bug de quem chamou.

### `src/validation.php`

**O que mudou.** A mensagem de campo obrigatório concorda em número: `"name is required"` no singular, `"name, age are required"` no plural.

**Por quê.** Faltando um único campo, a API respondia `"name are required"`. Não quebra nada — mas é a mensagem que o usuário lê na tela.

### `src/controllers.php`

**O que mudou.** `handleGet()` passou a usar o `respond()`, e a leitura do corpo foi extraída para `readJsonBody()`.

**Por quê.** O GET era o único handler que montava a resposta por conta própria, com `echo json_encode(...)` direto. Padronizar tira o caso especial e evita que ele fique para trás na próxima mudança.

## Frontend

### `scripts/dom/render.js`

**O que mudou.** Novo `escapeHtml()`: nome, e-mail, idade e id passam por ele antes de entrar no `innerHTML` do card.

**Por quê.** Era um **XSS armazenado**. Um usuário cadastrado como `<img src=x onerror=alert(1)>` não aparecia como texto: o navegador tentava carregar a imagem, falhava e executava o `onerror` — na máquina de *todo mundo* que abrisse a lista.

### `app.js`

**O que mudou.** Todas as chamadas de `renderUsers()` passaram a ser `await`, e a primeira renderização ganhou `try/catch` com mensagem na tela.

**Por quê.** `renderUsers()` é `async`. Sem `await`, a Promise rejeitada não era pega por nenhum `catch`: com a API fora do ar, a página ficava em branco e o erro só aparecia no console do DevTools.

### `app.js`

**O que mudou.** O endereço da API virou `import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api/users'`.

**Por quê.** O endereço estava cravado no código: publicar o frontend em outro lugar exigia editar o `app.js`. Sem nenhuma configuração, o comportamento continua exatamente o mesmo de antes.

## Dados e infraestrutura

### `data/data.json`

**O que mudou.** O arquivo foi restaurado: agora é um JSON válido com o usuário de exemplo.

**Por quê.** O arquivo versionado tinha um JSON válido **seguido de um dump de array PHP colado por engano**, o que o tornava inválido inteiro. O `json_decode` devolvia `null` e a API tratava tudo como banco vazio, sem avisar ninguém. Havia ainda dois usuários com o mesmo `id`.

### `compose.yaml`

**O que mudou.** Volume nomeado `api-data` montado em `/app/data`.

**Por quê.** Sem volume, cada `docker compose up --build` apagava tudo que tinha sido cadastrado. O passo a passo está no módulo **Bônus**.

### `api/openapi.json`

**O que mudou.** Arquivo removido do projeto.

**Por quê.** Era uma especificação OpenAPI paralela ao código, que precisaria ser mantida à mão a cada mudança da API — e que já divergia da documentação do próprio repositório.

## O que continua igual

- **O contrato HTTP.** As rotas, os métodos, os formatos de corpo e os códigos de sucesso continuam iguais. Um frontend escrito contra a API antiga funciona com a nova.
- **A arquitetura em camadas.** Nenhum arquivo novo, nenhuma camada a mais: as correções ficaram na camada onde o problema estava.
- **O fluxo de trabalho.** `npm install` e `npm run build` antes do `docker compose up` continuam sendo o caminho principal. Automatizar isso é o bônus 2, opcional.
