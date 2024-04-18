// Máscara para número de cartão de crédito
function lknCreditCardMask(inputHTML) {
    let cardNumber = inputHTML.target.value.replace(/\D/gmi, ''); // Remover caracteres não numéricos
    cardNumber = cardNumber.slice(0, 16);
    const cardNumberArr = cardNumber.split('');
    const resultArr = [];

    // Aplicar máscara ao número do cartão de crédito
    for (let i = 0; i < cardNumberArr.length; i++) {
        resultArr.push(cardNumberArr[i]);

        // Adicionar espaços a cada 4 dígitos
        if ((i + 1) % 4 === 0 && i < 15) {
            resultArr.push(' ');
        }
    }

    inputHTML.target.value = resultArr.join('');
}

// Formatar entrada para apenas números
function lknFormatNumbers(inputHTML) {
    inputHTML.target.value = inputHTML.target.value.replace(/\D/gmi, ''); // Remover caracteres não numéricos
}

function lknCVVMask(inputHTML) {
    let cvv = inputHTML.target.value.replace(/\D/gmi, ''); // Remover caracteres não numéricos

    // Limitar o CVV a 4 dígitos
    if (cvv.length > 4) {
        cvv = cvv.slice(0, 4);
    }

    inputHTML.target.value = cvv;
}

function lknNameValidation(inputHTML) {
    let name = inputHTML.target.value.replace(/[^A-Za-z\s]/g, ''); // Remover caracteres que não sejam letras ou espaços

    inputHTML.target.value = name;
}

// Função para aplicar máscara "XX / XXXX" para mês e ano, com validação de mês e ano
function lknApplyDateMask(inputHTML) {
    let currentDate = new Date(); // Obter a data atual
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth() + 1;

    let value = inputHTML.target.value.replace(/\D/g, ''); // Remover caracteres não numéricos
    let maskedValue = '';

    // Limitar o comprimento máximo do valor
    value = value.slice(0, 6);

    for (let i = 0; i < value.length; i++) {
        if (i === 2) {
            // Adicionar barra após os dois primeiros caracteres (mês)
            maskedValue += ' / ';
        }

        // Validar o mês (deve ser entre 01 e 12)
        if (i === 2 && (value.slice(0, 2) < '01' || value.slice(0, 2) > '12')) {
            maskedValue = ''; // Limpar o valor se o mês for inválido
            break;
        }

        // Adicionar apenas números para os caracteres de mês (do índice 0 ao 1) e ano (do índice 3 ao 6)
        maskedValue += value[i];
    }

    // Validar o ano (deve ser igual ou posterior ao ano atual)
    if (maskedValue.length === 9) {
        const inputYear = parseInt(maskedValue.slice(5, 9));
        if (inputYear < currentYear) {
            maskedValue = ''; // Limpar o valor se o ano for inválido
        } else if (inputYear === currentYear) {
            // Se o ano for igual ao atual, validar o mês para garantir que seja igual ou posterior ao mês atual
            const inputMonth = parseInt(maskedValue.slice(0, 2));
            if (inputMonth < currentMonth) {
                maskedValue = ''; // Limpar o valor se o mês for inválido
            }
        }
    }

    inputHTML.target.value = maskedValue;
}

function lknSetBorderColorOninput(inputHTML) {
    inputHTML.style.borderColor = '#666';
    inputHTML.setAttribute('aria-invalid', 'false')
}

function lknSetBorderIfEmpty(elementId) {
    const element = document.getElementById(elementId);
    if (element && !element.value.trim()) {
        element.style.borderColor = 'red';
        element.setAttribute('aria-invalid', 'true');
    }
}

function lknSetDataCard(values) {
    // Obter referência para todos os campos de entrada
    const cardNumElement = document.getElementById('card_number');
    const cardCVCElement = document.getElementById('card_cvc');
    const cardNameElement = document.getElementById('give-card-name-field');
    const cardExpirationElement = document.getElementById('card_expiry');

    // Verificar se os elementos existem antes de acessar seus valores
    if (!cardNumElement || !cardCVCElement || !cardNameElement || !cardExpirationElement) {
        throw new Error('Um ou mais campos de cartão não encontrados.');
    }

    // Obter os valores dos campos de entrada
    const cardNum = cardNumElement.value;
    const cardCVC = cardCVCElement.value;
    const cardName = cardNameElement.value;
    const cardExpiration = cardExpirationElement.value;

    // Verificar se algum campo está vazio
    if (cardNum.trim() === '' || cardCVC.trim() === '' || cardName.trim() === '' || cardExpiration.trim() === '') {
        // Definir a borda como vermelha para os campos vazios
        lknSetBorderIfEmpty('card_number');
        lknSetBorderIfEmpty('card_cvc');
        lknSetBorderIfEmpty('give-card-name-field');
        lknSetBorderIfEmpty('card_expiry');  
        throw new Error('Por favor, preencha todos os campos obrigatórios.');
    } else {
        // Todos os campos estão preenchidos, atribuir valores ao objeto 'values'
        values.paymentCardNum = cardNum;
        values.paymentCardCVC = cardCVC;
        values.paymentCardName = cardName;
        values.paymentCardExp = cardExpiration;
    }
}

