#!/bin/bash

# Petrochamp Setup Script
# Este script configura o ambiente inicial

echo "🏛️  Instalando Sistema Petrochamp..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Criar diretórios de upload
echo -e "${YELLOW}Criando diretórios...${NC}"
mkdir -p backend/uploads/documents
mkdir -p backend/uploads/proofs
mkdir -p backend/logs
chmod 755 backend/uploads
chmod 755 backend/uploads/documents
chmod 755 backend/uploads/proofs

# Copiar arquivo de configuração
echo -e "${YELLOW}Copiando configurações...${NC}"
cp .env.example .env

# Criar banco de dados
echo -e "${YELLOW}Criando banco de dados...${NC}"
echo "Insira as credenciais do MySQL:"
read -p "Usuário MySQL (padrão: root): " MYSQL_USER
read -p "Senha MySQL: " MYSQL_PASSWORD

MYSQL_USER=${MYSQL_USER:-root}

if [ -z "$MYSQL_PASSWORD" ]; then
    mysql -u "$MYSQL_USER" < database/schema.sql
else
    mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" < database/schema.sql
fi

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Banco de dados criado com sucesso!${NC}"
else
    echo -e "${RED}✗ Erro ao criar banco de dados${NC}"
    exit 1
fi

# Configurar permissões
echo -e "${YELLOW}Configurando permissões...${NC}"
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

echo -e "${GREEN}✓ Instalação concluída!${NC}"
echo ""
echo "Próximos passos:"
echo "1. Edite o arquivo .env com as credenciais corretas"
echo "2. Acesse: http://localhost/petrochamp-sistema/frontend/public/"
echo "3. Use as credenciais de teste do README.md"
echo ""
echo "Documentação: Veja README.md para mais detalhes"
