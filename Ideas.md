# Ideias

1. Expiração do link de download:
    - Quando o estudante ou docente faz uma compra, o link de download deve aparecer durante 1 mês, depois disso, o link deverá expirar.
2. Permitir o usuário editar somente a sua avaliação.

WEBTEC SOLUTION
A TECNOLOGIA CRIANDO SOLUÇÕES
Sistema Petrochamp: Base de dados
Acadêmica Integrada
LUANDA
20261. Visão Geral do Sistema
O sistema Petrochamp é uma plataforma online centralizada, acessível diretamente pelo
website da Petrochamp, para gestão de:
•
•
•
•
•
Artigos científicos
TCCs
Relatórios técnicos
Apresentações
Datasets e outros arquivos acadêmicos
O sistema será acessado através de um botão no website chamado “Portal Acadêmico”.
Principais funcionalidades:
•
•
•
•
•
Upload de arquivos com pagamento obrigatório.
Download de arquivos com pagamento prévio ou assinatura.
Verificação de comprovativos bancários via API confiável.
Painel administrativo completo para gestão de usuários, arquivos, pagamentos e
relatórios.
Funcionalidades inovadoras de gamificação, recomendações, alertas e
assinaturas premium.
2. Acesso ao Sistema
2.1 Botão de Entrada
•
•
•
Nome: Portal Acadêmico
Localização: menu principal do website e destaque na página inicial
Função: redirecionar o usuário para o módulo integrado com biblioteca, upload e
download.
2.2 Login e Cadastro
•
•
•
Usuários precisam estar logados para usar o sistema.
Tipos de usuários:
o Administrador: acesso completo, sem pagamento.
o Professor/Coordenador: pode enviar e aprovar arquivos sem pagamento.
o Usuário comum: upload e download somente após pagamento e
verificação.
Autenticação segura: senha + 2FA opcional.
3. Fluxo de Upload Pago
3.1 Submissão de Artigos
1. Usuário seleciona “Enviar Artigo” no Portal Acadêmico.
2. Preenche formulário de dados: título, autor, curso, tipo de arquivo, resumo e
palavras-chave.3. Sistema exibe valor em Kwanza (Kz) + IBAN para transferência.
4. Usuário envia comprovativo pelo sistema (imagem/PDF).
3.2 Verificação do Comprovativo
•
•
•
API externa verifica a autenticidade da transferência.
Se aprovado: usuário conclui upload e administrador recebe notificação.
Se inválido: upload bloqueado, usuário notificado para reenviar comprovativo.
3.3 Funcionalidades Avançadas de Upload
•
•
•
Revisão por pares: artigos passam por aprovação de professores/especialistas
antes da publicação.
Upload programado: o usuário define data futura para publicação.
Metadados configuráveis: título, autor, curso, resumo, tags e preço.
4. Fluxo de Download Pago
4.1 Acesso ao Arquivo
•
Usuário pesquisa arquivos por título, autor, curso, ano, tipo ou preço.
4.2 Pagamento e Verificação
•
•
•
Formas de pagamento: transferência (IBAN), cartão ou app digital.
Usuário envia comprovativo para validação via API.
Após aprovação: download liberado e histórico registrado.
4.3 Funcionalidades Avançadas de Download
•
•
•
Download parcelado: liberar arquivos grandes em partes ou com limite de
tempo.
Assinatura com tiers: diferentes níveis de acesso (básico, avançado, premium)
com limites diferenciados.
Links temporários: links de download expiram após certo período para
segurança.
5. Biblioteca / Repositório Acadêmico
•
•
•
•
•
•
•
Pesquisa avançada: por autor, curso, ano, tipo, preço, palavras-chave.
Filtros inteligentes: gratuitos, pagos, tipos de arquivo, popularidade,
recomendações.
Pré-visualização: primeira página do PDF ou slides antes de pagar.
Área do usuário: histórico de uploads/downloads, saldo, planos premium.
Notificações: alertas sobre novos uploads ou promoções.
Gamificação: pontos, badges e ranking de usuários mais ativos.
Comentários e discussões: mini fórum em cada artigo para perguntas e debates.6. Painel Administrativo Integrado
6.1 Menu Administrativo
•
•
•
•
•
•
Dashboard: estatísticas de uploads, downloads, receita e alertas de
comprovativos pendentes.
Gerenciar Arquivos: upload manual, aprovação de uploads pagos, edição de
metadados e preço.
Gerenciar Usuários: adicionar, editar, remover contas; definir permissões.
Pagamentos e Assinaturas: controle de downloads/uploads pagos, validação
manual de comprovativos, relatórios financeiros.
Relatórios e Analytics: downloads por arquivo, usuário, receita total, arquivos
mais populares, usuários mais ativos.
Configurações: métodos de pagamento, limites, notificações, preferências de
visualização.
7. Estrutura de Menu
7.1 Menu Principal (Desktop)
1.
2.
3.
4.
5.
6.
7.
8.
Home
Biblioteca / Repositório Acadêmico
Artigos recentes
Planos e Preços
Sobre nós
Contato / Suporte
Portal Acadêmico (destaque)
Login / Cadastro
7.2 Menu Hambúrguer (Mobile / Responsivo)
•
•
•
Ícone de três linhas no canto superior.
Painel lateral deslizante com todas as opções do menu principal.
Funcionalidades extras:
o Acesso rápido ao Portal Acadêmico
o Indicador de notificações
o Filtros rápidos de pesquisa
8. Funcionalidades Inovadoras
8.1 Avançadas de Upload e Download
•
•
•
•
Upload com revisão por pares.
Upload programado.
Download parcelado.
Assinaturas com tiers.8.2 Gamificação e Engajamento
•
•
•
•
Sistema de pontos e badges.
Ranking de usuários.
Feedback e avaliações.
Comentários e discussões em cada artigo.
8.3 Inteligência e Personalização
•
•
•
Recomendações inteligentes baseadas no histórico.
Alertas personalizados.
Dashboard do usuário com gráficos de atividades.
8.4 Monetização e Controle Financeiro
•
•
•
•
Promoções e descontos via códigos promocionais.
Relatórios detalhados de receita e atividades.
Pagamento recorrente em Kwanza.
Conversão automática de moeda para pagamentos internacionais (futuro).
8.5 Segurança e Confiança
•
•
•
Anti-fraude em comprovativos suspeitos.
Download seguro com links temporários.
Backup automático de arquivos.
8.6 Integração e Expansão
•
•
•
•
Integração com apps educacionais (Moodle, Google Classroom).
API para universidades ou empresas.
Compartilhamento em redes sociais (resumo ou link pago).
Versão mobile ou app dedicado.
8.7 Usabilidade
•
•
•
•
Filtros avançados: autor, área, ano, tipo, popularidade, preço.
Visualização rápida de PDF ou slides.
Histórico detalhado de downloads e uploads.
Pesquisa inteligente com sugestão automática de palavras-chave.
9. Fluxo Resumido do Sistema
1.
2.
3.
4.
Usuário clica em Portal Acadêmico.
Login / Cadastro seguro.
Escolhe Upload ou Download.
Upload: pagamento → envio comprovativo → verificação API → upload
permitido → aprovação opcional.
5. Download: pagamento → envio comprovativo → verificação API → download
liberado.6. Administradores monitoram, aprovam e geram relatórios pelo painel integrado.
10. Segurança e Controle
•
•
•
•
•
Criptografia de arquivos e dados pessoais.
Logs de atividades detalhados para auditoria.
Bloqueio de uploads/downloads até verificação do pagamento.
Notificações automáticas de status de upload/download.
Backup automático e proteção contra fraudes.
11. Benefícios do Sistema
•
•
•
•
•
•
Monetização segura e confiável.
Controle completo de uploads, downloads e pagamentos.
Engajamento elevado com gamificação, rankings e avaliações.
Repositório inteligente com recomendações e alertas.
Expansível para apps, integração de APIs e versão mobile.
Experiência unificada e segura diretamente no website.