const lkn_erede_credit = {
    id: 'lkn_erede_credit',
    async initialize() {
    },
    async beforeCreatePayment(values) {
    
        lknSetDataCard(values);

        if (values.firstname === 'error') {
            throw new Error('Gateway failed');
        }

        console.log(values);

        return {
            ...values
        };
    },
    async afterCreatePayment(response) {
    },
    Fields() {

        function isSSL() {
            return window.location.protocol === 'https:';
        }

        // retorna no front as mensagens de erro
        function lknPrintFrontendNotice(title, message) {
            return (
                <div className="error-notice">
                    <strong>{title}</strong> {message}
                </div>
            )
        }

        if (!isSSL()) {
            return lknPrintFrontendNotice('Erro:', 'Doação desabilitada por falta de SSL (HTTPS).');
        } else {
            return (
                <fieldset className="give-do-validate" id="give_dc_fields">
                    <legend style={{ fontSize: 'large' }}>
                        Informações de cartão de crédito
                    </legend>
                    <div id="give_secure_site_wrapper">
                        <span class="give-icon padlock"></span>
                        <span style={{ display: 'block', padding: '20px', textAlign: 'center' }}>
                            Doação Segura por Criptografia SSL
                        </span>
                    </div>

                    {/* // <!-- CARD NUMBER INPUT --> */}
                    <div id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                        <span for="card_number" class="give-label" style={{ display: 'block', padding: '10 0' }}>
                            Número do cartão
                            <span class="give-required-indicator" style={{ color: 'red' }} > *</span>
                            <span class="give-tooltip hint--top hint--medium hint--bounce" aria-label="Normalmente possui 16 digitos na frente do seu cartão de crédito." rel="tooltip"><i class="give-icon give-icon-question"></i></span>
                        </span>
                        <input
                            onInput={(e) => { lknFormatNumbers(e), lknCreditCardMask(e), lknSetBorderColorOninput(e.target) }}
                            type="text"
                            autocomplete="off"
                            name="card_number"
                            id="card_number"
                            class="card-number give-input required"
                            placeholder="Número do cartão"
                            aria-required="true"
                        />
                    </div>

                    {/* // <!-- CARD EXPIRY INPUT --> */}
                    <div id="give-card-expiration-wrap" class="card-expiration form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                        <span for="give-card-expiration-field" class="give-label" style={{ display: 'block', padding: '10 0' }}>
                            Expiração
                            <span class="give-required-indicator" style={{ color: 'red' }}> *</span>
                            <span class="give-tooltip give-icon give-icon-question"
                                data-tooltip="A data de expiração do cartão de crédito, geralmente na frente do cartão."></span>
                        </span>
                        <input
                            onInput={(e) => { lknApplyDateMask(e), lknSetBorderColorOninput(e.target) }}
                            type="text"
                            autocomplete="off"
                            name="card_expiry"
                            id="card_expiry"
                            class="card-expiry give-input required"
                            placeholder="MM / AAAA"
                            aria-required="true"
                        />
                    </div>

                    {/* // <!-- CARD HOLDER INPUT --> */}
                    <div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
                        <span for="give-card-name-field" class="give-label" style={{ display: 'block', padding: '10 0' }}>
                            Nome do títular do cartão
                            <span class="give-required-indicator" style={{ color: 'red' }}> *</span>
                            <span class="give-tooltip give-icon give-icon-question"
                                data-tooltip="O nome do titular da conta do cartão de crédito.">
                            </span>
                        </span>
                        <input
                            onInput={(e) => { lknNameValidation(e), lknSetBorderColorOninput(e.target) }}
                            type="text"
                            autocomplete="off"
                            id="give-card-name-field"
                            name="card_name"
                            class="card-name give-input required"
                            placeholder="Nome do titular do cartão"
                            aria-required="true"
                        />
                    </div>

                    {/* // <!-- CARD CVV INPUT --> */}
                    <div id="give-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                        <span for="give-card-cvc-field" class="give-label" style={{ display: 'block', padding: '10 0' }}>
                            CVV
                            <span class="give-required-indicator" style={{ color: 'red' }}> *</span>
                            <span class="give-tooltip give-icon give-icon-question"
                                data-tooltip="São os 3 ou 4 dígitos que estão atrás do seu cartão de crédito."></span>
                        </span>
                        <div id="give-card-cvc-field" class="input empty give-lkn-cielo-api-cc-field give-lkn-cielo-api-card-cvc-field"></div>
                        <input
                            onInput={(e) => { lknCVVMask(e), lknSetBorderColorOninput(e.target) }}
                            type="text"
                            size="4"
                            maxlength="4"
                            autocomplete="off"
                            name="card_cvc"
                            id="card_cvc"
                            class="give-input required"
                            placeholder="CVV"
                            aria-required="true"
                        />
                    </div>
                    {/* //TODO verificar o pq disso aqui */}
                    {/* // Remove Address Fields if user has option enabled.
                    // if ('disabled' === $$configs['billing_details']) {
                    //     remove_action('give_after_dc_fields', 'give_default_cc_address_fields');
                    // } */}
                </fieldset>
            )
        }
    },
};

window.givewp.gateways.register(lkn_erede_credit);