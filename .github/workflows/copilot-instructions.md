Diretrizes de Arquitetura e Design: Payment Gateway E-Rede for GiveWP
Versão: 2.0.10 | Contexto: Plugin WordPress de gateway de pagamento (cartão de crédito e débito) via API E-Rede, integrado ao GiveWP. Slug do Plugin: payment-erede-for-givewp

## 1. Visão Geral e Escopo

Este projeto segue uma **Arquitetura PSR-4 unificada**. Todo o código PHP usa PascalCase com namespace, incluindo as camadas de Admin, Public e Includes. Não há separação de convenções entre camadas.

**Dependência obrigatória:** Plugin GiveWP (`give`). Toda lógica de gateway estende `Give\Framework\PaymentGateways\PaymentGateway`.

**Protocolo de pagamento:** API REST da E-Rede (JSON via HTTPS). Suporta crédito (com 3DS 2.0) e débito.

## 2. Padrões de Codificação (PSR-4 + PSR-12 em todo o projeto)

### Namespace raiz: `Lknpg\PaymentEredeForGivewp`

| Camada | Namespace | Diretório |
|---|---|---|
| Core | `Lknpg\PaymentEredeForGivewp\Includes` | `Includes/` |
| Admin | `Lknpg\PaymentEredeForGivewp\Admin` | `Admin/` |
| Public/Gateways | `Lknpg\PaymentEredeForGivewp\PublicView` | `Public/` |

### Regras de nomenclatura

- **Classes:** PascalCase com prefixo `Lknpg` — ex: `LknpgPaymentEredeForGivewpCreditGateway`
- **Métodos:** camelCase — ex: `getId()`, `getName()`, `processPayment()`
- **Funções globais (bootstrap):** snake_case com prefixo — ex: `payment_erede_for_givewp_run()`
- **Arquivos:** nome idêntico à classe — ex: `LknpgPaymentEredeForGivewpAdmin.php`
- **Assets JS/CSS:** camelCase — ex: `lknpgPaymentEredeForGivewpAdmin.js`
- **Constantes:** SCREAMING_SNAKE_CASE com prefixo — ex: `PAYMENT_EREDE_FOR_GIVEWP_VERSION`


## 3. Estrutura de Diretórios

```
/payment-erede-for-givewp/
|-- payment-erede-for-givewp.php       (bootstrap: define constantes, hooks de ativação, inicia plugin)
|-- uninstall.php                      (rotina de desinstalação)
|-- composer.json                      (autoload PSR-4)
|
|-- Admin/
|   |-- LknpgPaymentEredeForGivewpAdmin.php      (enqueue de assets admin)
|   |-- css/
|   |-- js/
|   |-- partials/
|       |-- lknpgPaymentEredeForGivewpAdminDisplay.php
|
|-- Public/
|   |-- LknpgPaymentEredeForGivewpPublic.php         (enqueue de assets public)
|   |-- LknpgPaymentEredeForGivewpCreditGateway.php  (gateway de crédito + 3DS 2.0)
|   |-- LknpgPaymentEredeForGivewpDebitGateway.php   (gateway de débito)
|   |-- css/
|   |-- js/
|
|-- Includes/
|   |-- LknpgPaymentEredeForGivewp.php             (classe principal — registra hooks via Loader)
|   |-- LknpgPaymentEredeForGivewpLoader.php       (registrador de add_action / add_filter)
|   |-- LknpgPaymentEredeForGivewpActivator.php
|   |-- LknpgPaymentEredeForGivewpDeactivator.php
|   |-- LknpgPaymentEredeForGivewpHelper.php       (classe abstrata com helpers estáticos)
|   |-- logs/
|
|-- languages/
|-- .github/
    |-- workflows/
        |-- main.yml               (verificação e geração do .zip)
        |-- wordpressRelease.yml
```

## 4. Classes Principais e Responsabilidades

### 4.1. `LknpgPaymentEredeForGivewp` (Includes/)
Classe principal do plugin. Instancia Admin, Public e Loader. Registra todos os hooks nos métodos `defineAdminHooks()` e `definePublicHooks()`. **Nunca registrar `add_action`/`add_filter` nos construtores de Admin ou Public.**

