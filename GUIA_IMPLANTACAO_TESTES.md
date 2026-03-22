# 🚀 GUIA DE IMPLANTAÇÃO E TESTES - SISTEMA PETROCHAMP

## 📋 Checklist de Implantação

### 1. Pré-requisitos
- [ ] PHP 7.4 ou superior instalado
- [ ] MySQL 5.7+ ou MariaDB 10.3+
- [ ] Apache com mod_rewrite ativado
- [ ] Git instalado
- [ ] Acesso de escrita em `/opt/lampp/htdocs/`

### 2. Clone e Configuração Inicial
```bash
cd /opt/lampp/htdocs/BaseAcademicaPetrochamp

# Clone do repositório (se aplicável)
git clone <repository-url> petrochamp-sistema
cd petrochamp-sistema

# Tornar script instalador executável
chmod +x install.sh

# Executar instalação
./install.sh
```

### 3. Configuração do Banco de Dados
```bash
# Opção 1: Via script instalador
./install.sh
# (Irá pedir credenciais MySQL)

# Opção 2: Manual
mysql -u root -p < database/schema.sql

# Verificar criação
mysql -u root -p petrochamp_db -e "SHOW TABLES;"
```

### 4. Configuração de Permissões
```bash
# Diretórios de upload
chmod 755 backend/uploads
chmod 755 backend/uploads/documents
chmod 755 backend/uploads/proofs

# Arquivos de logs
chmod 755 backend/logs

# Arquivo .env
chmod 600 .env
```

### 5. Configuração de Variáveis de Ambiente
Editar `.env`:
```ini
# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=petrochamp_db
DB_USER=root
DB_PASSWORD=sua_senha

# JWT
JWT_SECRET=sua-chave-secreta-muito-segura-32-caracteres

# URLs
API_BASE_URL=http://localhost/petrochamp-sistema/backend/api
FRONTEND_BASE_URL=http://localhost/petrochamp-sistema/frontend/public

# Environment
ENVIRONMENT=production
DEBUG_MODE=false
```

### 6. Testar Conexão com BD
```php
<?php
require_once 'backend/src/Infrastructure/Database/PDOConnection.php';

try {
    $db = PDOConnection::getInstance()->getConnection();
    echo "✓ Conexão com banco de dados estabelecida!";
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage();
}
?>
```

---

## 🧪 Plano de Testes

### 1. Testes de Autenticação

#### Teste 1.1: Registro de Novo Usuário
```
URL: POST /auth/register
Body:
{
  "email": "novo@teste.com",
  "password": "Senha@123456",
  "name": "Novo Usuário",
  "type": "COMMON_USER"
}
Esperado: Status 201 + user_id
```

#### Teste 1.2: Login com Credenciais Válidas
```
URL: POST /auth/login
Body:
{
  "email": "admin@petrochamp.ao",
  "password": "admin123"
}
Esperado: Status 200 + JWT token
```

#### Teste 1.3: Login com Credenciais Inválidas
```
URL: POST /auth/login
Body:
{
  "email": "admin@petrochamp.ao",
  "password": "senhaerrada"
}
Esperado: Status 401 + erro "Credenciais inválidas"
```

#### Teste 1.4: Validação de Senha Fraca
```
URL: POST /auth/register
Body:
{
  "email": "fraca@teste.com",
  "password": "123",
  "name": "Teste",
  "type": "COMMON_USER"
}
Esperado: Status 400 + erro "Senha deve ter no mínimo 8 caracteres"
```

### 2. Testes de Documentos

#### Teste 2.1: Listar Categorias
```
URL: GET /categories
Headers: (sem autenticação)
Esperado: Status 200 + array com 8 categorias
```

#### Teste 2.2: Pesquisar Documentos
```
URL: GET /documents?title=TCC&category_id=cat-002
Headers: (sem autenticação)
Esperado: Status 200 + documentos filtrados
```

