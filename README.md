# 🏛️ Sistema Petrochamp - Base de Dados Académica Integrada

## 📋 Visão Geral

O **Sistema Petrochamp** é uma plataforma web académica integrada, completa e moderna para armazenamento, comercialização, gestão e disseminação de conteúdos académicos e científicos.

**Desenvolvido para:** WEBTEC SOLUTION – A Tecnologia Criando Soluções  
**Local:** Luanda – 2026

---

## 🎯 Objetivos do Sistema

- ✅ Criar um repositório académico inteligente e monetizado
- ✅ Garantir segurança e controlo financeiro
- ✅ Facilitar a partilha e valorização do conhecimento
- ✅ Incentivar produção científica através de gamificação
- ✅ Integrar universidades, professores e estudantes numa única plataforma

---

## 🏗️ Arquitetura

O projeto segue **Domain-Driven Design (DDD)** e **Clean Code**, dividindo-se em camadas:

### Backend (PHP)
```
backend/
├── src/
│   ├── Domain/           # Entidades e Value Objects
│   │   ├── User/         # Usuário, Email, Password, UserType
│   │   ├── Document/     # Documento (artigos, TCCs, etc)
│   │   ├── Category/     # Categorias académicas
│   │   └── Payment/      # Pagamentos
│   ├── Application/      # Serviços de orquestração
│   │   ├── AuthenticationService
│   │   ├── DocumentService
│   │   └── PaymentService
│   └── Infrastructure/   # Implementação técnica
│       ├── Database/     # Repositórios e conexão
│       └── Security/     # JWT e autenticação
└── api/                  # Endpoints REST
    └── routes/           # Rotas de cada módulo
```

### Frontend (HTML/CSS/JavaScript)
```
frontend/
├── public/
│   ├── index.html                 # Página inicial
│   ├── login.html                 # Login com 2FA
│   ├── register.html              # Registro de usuários
│   ├── dashboard.html             # Dashboard do usuário
│   ├── biblioteca.html            # Repositório de documentos
│   ├── novo-upload.html           # Submissão de documentos
│   └── assets/
│       ├── js/
│       │   ├── api.js             # Funções de requisição à API
│       │   ├── auth.js            # Autenticação
│       │   ├── main.js            # Página inicial
│       │   ├── dashboard.js       # Dashboard
│       │   ├── biblioteca.js      # Biblioteca
│       │   └── upload.js          # Upload de documentos
│       └── css/
│           └── style.css          # Estilos customizados
```

---

## 🗄️ Banco de Dados

**Sistema:** MySQL 5.7+  
**Driver:** PDO

### Tabelas Principais

1. **users** - Usuários do sistema (Admin, Professor, Usuário Comum)
2. **categories** - Categorias académicas (TCC, Artigos, Dissertações, etc)
3. **documents** - Documentos carregados (com versionamento)
4. **payments** - Pagamentos e transações
5. **download_history** - Histórico de downloads
6. **ratings** - Avaliações de documentos
7. **subscriptions** - Planos de assinatura
8. **favorites** - Documentos favoritos dos usuários

---

## 🚀 Instalação e Setup

### Pré-requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Git

### Passos de Instalação

#### 1. Clonar o repositório
```bash
cd /opt/lampp/htdocs/BaseAcademicaPetrochamp
git clone <url-repositorio> petrochamp-sistema
cd petrochamp-sistema
```

#### 2. Criar banco de dados
```bash
mysql -u root -p < database/schema.sql
```

#### 3. Configurar variáveis de ambiente
Editar `/backend/src/Infrastructure/Database/PDOConnection.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'petrochamp_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
```

#### 4. Criar diretórios de upload
```bash
mkdir -p backend/uploads/documents
mkdir -p backend/uploads/proofs
chmod 755 backend/uploads/*
```

#### 5. Acessar a aplicação
- **Frontend:** `http://localhost/petrochamp-sistema/frontend/public/index.html`
- **API:** `http://localhost/petrochamp-sistema/backend/api/`

---

## 👥 Tipos de Usuários e Permissões

| Tipo | Permissões |
|------|-----------|
| **Admin** | Acesso total, gestão de tudo, sem pagamento |
| **Professor/Coordenador** | Upload sem pagamento, documentos auto-aprovados |
| **Usuário Comum** | Upload com pagamento, downloads após compra |

---

## 📚 Categorias de Documentos

1. **📄 Artigo Científico** - Requer revisão
2. **🎓 Trabalho de Conclusão de Curso (TCC)** - Requer revisão
3. **🧪 Relatório Técnico** - Requer revisão
4. **📘 Monografia** - Requer revisão
5. **📕 Dissertação** - Requer revisão
6. **📊 Apresentação Acadêmica** - Sem revisão
7. **📁 Dataset Científico** - Requer revisão
8. **📚 Outros Documentos** - Sem revisão

---

## 🔐 Segurança

### Autenticação
- ✅ Hash de senhas com BCRYPT (cost: 12)
- ✅ JWT para autenticação de requisições
- ✅ 2FA opcional com códigos de 6 dígitos
- ✅ Validação de email único

### Autorização
- ✅ Controle de acesso por perfil
- ✅ Proteção de endpoints sensíveis
- ✅ CORS configurado
- ✅ Validação de entrada em todos os endpoints

### Proteção de Dados
- ✅ PDO com prepared statements
- ✅ Senhas nunca armazenadas em texto plano
- ✅ Links de download temporários com expiração
- ✅ Marca d'água dinâmica em PDFs (futuro)

