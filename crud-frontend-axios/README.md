# CRUD JS Project - Frontend

Frontend de um sistema CRUD de usuarios, desenvolvido com **JavaScript vanilla** e **Bootstrap 5**. Este repositorio contem apenas o frontend — voce deve desenvolver a **API (backend)** que atenda aos requisitos documentados abaixo.

## Sumario

- [Visao Geral](#visao-geral)
- [Como Rodar o Frontend](#como-rodar-o-frontend)
- [Especificacao da API](#especificacao-da-api)
  - [Base URL](#base-url)
  - [Modelo de Dados](#modelo-de-dados)
  - [Endpoints](#endpoints)
  - [Formato de Erro](#formato-de-erro)
  - [CORS](#cors)
- [Exemplos de Requisicao e Resposta](#exemplos-de-requisicao-e-resposta)
- [Checklist de Implementacao](#checklist-de-implementacao)

---

## Visao Geral

O frontend exibe uma lista de usuarios em cards e permite criar, editar e excluir usuarios atraves de um formulario. Toda a comunicacao com o backend e feita via **Axios** usando **JSON**.

**Tecnologias do frontend:**
- HTML5, CSS3, JavaScript (ES Modules)
- Bootstrap 5.3.8 (via CDN)
- Docker (Apache httpd:alpine)

---

## Como Rodar o Frontend

### Pre-requisitos

- [Node.js](https://nodejs.org/) (para instalar dependencias e fazer o build)
- [Docker](https://www.docker.com/) (opcional, para servir com Apache)

### 1. Instalar dependencias

```bash
npm install
```

### 2. Desenvolvimento local (com Vite)

```bash
npm run dev
```

O Vite iniciara um servidor de desenvolvimento com hot reload.

### 3. Build de producao

```bash
npm run build
```

O build sera gerado na pasta `dist/`.

### 4. Rodar com Docker

Depois de gerar o build, suba o container:

```bash
docker compose up --build
```

O frontend estara disponivel em `http://localhost:8080`.

> **Importante:** O frontend espera que a API esteja rodando em `http://localhost:8000`.

---

## Especificacao da API

Este e o contrato que sua API **deve** seguir para funcionar com o frontend.

### Base URL

```
http://localhost:8000/api/users
```

Sua API deve escutar na **porta 8000** e expor o recurso no caminho `/api/users`.

### Modelo de Dados

A entidade gerenciada e **User** com os seguintes campos:

| Campo   | Tipo     | Descricao              | Obrigatorio |
|---------|----------|------------------------|-------------|
| `id`    | `number` | Identificador unico    | Gerado pela API |
| `name`  | `string` | Nome do usuario        | Sim |
| `age`   | `number` | Idade (numero inteiro) | Sim |
| `email` | `string` | E-mail do usuario      | Sim |

### Endpoints

Sua API deve implementar os **5 endpoints** abaixo:

---

#### 1. Listar usuarios

```
GET /api/users
```

**Resposta (200):**

```json
{
  "users": [
    { "id": 1, "name": "Ana", "age": 25, "email": "ana@email.com" },
    { "id": 2, "name": "Carlos", "age": 30, "email": "carlos@email.com" }
  ]
}
```

> A resposta **deve** ser um objeto com a chave `users` contendo um array. Se nao houver usuarios, retorne `{ "users": [] }`.

---

#### 2. Criar usuario

```
POST /api/users
Content-Type: application/json
```

**Corpo da requisicao:**

```json
{
  "name": "Ana",
  "age": 25,
  "email": "ana@email.com"
}
```

**Resposta de sucesso (201):** retorne o usuario criado (com `id` gerado).

```json
{
  "id": 1,
  "name": "Ana",
  "age": 25,
  "email": "ana@email.com"
}
```

**Resposta de erro (400/422):**

```json
{
  "error": "Descricao do erro"
}
```

---

#### 3. Atualizar usuario (substituicao total)

```
PUT /api/users?id={id}
Content-Type: application/json
```

**Corpo da requisicao (todos os campos):**

```json
{
  "name": "Ana Silva",
  "age": 26,
  "email": "ana.silva@email.com"
}
```

**Resposta de sucesso (200):** retorne o usuario atualizado.

**Resposta de erro (404):**

```json
{
  "error": "User not found"
}
```

> O `id` e passado como **query parameter** (`?id=1`), **nao** no corpo da requisicao.

---

#### 4. Atualizar usuario (parcial)

```
PATCH /api/users?id={id}
Content-Type: application/json
```

**Corpo da requisicao (apenas os campos alterados):**

```json
{
  "age": 27
}
```

O corpo pode conter **1 ou 2** campos (nunca os 3 — nesse caso o frontend usa PUT). Os campos nao enviados devem permanecer inalterados.

**Resposta de sucesso (200):** retorne o usuario atualizado.

**Resposta de erro (404):**

```json
{
  "error": "User not found"
}
```

---

#### 5. Deletar usuario

```
DELETE /api/users?id={id}
```

**Sem corpo na requisicao.**

**Resposta de sucesso (200):**

```json
{
  "message": "User deleted"
}
```

**Resposta de erro (404):**

```json
{
  "error": "User not found"
}
```

> O `id` e passado como **query parameter** (`?id=1`).

---

### Formato de Erro

Em caso de erro, a API deve retornar um JSON com a chave `error`:

```json
{
  "error": "Mensagem descrevendo o erro"
}
```

O frontend le `data.error` para exibir a mensagem ao usuario. Use um **status HTTP adequado** (400, 404, 422, 500, etc).

### CORS

Como o frontend roda em `localhost:8080` e a API em `localhost:8000`, sua API **deve** configurar os headers CORS para permitir requisicoes cross-origin:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type
```

> Sem CORS configurado, o navegador bloqueara todas as requisicoes.

---

## Exemplos de Requisicao e Resposta

### Criar

```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name": "Maria", "age": 22, "email": "maria@email.com"}'
```

### Listar

```bash
curl http://localhost:8000/api/users
```

### Atualizar (PUT)

```bash
curl -X PUT "http://localhost:8000/api/users?id=1" \
  -H "Content-Type: application/json" \
  -d '{"name": "Maria Santos", "age": 23, "email": "maria.santos@email.com"}'
```

### Atualizar (PATCH)

```bash
curl -X PATCH "http://localhost:8000/api/users?id=1" \
  -H "Content-Type: application/json" \
  -d '{"age": 24}'
```

### Deletar

```bash
curl -X DELETE "http://localhost:8000/api/users?id=1"
```

---

## Checklist de Implementacao

Use esta lista para verificar se sua API atende a todos os requisitos:

- [ ] API rodando na porta **8000**
- [ ] Rota base: `/api/users`
- [ ] **GET** retorna `{ "users": [...] }`
- [ ] **POST** cria usuario com `name`, `age` (number) e `email`
- [ ] **PUT** atualiza todos os campos (id via query param)
- [ ] **PATCH** atualiza campos parciais (id via query param)
- [ ] **DELETE** remove usuario (id via query param)
- [ ] Respostas em **JSON** com `Content-Type: application/json`
- [ ] Erros retornam `{ "error": "mensagem" }` com status HTTP adequado
- [ ] **CORS** configurado para aceitar requisicoes de `localhost:8080`
- [ ] Campo `id` gerado automaticamente pela API
- [ ] Campo `age` armazenado como **numero** (nao string)

---

## Estrutura do Frontend (referencia)

```
src/
├── index.html              # Pagina principal
├── app.js                  # Logica da aplicacao (formulario, eventos)
├── scripts/
│   ├── api/
│   │   ├── create.js       # POST /api/users
│   │   ├── read.js         # GET /api/users
│   │   ├── update.js       # PUT e PATCH /api/users?id={id}
│   │   └── delete.js       # DELETE /api/users?id={id}
│   └── dom/
│       └── render.js       # Renderiza os cards de usuarios
└── styles/
    ├── reset.css           # Reset CSS
    └── style.css           # Estilos customizados
```
