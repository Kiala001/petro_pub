# ⚡ QUICK START - PETROCHAMP v2.0

## 🚀 Em 5 Minutos

### 1. Instalação
```bash
cd /opt/lampp/htdocs/BaseAcademicaPetrochamp/petrochamp-sistema
chmod +x install.sh
./install.sh
```

### 2. Configurar Banco de Dados
```bash
mysql -u root -p < database/schema.sql
```

### 3. Editar .env
```bash
nano .env
# Preencher credenciais do banco
```

### 4. Acessar
- **Frontend**: http://localhost/petrochamp-sistema/frontend/public/
- **Admin**: admin@petrochamp.ao / admin123

---

## ✨ Novidades v2.0

### Value Objects
- ✅ Email com validação de nomes
- ✅ UserId formato `Us-XXXX`

### Validações
- ✅ Apenas PDF para documentos
- ✅ JPG/PNG/PDF para comprovantes

### Biblioteca
- ✅ 5 filtros avançados
- ✅ Paginação 9 itens/página
- ✅ Pesquisa em tempo real

### Perfil (NOVO)
- ✅ Página completa em `/perfil.html`
- ✅ Abas: Informações, Segurança, Histórico, Preferências

### UI/UX
- ✅ Design profissional
- ✅ 100% responsivo
- ✅ Cores Petrochamp

---

## 📁 Arquivos Principais

| Arquivo | Descrição |
|---------|-----------|
| `README_v2.0.md` | Resumo completo v2.0 |
| `MUDANCAS_v2.0.md` | Detalhes de mudanças |
| `backend/src/Domain/User/Email.php` | ⭐ Validação de nomes |
| `backend/src/Domain/User/UserId.php` | ⭐ Formato Us-XXXX |
| `frontend/public/perfil.html` | ⭐ Página de perfil |
| `frontend/public/biblioteca.html` | ⭐ Filtros avançados |

---

## 🎯 Funcionalidades

### Filtros Biblioteca
1. **Pesquisa**: Título + Autores
2. **Categoria**: Dropdown
3. **Preço**: Máximo customizável
4. **Tamanho**: MB máximo
5. **Paginação**: 9 itens/página

### Página Perfil
- Editar informações pessoais
- Alterar senha (min 8 caracteres)
- Ativar 2FA
- Ver histórico
- Preferências de notificação

### Validações
```
Email:
- Primeiro nome: 2-50 chars, sem números
- Último nome: 2-50 chars, sem números
- Acentuação aceita ✓

Upload:
- Apenas PDF ✓
- Outras extensões ❌

Comprovantes:
- JPG, PNG, PDF ✓
- Doc, XLS ❌
```

---

## 🔐 Teste de Segurança

Tente criar usuário com:
```
Nome: João123
Resultado: ❌ Rejeitado (número não permitido)

Nome: José Maria de Sousa
Resultado: ✅ Aceito (caracteres válidos)
```

---

## 📊 Estrutura Banco

10 Tabelas:
- `users` (com 2FA)
- `documents` (apenas PDF)
- `payments` (comprovantes validados)
- `categories`
- `comments` (com respostas)
- `comment_reactions`
- `download_history`
- `ratings`
- `subscriptions`
- `favorites`

---

## 💡 Dicas

- ✅ Sempre fazer login com `/login.html`
- ✅ Upload apenas em `/novo-upload.html`
- ✅ Ver perfil em `/perfil.html`
- ✅ Pesquisar em `/biblioteca.html`
- ✅ Comentários em `/detalhes.html?id=xxx`

---

## 🆘 Troubleshooting

**Erro 404 na biblioteca?**
→ Verificar se arquivos JS estão em `assets/js/`

**Filtros não funcionam?**
→ Verificar console (F12) para erros

**Banco não conecta?**
→ Verificar credenciais em `.env`

**Avatar não aparece?**
→ Verificar permissões em `uploads/`

---

## 📞 Próximas Ações

1. ✅ Testar filtros
2. ✅ Editar perfil
3. ✅ Fazer upload PDF
4. ✅ Comentar documento
5. ✅ Alterar senha

---

**Tudo pronto! Boa sorte! 🚀**