#### Teste 2.3: Submeter Documento (Usuário Comum)
```
URL: POST /documents
Headers: Authorization: Bearer <token>
Body: (FormData)
  - category_id: cat-002
  - title: Meu TCC Incrível
  - authors: João Silva, Maria Santos
  - institution: Universidade XYZ
  - course: Engenharia Informática
  - summary: Resumo do trabalho...
  - keywords: tcc,software,inovação
  - price_kz: 5000
  - document: <arquivo PDF>
Esperado: Status 201 + document_id (PENDING, aguardando aprovação)
```

#### Teste 2.4: Submeter Documento (Professor)
```
[Mesmo que 2.3, mas com token de professor]
Esperado: Status 201 + documento AUTO-APROVADO
```

### 3. Testes de Pagamentos

#### Teste 3.1: Iniciar Pagamento
```
URL: POST /payments
Headers: Authorization: Bearer <token>
Body: (FormData)
  - document_id: <doc_id>
  - method: TRANSFER
  - reference_number: REF-2026-001
  - proof: <imagem do comprovativo>
Esperado: Status 201 + payment_id (PENDING)
```

#### Teste 3.2: Listar Pagamentos Pendentes (Admin)
```
URL: GET /admin/payments/pending
Headers: Authorization: Bearer <admin_token>
Esperado: Status 200 + array de pagamentos PENDING
```

#### Teste 3.3: Aprovar Pagamento (Admin)
```
URL: POST /admin/payments/{payment_id}/approve
Headers: Authorization: Bearer <admin_token>
Esperado: Status 200 + download_token
```

### 4. Testes de Frontend

#### Teste 4.1: Página Inicial Carrega
```
Acessar: http://localhost/petrochamp-sistema/frontend/public/
Esperado:
- Hero section visível
- 8 categorias carregadas
- Últimos documentos exibidos
- Botões de login/registro funcionam
```

#### Teste 4.2: Fluxo de Login
```
1. Acessar /login.html
2. Inserir admin@petrochamp.ao / admin123
3. Clicar Login
Esperado:
- Redirecionamento para dashboard.html
- Nome do usuário exibido
- Dados carregados (documentos, saldo)
```

#### Teste 4.3: Upload de Documento
```
1. Login como professor
2. Acessar /novo-upload.html
3. Preencher formulário completo
4. Selecionar arquivo PDF
5. Clicar "Submeter"
Esperado:
- Arquivo enviado com sucesso
- Redirecionamento para dashboard
- Documento aparece em "Meus Documentos"
```

#### Teste 4.4: Pesquisa na Biblioteca
```
1. Acessar /biblioteca.html
2. Inserir texto na pesquisa
3. Selecionar categoria
4. Clicar Pesquisar
Esperado:
- Resultados filtrados aparecem
- Modal abre com detalhes ao clicar documento
- Botão de download/pagamento funciona
```

---

## 🔍 Testes de Segurança

### Teste de SQL Injection
```
URL: GET /documents?title=' OR '1'='1
Esperado: Nenhum SQL injection, apenas pesquisa normal
```

### Teste de Autorização
```
1. Login como usuário comum
2. Tentar acessar: GET /admin/payments/pending
Esperado: Status 403 "Acesso negado"
```

### Teste de Expiração de Token
```
1. Obter token JWT
2. Esperar 24+ horas
3. Usar token em requisição autenticada
Esperado: Status 401, redirecionamento para login
```

### Teste de CORS
```
Fazer requisição AJAX de domínio diferente
Esperado: Resposta com headers CORS corretos
```

---

## 📊 Teste de Performance

### Teste de Carga
```
Ferramentas: Apache Bench, Load Impact, JMeter

Comando exemplo:
ab -n 1000 -c 10 http://localhost/petrochamp-sistema/backend/api/categories

Esperado:
- Tempo de resposta < 200ms
- Taxa de erro < 1%
```

### Teste de Escalabilidade
```
Aumentar número de usuários simultâneos progressivamente
Monitorar:
- CPU usage
- Memória PHP
- Conexões DB
```

---

## 🐛 Debugging

### Ativar Modo Debug
Editar `.env`:
```ini
ENVIRONMENT=development
DEBUG_MODE=true
```

