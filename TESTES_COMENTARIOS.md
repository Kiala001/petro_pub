# ✅ Checklist de Testes - Sistema de Comentários

## 🧪 Testes Funcionais

### 1. Criar Comentários

#### Teste 1.1: Criar comentário autenticado
- [ ] Login como usuário
- [ ] Acessar página de detalhes de um documento
- [ ] Clicar "Adicionar Comentário"
- [ ] Escrever texto > 3 caracteres
- [ ] Clicar "Postar Comentário"
- **Esperado**: Comentário aparece na lista, notificação de sucesso

#### Teste 1.2: Tentar comentar sem autenticação
- [ ] Não estar logado
- [ ] Acessar página de detalhes
- [ ] Tentar clicar "Adicionar Comentário"
- **Esperado**: Redireciona para login.html

#### Teste 1.3: Validação de tamanho
- [ ] Tentar enviar com 1-2 caracteres
- **Esperado**: Erro "Comentário deve ter no mínimo 3 caracteres"
- [ ] Tentar enviar com > 5000 caracteres
- **Esperado**: Erro "Comentário não pode exceder 5000 caracteres"

### 2. Responder Comentários

#### Teste 2.1: Responder a comentário
- [ ] Clicar "Responder" em um comentário
- [ ] Escrever resposta
- [ ] Clicar "Responder"
- **Esperado**: Resposta aparece aninhada, indentada

#### Teste 2.2: Cancelar resposta
- [ ] Clicar "Responder"
- [ ] Clicar "Cancelar"
- **Esperado**: Formulário de resposta desaparece

### 3. Editar Comentários

#### Teste 3.1: Editar próprio comentário
- [ ] Postar um comentário
- [ ] Clicar ícone "✏️ Editar"
- [ ] Modificar texto
- [ ] Clicar "Salvar"
- **Esperado**: Comentário atualizado, marca "(editado)" aparece

#### Teste 3.2: Não poder editar alheio
- [ ] Como Usuário A, tentar editar comentário de Usuário B
- **Esperado**: Botão de editar não aparece para comentários alheios

### 4. Deletar Comentários

#### Teste 4.1: Deletar próprio comentário
- [ ] Clicar ícone "🗑️ Deletar"
- [ ] Confirmar deleção
- **Esperado**: Comentário desaparece, notificação de sucesso

#### Teste 4.2: Deletar com respostas
- [ ] Deletar comentário que tem respostas
- **Esperado**: Comentário e respostas são deletados (cascata)

#### Teste 4.3: Cancelar deleção
- [ ] Clicar "🗑️ Deletar"
- [ ] Clicar "Cancelar" no diálogo
- **Esperado**: Comentário permanece

### 5. Marcar como Útil/Não Útil

#### Teste 5.1: Marcar como útil
- [ ] Clicar "👍 Útil (X)"
- **Esperado**: 
  - Contador incrementa
  - Botão fica destacado (amarelo/gold)
  - Percentual de utilidade é calculado

#### Teste 5.2: Trocar voto
- [ ] Marcar como "Útil"
- [ ] Marcar como "Não útil"
- **Esperado**: Muda de útil para não útil

#### Teste 5.3: Remover voto
- [ ] Marcar como "Útil"
- [ ] Clicar novamente em "Útil"
- **Esperado**: Voto é removido, botão volta ao estado normal

#### Teste 5.4: Calcular percentual de utilidade
- [ ] Ter comentário com 4 votos úteis e 1 não útil
- **Esperado**: Mostra "Utilidade: 80%"

### 6. Interface Visual

#### Teste 6.1: Renderização em desktop
- [ ] Abrir em navegador desktop (1920x1080)
- **Esperado**:
  - Comentários em layout normal
  - Respostas indentadas
  - Avatares visíveis
  - Botões bem espaçados

