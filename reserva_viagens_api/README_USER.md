# API de Reserva de Viagens - Guia do Utilizador

## Introdução

Esta API permite gerir reservas de viagens, oferecendo funcionalidades para registo, autenticação e gestão de reservas. Este guia explica como utilizar a API como utilizador regular.

## Autenticação

A API utiliza autenticação JWT (JSON Web Token). Para aceder à maioria dos endpoints, é necessário incluir um token válido no cabeçalho das requisições.

### Registo de Utilizador

```
POST /api/register
```

**Corpo da requisição:**
```json
{
  "name": "Nome do Utilizador",
  "email": "utilizador@exemplo.com",
  "password": "senha123"
}
```

**Resposta de sucesso:**
```json
{
  "message": "Utilizador registado com sucesso!",
  "access_token": "seu_token_jwt",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Nome do Utilizador",
    "email": "utilizador@exemplo.com",
    "role": "user"
  }
}
```

### Login

```
POST /api/login
```

**Corpo da requisição:**
```json
{
  "email": "utilizador@exemplo.com",
  "password": "senha123"
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
    "name": "Nome do Utilizador",
    "email": "utilizador@exemplo.com",
    "role": "user"
  }
}
```

### Logout

```
POST /api/logout
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Resposta de sucesso:**
```json
{
  "message": "Logout realizado com sucesso"
}
```

## Perfil do Utilizador

### Ver Perfil

```
GET /api/perfil
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Resposta de sucesso:**
```json
{
  "id": 1,
  "name": "Nome do Utilizador",
  "email": "utilizador@exemplo.com",
  "role": "user"
}
```

### Eliminar Conta

```
DELETE /api/user
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Resposta de sucesso:**
```json
{
  "message": "Conta eliminada com sucesso!"
}
```

**Nota:** Administradores não podem eliminar suas próprias contas por motivos de segurança.

## Gestão de Reservas

### Listar Todas as Reservas do Utilizador

```
GET /api/reservas
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

### Criar Nova Reserva

```
POST /api/reservas
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

**Corpo da requisição:**
```json
{
  "viagem_id": 1,
  "lugares": 2
}
```

### Ver Detalhes de uma Reserva

```
GET /api/reservas/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

### Cancelar Reserva

```
DELETE /api/reservas/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

## Viagens Disponíveis

### Listar Todas as Viagens

```
GET /api/viagens
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

### Ver Detalhes de uma Viagem

```
GET /api/viagens/{id}
```

**Cabeçalho:**
```
Authorization: Bearer seu_token_jwt
```

## Tratamento de Erros

A API retorna códigos de status HTTP apropriados junto com mensagens de erro descritivas:

- **400** - Requisição inválida
- **401** - Não autenticado
- **403** - Acesso negado
- **404** - Recurso não encontrado
- **422** - Erro de validação
- **500** - Erro interno do servidor

## Notas Importantes

1. O token JWT expira após 60 minutos. Após esse período, é necessário fazer login novamente.
2. Todas as datas devem ser enviadas no formato YYYY-MM-DD.
3. Certifique-se de incluir o token JWT em todas as requisições autenticadas.