---

## 🔄 Fluxos Principais

### 1. Registro e Login
```
Usuário → Register → Validações → BD → Login → JWT → Dashboard
```

### 2. Upload de Documento
```
Usuário → Seleciona Categoria → Preenche Formulário → Upload Arquivo
→ Se Prof/Admin: Auto-aprovado → Publicado
→ Se Comum: Aguarda revisão → Admin aprova/rejeita
```

### 3. Compra de Documento
```
Usuário → Pesquisa → Seleciona Doc Pago → Pagamento (Prova)
→ Admin verifica → Aprova/Rejeita → Token temporário → Download
```

### 4. Download
```
Usuário → Visualiza Doc → Download Direto (Gratuito) OU Pagamento (Pago)
→ Histórico atualizado → Receita dividida (70% autor, 30% plataforma)
```

---

## 💻 Endpoints da API

### Autenticação
- `POST /auth/register` - Registrar novo usuário
- `POST /auth/login` - Login
- `POST /auth/verify-2fa` - Verificar código 2FA

### Categorias
- `GET /categories` - Listar todas as categorias
- `GET /categories/{id}` - Detalhes de uma categoria

### Documentos
- `GET /documents` - Listar documentos (com filtros)
- `GET /documents/{id}` - Detalhes de um documento
- `POST /documents` - Submeter novo documento
- `GET /documents/user/{userId}` - Documentos do usuário

### Pagamentos
- `POST /payments` - Iniciar pagamento
- `GET /payments/history` - Histórico de pagamentos

### Admin
- `GET /admin/payments/pending` - Pagamentos pendentes
- `POST /admin/payments/{id}/approve` - Aprovar pagamento
- `POST /admin/payments/{id}/reject` - Rejeitar pagamento

---

## 🎨 Identidade Visual

### Cores
- **Primária:** Vermelho Escuro (`#5a0101`, `#340100`)
- **Secundária:** Dourado/Âmbar (`#f59e0b`)
- **Fundo:** Cinza claro (`#f3f4f6`)
- **Texto:** Cinza escuro (`#1f2937`)

### Tipografia
- **Font:** Poppins, Inter (Tailwind CDN)
- **Espaçamento:** Sistema de 4px

---

## 📱 Responsividade

A aplicação é **100% responsiva**:
- ✅ Desktop (1920px+)
- ✅ Tablet (768px - 1024px)
- ✅ Mobile (320px - 767px)

---

## 🚀 Funcionalidades Implementadas

### MVP (Versão 1.0)
- ✅ Autenticação com 2FA
- ✅ Upload de documentos com categorização
- ✅ Pesquisa e filtros avançados
- ✅ Sistema de pagamentos
- ✅ Histórico de transações
- ✅ Dashboard do usuário
- ✅ Painel administrativo básico

### Roadmap (Futuro)
- 🔄 Detecção de plágio (Turnitin API)
- 🔄 DOI automático
- 🔄 Integração ORCID
- 🔄 Citação automática (APA, MLA, ABNT)
- 🔄 Marca d'água em PDFs
- 🔄 Sistema de gamificação (pontos, badges)
- 🔄 Recomendações inteligentes
- 🔄 Versão mobile (React Native)
- 🔄 API pública para parceiros

---

## 📊 Estatísticas e Relatórios

- 📈 Downloads por documento
- 💰 Receita gerada por categoria
- 👥 Usuários ativos
- 📄 Documentos em revisão
- 💳 Pagamentos processados

---

## 🔍 Testes

### Usuários de Teste

**Admin:**
- Email: `admin@petrochamp.ao`
- Senha: `admin123`
- Tipo: `ADMIN`

**Professor:**
- Email: `professor@petrochamp.ao`
- Senha: `professor123`
- Tipo: `PROFESSOR`

**Usuário Comum:**
- Email: `usuario@petrochamp.ao`
- Senha: `usuario123`
- Tipo: `COMMON_USER`

---

## 📝 Convenções de Código

### PHP (Backend)
```php
// PascalCase para classes
class UserRepository { }

// camelCase para métodos
public function getUserById() { }

// SNAKE_CASE para constantes
const STATUS_ACTIVE = 'ACTIVE';

// Sempre usar type hints
public function save(User $user): void { }
```

### JavaScript (Frontend)
```javascript
// camelCase para variáveis e funções
const userData = getUserData();
function loadDocuments() { }

// UPPER_CASE para constantes
const API_BASE_URL = 'http://localhost/api';
```

---

## 🐛 Troubleshooting

### Erro: CORS origin not allowed
**Solução:** Editar `backend/api/index.php` e adicionar origem:
```php
header('Access-Control-Allow-Origin: http://seu-dominio.com');
```

### Erro: Database connection failed
**Solução:** Verificar credenciais em `PDOConnection.php`

### Erro: File upload failed
**Solução:** Verificar permissões da pasta `backend/uploads/`

---

## 📞 Suporte

Para dúvidas ou problemas, contacte:
- **Email:** support@petrochamp.ao
- **Telefone:** +244 xxx xxx xxx

---

## 📄 Licença

© 2026 WEBTEC SOLUTION. Todos os direitos reservados.

---

## 👨‍💻 Desenvolvido por

**WEBTEC SOLUTION** – A Tecnologia Criando Soluções  
Luanda, Angola – 2026

---

**Última atualização:** 6 de fevereiro de 2026
