# Guia de Configuração B2B - Grupo Awamotos

## ✅ Configurações Aplicadas via CLI

As seguintes configurações B2B foram ativadas automaticamente:

```bash
✅ Módulo B2B: Habilitado
✅ Ocultar Preços para Visitantes: Ativado
✅ Sistema de Cotação: Habilitado
✅ Botão de Cotação: Exibir na página do produto
✅ Aprovação de Clientes: Obrigatória
```

## 🎛️ Painel Admin - Configurações Disponíveis

Acesse: **Admin → Stores → Configuration → Grupo Awamotos → B2B Settings**

### 1. Configurações Gerais
- ✅ **Habilitar Módulo B2B**: Sim
- **Modo B2B**: Mixed (B2B + B2C) ou Strict (apenas B2B)

### 2. Visibilidade de Preços
- ✅ **Ocultar Preços para Visitantes**: Sim
- **Ocultar Botão Comprar**: Sim/Não
- **Mensagem Personalizada**: "Faça login para ver preços"
- **Mostrar para Pendentes**: Sim/Não

### 3. Aprovação de Clientes
- ✅ **Exigir Aprovação**: Sim
- **Grupos com Auto-Aprovação**: Selecione grupos (opcional)
- **Mensagem para Pendentes**: Texto customizado
- **Enviar E-mail de Aprovação**: Sim/Não
- **Notificar Admin**: Sim/Não
- **E-mail do Admin**: email@empresa.com.br

### 4. Quantidade Mínima
- **Habilitar**: Sim/Não
- **Quantidade Mínima Global**: Ex: 10 unidades
- **Valor Mínimo do Pedido**: Ex: R$ 500,00
- **Mensagem**: "Pedido mínimo: {{min_amount}}"

### 5. Sistema de Cotação (RFQ)
- ✅ **Habilitar**: Sim
- ✅ **Exibir Botão**: Página do Produto / Carrinho / Ambos
- **Permitir Visitantes**: Sim/Não
- **Validade (dias)**: Ex: 15 dias
- **Notificar Cliente**: Sim/Não

### 6. Grupos de Clientes
- **Grupo Atacado**: B2B Atacado (ID: 4)
  - **Desconto**: 15%
- **Grupo VIP**: B2B VIP (ID: 5)
  - **Desconto**: 20%
- **Grupo Revendedor**: B2B Revendedor (ID: 6)
  - **Desconto**: 10%
- **Grupo Padrão para Novos B2B**: Escolher grupo após aprovação

## 📋 URLs Importantes

| Funcionalidade | URL |
|----------------|-----|
| Cadastro B2B | `/b2b/register/index` |
| Dashboard B2B | `/b2b/account/dashboard` |
| Minhas Cotações | `/b2b/quote/history` |
| Solicitar Cotação | `/b2b/quote` |
| Shopping Lists | `/b2b/shoppinglist` |

## 🔐 Grupos de Clientes B2B

Os seguintes grupos foram criados:

1. **B2B Atacado** (ID: 4) - Desconto 15%
2. **B2B VIP** (ID: 5) - Desconto 20%
3. **B2B Revendedor** (ID: 6) - Desconto 10%
4. **B2B Pendente** (ID: 7) - Aguardando aprovação

## 📧 Emails Configurados

O módulo envia os seguintes emails automaticamente:

- ✉️ Novo cadastro → Admin
- ✉️ Aprovação → Cliente
- ✉️ Rejeição → Cliente
- ✉️ Nova cotação → Admin
- ✉️ Resposta de cotação → Cliente

## 🎯 Próximos Passos Recomendados

1. **Configurar E-mails**:
   - Admin → Marketing → Email Templates
   - Personalizar templates B2B

2. **Configurar Valores Mínimos** (opcional):
   - Definir valor mínimo de pedido
   - Configurar quantidade mínima

3. **Testar Fluxo Completo**:
   - Cadastro B2B → Aprovação → Primeira Compra
   - Solicitar Cotação → Responder no Admin

4. **Treinamento da Equipe**:
   - Como aprovar clientes no Admin
   - Como responder cotações
   - Como gerenciar grupos e descontos

## 📞 Suporte

Para dúvidas ou problemas, verifique:
- Logs: `var/log/system.log`
- Exception: `var/log/exception.log`