Condição para pontos desbloquearem downloads pagos
Os pontos não devem desbloquear qualquer artigo automaticamente, senão o sistema
perde monetização.
Condição recomendada:
•
•
Cada artigo tem valor em Kz e valor equivalente em pontos.
Exemplo:
Preço do artigo
Pontos necessários
500 Kz120 pontos
800 Kz200 pontos
1.500 Kz350 pontos
Regras adicionais:
•
•
•
O estudante precisa ter mínimo de atividade no portal (ex: pelo menos 2 uploads
ou 5 downloads).
Pontos só podem desbloquear até 50% dos downloads mensais.
Artigos premium podem não aceitar pontos.
Isso mantém equilíbrio entre monetização e engajamento.
Condição para reduzir custo com pontos
Aqui a lógica deve ser conversão parcial, não total sempre.
Exemplo:
Pontos usados
50 pontos
100 pontos
200 pontos
300 pontos
Desconto
10%
20%
40%
60%
Regra importante:
•
•
Pontos não reduzem mais que 70% do valor do artigo.
O sistema exige mínimo de pagamento real para manter receita.
Exemplo real:
Artigo = 800 KzAluno usa 200 pontos
Novo preço = 480 Kz
Administradores compram artigos?
Não.
Administradores não entram na economia do sistema.
Eles têm:
•
•
•
•
acesso total
download ilimitado
sem pagamento
sem sistema de pontos
Porque o papel deles é:
•
•
•
gestão
moderação
aprovação
Se entrarem no sistema de pontos cria conflito de interesse.
Se um docente rejeitar o artigo
Aqui entra governança da plataforma.
Fluxo ideal:
Estudante envia artigo
Docente avalia
Pode:
•
•
•
Aprovar
Solicitar correção
Rejeitar
Administrador faz a validação final
Mas atenção:
•
•
Se docente aprovar, admin só confirma.
Se docente rejeitar, admin decide:
o confirmar rejeiçãoo
o
pedir revisão
encaminhar para outro avaliador
O administrador é a autoridade final.
Isso evita:
•
•
•
rejeições injustas
erros de avaliação
conflitos acadêmicos
Adicionar Índice de Reputação do Autor.
Cada estudante tem uma pontuação baseada em:
•
•
•
•
artigos aprovados
downloads recebidos
avaliações positivas
participação na comunidade
Autores com alta reputação:
•
•
•
pagam menos para publicar
têm prioridade de aprovação
aparecem mais nas pesquisas.