### 4.2. `LknpgPaymentEredeForGivewpLoader` (Includes/)
Centraliza o registro de hooks. Mantém arrays de actions e filters, aplicando-os em `run()`.

### 4.3. `LknpgPaymentEredeForGivewpHelper` (Includes/)
Classe `abstract` com métodos estáticos utilitários compartilhados entre gateways (ex: leitura de configurações, log, formatação de valores). Não instanciar diretamente.

### 4.4. `LknpgPaymentEredeForGivewpCreditGateway` (Public/)
Estende `Give\Framework\PaymentGateways\PaymentGateway`. Implementa o fluxo de pagamento por crédito, incluindo autenticação 3DS 2.0 quando configurado. Métodos obrigatórios: `id()`, `getId()`, `getName()`, `getPaymentMethodLabel()`, `createPayment()`.

### 4.5. `LknpgPaymentEredeForGivewpDebitGateway` (Public/)
Mesmo padrão do gateway de crédito, para o fluxo de débito.

## 5. Regras de Implementação

### 5.1. Registro de Hooks
- NUNCA colocar `add_action` ou `add_filter` dentro de construtores.
- Todo registro deve ocorrer em `defineAdminHooks()` / `definePublicHooks()` na classe principal `LknpgPaymentEredeForGivewp`.

### 5.2. Integração com GiveWP
- Gateways devem obrigatoriamente estender `Give\Framework\PaymentGateways\PaymentGateway`.
- Usar `Give\Donations\Models\Donation` e `Give\Donations\ValueObjects\DonationStatus` para manipular doações.
- Registrar gateways via hook `givewp_register_payment_gateway`.

### 5.3. Comunicação com a API E-Rede
- Toda requisição HTTP deve usar `wp_remote_post()` / `wp_remote_get()` (nunca `curl` direto).
- Autenticar com PV e token via Basic Auth no header.
- Tratar todos os códigos de resposta HTTP (200, 201, 400, 401, 500).
- Registrar erros via `Give\Log\LogFactory` quando o debug estiver ativo.

### 5.4. Segurança
- Sanitizar todos os inputs com `sanitize_text_field()`, `absint()`, etc.
- Verificar nonce em todas as requisições admin com `wp_verify_nonce()`.
- Escapar todos os outputs com `esc_html()`, `esc_attr()`, `esc_url()`.
- Nunca expor PV, token ou dados de cartão em logs.

### 5.5. Internacionalização
- Text domain: `payment-gateway-e-rede-for-givewp`
- Toda string visível ao usuário deve usar `__()` ou `_e()`.

### 5.6. Constantes disponíveis
```php
PAYMENT_EREDE_FOR_GIVEWP_VERSION
PAYMENT_EREDE_FOR_GIVEWP_MIN_GIVE_VERSION
PAYMENT_EREDE_FOR_GIVEWP_BASENAME
PAYMENT_EREDE_FOR_GIVEWP_FILE
PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR
PAYMENT_EREDE_FOR_GIVEWP_URL
```

### 5.7. Workflows
- `main.yml` — verificação de código e geração do `.zip` do plugin.
- `wordpressRelease.yml` — publicação no repositório SVN do WordPress.org.

## 6. Exemplo de Fluxo de Trabalho (Copilot Prompting)

Para implementar uma nova feature, ex: **"adicionar suporte a parcelamento"**:

1. **Config (Admin):** "Vou adicionar campo `installments` nas settings do gateway em `LknpgPaymentEredeForGivewpAdmin.php` e no partial de display."
2. **Helper:** "Vou criar método estático `getInstallmentOptions()` em `LknpgPaymentEredeForGivewpHelper` para calcular as parcelas com base no valor."
3. **Gateway:** "Em `LknpgPaymentEredeForGivewpCreditGateway::createPayment()`, vou ler o campo de parcelas do form e incluir no payload da requisição à API E-Rede."
4. **Frontend:** "Vou adicionar o campo de seleção de parcelas no JS/TSX do formulário de doação (`plugin-credit-script.tsx`)."

---

**Nota Final:** Todo novo arquivo PHP deve declarar `namespace` correto conforme a camada. Nunca misturar snake_case em nomes de classes ou arquivos PHP. Sempre verificar compatibilidade com a versão mínima do GiveWP (`PAYMENT_EREDE_FOR_GIVEWP_MIN_GIVE_VERSION`).

