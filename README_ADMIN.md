# API de Reserva de Viagens - Guia do Administrador

## Introdução

Este guia detalha as funcionalidades exclusivas disponíveis para administradores na API de Reserva de Viagens. Os administradores têm acesso a operações CRUD completas em todos os recursos do sistema.

## Autenticação

A autenticação para administradores é idêntica à dos utilizadores comuns, utilizando JWT (JSON Web Token). A diferença está nas permissões concedidas após a autenticação.

### Login como Administrador

```
POST /api/login
```

**Corpo da requisição:**
```json
{
  "email": "admin@exemplo.com",
  "password": "senha_admin"
}
```

**Resposta de sucesso:**
```json
{
  "access_token": "seu_token_jwt",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@exemplo.com",
    "role": "admin"
  }
}
```

## Gestão de Utilizadores

Os administradores têm acesso completo à gestão de utilizadores.

### Listar Todos os Utilizadores

```
GET /api/users
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

### Ver Detalhes de um Utilizador

```
GET /api/users/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

### Criar Novo Utilizador

```
POST /api/users
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Corpo da requisição:**
```json
{
  "name": "Novo Utilizador",
  "email": "novo@exemplo.com",
  "password": "senha123",
  "role": "user"  // Pode ser "user" ou "admin"
}
```

### Atualizar Utilizador

```
PUT /api/users/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Corpo da requisição:**
```json
{
  "name": "Nome Atualizado",
  "email": "atualizado@exemplo.com",
  "password": "nova_senha",
  "role": "admin"  // Opcional, para alterar o papel do utilizador
}
```

### Eliminar Utilizador

```
DELETE /api/users/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Nota:** Administradores não podem eliminar suas próprias contas por motivos de segurança.

## Gestão de Viagens

Os administradores têm controle total sobre as viagens disponíveis no sistema.

### Criar Nova Viagem

```
POST /api/viagens
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Corpo da requisição:**
```json
{
  "destino": "Paris",
  "data_partida": "2023-12-01",
  "data_regresso": "2023-12-10",
  "preco": 1200.50
}
```

### Atualizar Viagem

```
PUT /api/viagens/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Corpo da requisição:**
```json
{
  "destino": "Roma",
  "data_partida": "2023-12-05",
  "data_regresso": "2023-12-15",
  "preco": 1500.75
}
```

### Eliminar Viagem

```
DELETE /api/viagens/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

## Gestão de Reservas

Os administradores podem gerir todas as reservas do sistema, independentemente do utilizador.

### Listar Todas as Reservas (Admin)

```
GET /api/reservas/admin
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

### Ver Detalhes de Qualquer Reserva

```
GET /api/reservas/admin/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

### Criar Reserva para Qualquer Utilizador

```
POST /api/reservas/admin
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Corpo da requisição:**
```json
{
  "user_id": 3,
  "viagem_id": 2,
  "lugares": 2
}
```

### Atualizar Qualquer Reserva

```
PUT /api/reservas/admin/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Corpo da requisição:**
```json
{
  "user_id": 3,
  "viagem_id": 4,
  "lugares": 3
}
```

### Eliminar Qualquer Reserva

```
DELETE /api/reservas/admin/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

## Notas Importantes para Administradores

1. Todas as operações administrativas são protegidas pelo middleware `is.admin`, que verifica se o utilizador autenticado tem o papel de administrador.

2. As operações administrativas são registadas no sistema para fins de auditoria.

3. Administradores não podem eliminar suas próprias contas por motivos de segurança.

4. Ao eliminar um utilizador, todas as suas reservas são automaticamente eliminadas.

5. Ao eliminar uma viagem, todas as reservas associadas a essa viagem são automaticamente eliminadas.

6. Os administradores têm acesso a todas as funcionalidades disponíveis para utilizadores comuns, além das funcionalidades administrativas.

## Tratamento de Erros

A API retorna códigos de status HTTP apropriados junto com mensagens de erro descritivas:

- **400** - Requisição inválida
- **401** - Não autenticado
- **403** - Acesso negado (tentativa de acesso a recurso administrativo por utilizador comum)
- **404** - Recurso não encontrado
- **422** - Erro de validação
- **500** - Erro interno do servidor