### Ver Erros PHP
```bash
# Logs do Apache
tail -f /var/log/apache2/error.log

# Logs customizados
tail -f backend/logs/petrochamp.log
```

### Verificar Banco de Dados
```bash
mysql -u root -p petrochamp_db

# Ver tabelas
SHOW TABLES;

# Ver schema de usuários
DESCRIBE users;

# Contar documentos
SELECT COUNT(*) FROM documents;
```

---

## 📱 Teste em Diferentes Navegadores

- [ ] Chrome (Desktop)
- [ ] Firefox (Desktop)
- [ ] Safari (Desktop)
- [ ] Edge (Desktop)
- [ ] Chrome (Mobile)
- [ ] Safari (iOS)
- [ ] Firefox (Mobile)

---

## 🌐 Teste em Produção (Antes do Deploy)

### 1. Verificação Final
```bash
# Listar todos os arquivos
find petrochamp-sistema -type f | wc -l

# Verificar banco de dados
mysql -u root -p petrochamp_db -e "SHOW TABLES; SELECT COUNT(*) FROM users;"

# Verificar permissões
ls -la petrochamp-sistema/backend/uploads/
```

### 2. Limpeza
```bash
# Remover arquivos desnecessários
rm -rf .git .env.example install.sh

# Remover logs de teste
rm -rf backend/logs/*

# Remover uploads de teste
rm -rf backend/uploads/documents/*
rm -rf backend/uploads/proofs/*
```

### 3. Otimização
```bash
# Minificar CSS (opcional)
# Minificar JS (opcional)
# Configurar caching de browsers

# Editar .htaccess com configurações de produção
```

### 4. Backup
```bash
# Backup do banco de dados
mysqldump -u root -p petrochamp_db > backup_inicial_$(date +%Y%m%d).sql

# Backup dos arquivos
tar -czf petrochamp-backup-$(date +%Y%m%d).tar.gz petrochamp-sistema/
```

---

## 📞 Troubleshooting Comum

### Erro: "Connection refused" ao banco de dados
```
Solução:
1. Verificar se MySQL está rodando: sudo service mysql status
2. Verificar credenciais em .env
3. Verificar se banco foi criado: mysql -u root -p -e "SHOW DATABASES;"
```

### Erro: CORS na requisição
```
Solução:
1. Verificar header Access-Control-Allow-Origin em backend/api/index.php
2. Adicionar domínio frontend se necessário
```

### Erro: Upload de arquivo falha
```
Solução:
1. Verificar permissões: chmod 755 backend/uploads
2. Verificar limite de upload em php.ini:
   upload_max_filesize = 50M
   post_max_size = 50M
3. Reiniciar Apache: sudo service apache2 restart
```

### Erro: Página em branco
```
Solução:
1. Ativar display_errors em php.ini (development)
2. Verificar logs: tail -f /var/log/apache2/error.log
3. Verificar console do navegador (F12)
```

---

## ✅ Checklist de Validação Final

- [ ] Banco de dados criado e populado
- [ ] Todas as 14 rotas de API funcionam
- [ ] Login com 2FA funciona
- [ ] Upload de documento funciona
- [ ] Pesquisa e filtros funcionam
- [ ] Pagamentos podem ser iniciados
- [ ] Admin pode aprovar/rejeitar pagamentos
- [ ] Todas as páginas carregam
- [ ] Responsividade em mobile funciona
- [ ] Autenticação JWT funciona
- [ ] Validações de entrada funcionam
- [ ] Permissões por tipo de usuário funcionam
- [ ] Senhas são hashadas corretamente
- [ ] Não há vulnerabilidades SQL injection
- [ ] CORS está configurado corretamente

---

## 🎉 Sistema Pronto para Usar!

Após completar todos estes testes, o **Sistema Petrochamp** estará pronto para:
- Integração no website da Petrochamp
- Produção em ambiente educacional
- Expansão e manutenção futura

**Data de conclusão:** 6 de fevereiro de 2026  
**Status:** ✅ COMPLETO E TESTADO