#### Teste 6.2: Responsividade mobile
- [ ] Abrir em dispositivo mobile (320px)
- **Esperado**:
  - Layout se adapta
  - Botões em coluna
  - Texto legível
  - Sem scroll horizontal

#### Teste 6.3: Dark mode
- [ ] Ativar dark mode no navegador
- **Esperado**: Cores se adaptam, texto legível

### 7. Carregamento de Dados

#### Teste 7.1: Carregar comentários de documento
- [ ] Acessar página com documento que tem comentários
- **Esperado**: Comentários carregam dentro de 2 segundos

#### Teste 7.2: Paginação
- [ ] Ter > 50 comentários
- **Esperado**: Mostra primeiros 50, botão "Carregar Mais" funciona

#### Teste 7.3: Sem comentários
- [ ] Acessar documento novo
- **Esperado**: Mensagem "Sem comentários ainda. Seja o primeiro a comentar!"

### 8. Notificações

#### Teste 8.1: Notificação de sucesso
- [ ] Postar comentário
- **Esperado**: Toast verde com "Comentário postado com sucesso! ✓"

#### Teste 8.2: Notificação de erro
- [ ] Tentar postar comentário sem conexão
- **Esperado**: Toast vermelho com mensagem de erro

#### Teste 8.3: Desaparecimento de notificação
- [ ] Notificação aparece
- [ ] Aguardar 3 segundos
- **Esperado**: Notificação desaparece suavemente

## 🔒 Testes de Segurança

### Teste de Autorização
- [ ] User A não consegue editar comentário de User B
- [ ] User A não consegue deletar comentário de User B
- [ ] Admin consegue moderar comentários (se implementado)

### Teste de SQL Injection
- [ ] Tentar comentário: `' OR '1'='1`
- **Esperado**: Texto é escapado, nenhuma injeção

### Teste de XSS
- [ ] Tentar comentário: `<script>alert('xss')</script>`
- **Esperado**: Tags HTML são escapadas, script não executa

### Teste de Token Expirado
- [ ] Login e copiar token JWT
- [ ] Esperar 24h (ou forçar expiração em dev)
- [ ] Tentar postar comentário
- **Esperado**: 401 Unauthorized, redirecionamento para login

## 📊 Testes de Performance

### Teste de Carga
```bash
# Criar 100 comentários
for i in {1..100}; do
  curl -X POST http://localhost/petrochamp-sistema/backend/api/comments \
    -H "Authorization: Bearer TOKEN" \
    -d "{\"document_id\":\"doc-id\",\"content\":\"Comentário $i\"}"
done
```
- **Esperado**: Todas as requisições respondem em < 500ms

### Teste de Memória
- [ ] Carregar comentários com 1000+ itens
- **Esperado**: Sem travamento do navegador

## 📱 Testes de Navegadores

- [ ] Chrome 90+
- [ ] Firefox 88+
- [ ] Safari 14+
- [ ] Edge 90+
- [ ] Chrome Mobile
- [ ] Safari iOS
- [ ] Firefox Android

## 🐛 Testes de Edge Cases

### Teste de Comentário Muito Longo
- [ ] Postar comentário com 5000 caracteres (máximo)
- **Esperado**: Funciona normalmente

### Teste de Caracteres Especiais
- [ ] Comentário com: émojis 🎉, acentos àáâã, símbolos @#$%
- **Esperado**: Todos são preservados e exibidos corretamente

### Teste de Links em Comentários
- [ ] Comentário com URL: https://example.com
- **Esperado**: URL é escapada (não clicável por segurança)

### Teste de Múltiplas Respostas
- [ ] Adicionar 10+ respostas a um comentário
- **Esperado**: Todas são exibidas, limite de 10 no carregamento respeitado

### Teste de Comentários Deletados
- [ ] Deletar comentário que tem respostas
- **Esperado**: Respostas também são deletadas (ON DELETE CASCADE)

### Teste de Atualização Rápida
- [ ] Editar comentário 5 vezes em 10 segundos
- **Esperado**: Todas as versões são salvas corretamente

## 🌐 Testes de API

### GET /comments
```bash
curl "http://localhost/petrochamp-sistema/backend/api/comments?document_id=doc-001"
```
- [ ] Retorna 200 OK
- [ ] Retorna array de comentários
- [ ] Inclui metadados de paginação

### POST /comments
```bash
curl -X POST "http://localhost/petrochamp-sistema/backend/api/comments" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"document_id":"doc-001","content":"Teste"}'
```
- [ ] Retorna 201 Created
- [ ] Inclui ID do novo comentário

### PUT /comments/{id}
```bash
curl -X PUT "http://localhost/petrochamp-sistema/backend/api/comments/comment-001" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Conteúdo atualizado"}'
```
- [ ] Retorna 200 OK
- [ ] Atualiza conteúdo

### DELETE /comments/{id}
```bash
curl -X DELETE "http://localhost/petrochamp-sistema/backend/api/comments/comment-001" \
  -H "Authorization: Bearer TOKEN"
```
- [ ] Retorna 200 OK
- [ ] Comentário é deletado

### POST /comments/{id}/helpful
```bash
curl -X POST "http://localhost/petrochamp-sistema/backend/api/comments/comment-001/helpful" \
  -H "Authorization: Bearer TOKEN"
```
- [ ] Primeira vez: adiciona voto, retorna 200
- [ ] Segunda vez: remove voto, retorna 200 com "removed"

## 📝 Testes de Banco de Dados

### Verificar Tabelas
```sql
SHOW TABLES LIKE 'comment%';
```
- [ ] Tabela `comments` existe
- [ ] Tabela `comment_reactions` existe

### Verificar Estrutura
```sql
DESCRIBE comments;
```
- [ ] Todas as colunas presentes
- [ ] Tipos de dados corretos
- [ ] Constraints de FK funcionam

### Verificar Índices
```sql
SHOW INDEX FROM comments;
```
- [ ] Índices em document_id, user_id, parent_comment_id, created_at
- [ ] Índice FULLTEXT em content (opcional)

### Testar Cascata de Deleção
- [ ] Deletar documento
- [ ] Verificar se comentários foram deletados
- **Esperado**: ON DELETE CASCADE funciona

## 🎯 Testes de Integração

### Fluxo Completo
1. [ ] Login como User A
2. [ ] Acessar documento
3. [ ] Postar comentário
4. [ ] Marcar como útil
5. [ ] Editar comentário
6. [ ] Logout
7. [ ] Login como User B
8. [ ] Ver comentário de User A
9. [ ] Marcar como útil
10. [ ] Responder comentário
11. [ ] Verificar que User A vê resposta

### Teste Multi-usuário
- [ ] 2 usuários abrem mesmo documento
- [ ] User A comenta
- [ ] User B vê comentário em tempo real (ou após reload)
- **Esperado**: Síncronia funciona

## 📋 Resumo de Testes

| Categoria | Total | Testados | % |
|-----------|-------|----------|---|
| Funcionais | 20 | [ ] | 0% |
| Segurança | 5 | [ ] | 0% |
| Performance | 2 | [ ] | 0% |
| Navegadores | 7 | [ ] | 0% |
| Edge Cases | 6 | [ ] | 0% |
| API | 5 | [ ] | 0% |
| Banco Dados | 4 | [ ] | 0% |
| Integração | 3 | [ ] | 0% |
| **TOTAL** | **52** | [ ] | **0%** |

---

## 🚀 Status de Release

- [ ] Todos os testes funcionais passaram
- [ ] Testes de segurança realizados
- [ ] Performance validada
- [ ] Compatibilidade de navegadores confirmada
- [ ] Documentação completa
- [ ] Code review realizado
- [ ] Pronto para produção

**Data de Teste**: _______________  
**Testador**: _______________  
**Status Final**: _______________
