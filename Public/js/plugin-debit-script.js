function lknSet3DSvalue() {
  const language = window.navigator.language.slice(0, 2);
  const height = screen.height;
  const width = screen.width;
  const colorDepth = window.screen.colorDepth;
  const userAgent = navigator.userAgent;
  const date = new Date();
  const timezoneOffset = date.getTimezoneOffset();
  const userAgentInput = document.getElementsByName('lkn_erede_debit_3ds_user_agent')[0];
  const deviceColorInput = document.getElementsByName('lkn_erede_debit_3ds_device_color')[0];
  const langInput = document.getElementsByName('lkn_erede_debit_3ds_lang')[0];
  const heightInput = document.getElementsByName('lkn_erede_debit_3ds_device_height')[0];
  const widthInput = document.getElementsByName('lkn_erede_debit_3ds_device_width')[0];
  const timezoneInput = document.getElementsByName('lkn_erede_debit_3ds_timezone')[0];
  if (userAgentInput && deviceColorInput && langInput && heightInput && widthInput && timezoneInput) {
    userAgentInput.value = userAgent;
    deviceColorInput.value = colorDepth.toString();
    langInput.value = language;
    heightInput.value = height.toString();
    widthInput.value = width.toString();
    timezoneInput.value = timezoneOffset.toString();
  }
}

// Máscara para número de cartão de débito
function lknDebitCardMask(inputHTML) {
  let cardNumber = inputHTML.target.value.replace(/\D/gmi, ''); // Remover caracteres não numéricos
  cardNumber = cardNumber.slice(0, 16);
  const cardNumberArr = cardNumber.split('');
  const resultArr = [];

  // Aplicar máscara ao número do cartão de débito
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
function lknSetBorderIfEmpty(elementId) {
  const element = document.getElementById(elementId);
  if (!element.value.trim()) {
    element.style.borderColor = 'red';
  }
}
function lknSetBorderColorOninput(inputHTML) {
  inputHTML.style.borderColor = '#666';
}
const lkn_erede_debit_3ds = {
  id: 'lkn_erede_debit_3ds',
  async initialize() {},
  async beforeCreatePayment(values) {
    // Obtenha uma referência para todos os campos de entrada
    const cardNum = document.getElementById('card_number')?.value;
    const cardCVC = document.getElementById('card_cvc')?.value;
    const cardName = document.getElementById('give-card-name-field')?.value;
    const cardExpiration = document.getElementById('card_expiry')?.value;

    // Verifique se todos os campos estão preenchidos
    if (cardNum.trim() === '' || cardCVC.trim() === '' || cardName.trim() === '' || cardExpiration.trim() === '') {
      document.getElementById('card_number')?.setAttribute('required', 'required');
      document.getElementById('card_cvc')?.setAttribute('required', 'required');
      document.getElementById('give-card-name-field')?.setAttribute('required', 'required');
      document.getElementById('card_expiry')?.setAttribute('required', 'required');

      // Define a borda como vermelha para os campos vazios
      lknSetBorderIfEmpty('card_number');
      lknSetBorderIfEmpty('card_cvc');
      lknSetBorderIfEmpty('give-card-name-field');
      lknSetBorderIfEmpty('card_expiry');
    }
    if (cardNum && cardCVC && cardName && cardExpiration) {
      //setando em values
      values.paymentCardNum = cardNum;
      values.paymentCardCVC = cardCVC;
      values.paymentCardName = cardName;
      values.paymentCardExp = cardExpiration;
    }
    const userAgentInput = document.getElementsByName('lkn_erede_debit_3ds_user_agent')[0];
    const deviceColorInput = document.getElementsByName('lkn_erede_debit_3ds_device_color')[0];
    const langInput = document.getElementsByName('lkn_erede_debit_3ds_lang')[0];
    const heightInput = document.getElementsByName('lkn_erede_debit_3ds_device_height')[0];
    const widthInput = document.getElementsByName('lkn_erede_debit_3ds_device_width')[0];
    const timezoneInput = document.getElementsByName('lkn_erede_debit_3ds_timezone')[0];
    if (userAgentInput && deviceColorInput && langInput && heightInput && widthInput && timezoneInput) {
      //setando em values
      values.paymentUserAgentInput = deviceColorInput.value;
      values.paymentDeviceColorInput = deviceColorInput.value;
      values.paymentLangInput = langInput.value;
      values.paymentHeightInput = heightInput.value;
      values.paymentwidthInput = widthInput.value;
      values.paymentTimezoneInput = timezoneInput.value;
      console.log(deviceColorInput.value);
    }
    if (values.firstname === 'error') {
      throw new Error('Gateway failed');
    }
    console.log(values);
    return {
      pluginIntent: 'lkn-plugin-intent',
      custom: 'anything'
    };
  },
  async afterCreatePayment(response) {},
  Fields() {
    setTimeout(() => {
      lknSet3DSvalue(); // Chamar a função após o atraso de 1 segundo
    }, 1000);
    function isSSL() {
      return window.location.protocol === 'https:';
    }

    // retorna no front as mensagens de erro
    function lknPrintFrontendNotice(title, message) {
      return /*#__PURE__*/React.createElement("div", {
        className: "error-notice"
      }, /*#__PURE__*/React.createElement("strong", null, title), " ", message);
    }
    if (!isSSL()) {
      return lknPrintFrontendNotice('Erro:', 'Doação desabilitada por falta de SSL (HTTPS).');
    } else {
      return /*#__PURE__*/React.createElement("fieldset", {
        className: "give-do-validate",
        id: "give_dc_fields"
      }, /*#__PURE__*/React.createElement("legend", {
        style: {
          fontSize: 'large'
        }
      }, "Informa\xE7\xF5es de cart\xE3o de d\xE9bito"), /*#__PURE__*/React.createElement("div", {
        id: "give_secure_site_wrapper"
      }, /*#__PURE__*/React.createElement("span", {
        class: "give-icon padlock"
      }), /*#__PURE__*/React.createElement("span", {
        style: {
          display: 'block',
          padding: '20px',
          textAlign: 'center'
        }
      }, "Doa\xE7\xE3o Segura por Criptografia SSL")), /*#__PURE__*/React.createElement("input", {
        type: "hidden",
        name: "lkn_erede_debit_3ds_user_agent",
        value: ""
      }), /*#__PURE__*/React.createElement("input", {
        type: "hidden",
        name: "lkn_erede_debit_3ds_device_color",
        value: ""
      }), /*#__PURE__*/React.createElement("input", {
        type: "hidden",
        name: "lkn_erede_debit_3ds_lang",
        value: ""
      }), /*#__PURE__*/React.createElement("input", {
        type: "hidden",
        name: "lkn_erede_debit_3ds_device_height",
        value: ""
      }), /*#__PURE__*/React.createElement("input", {
        type: "hidden",
        name: "lkn_erede_debit_3ds_device_width",
        value: ""
      }), /*#__PURE__*/React.createElement("input", {
        type: "hidden",
        name: "lkn_erede_debit_3ds_timezone",
        value: ""
      }), /*#__PURE__*/React.createElement("div", {
        id: "give-card-number-wrap",
        class: "form-row form-row-two-thirds form-row-responsive give-lkn-cielo-api-cc-field-wrap"
      }, /*#__PURE__*/React.createElement("span", {
        for: "card_number",
        class: "give-label",
        style: {
          display: 'block',
          padding: '10 0'
        }
      }, "N\xFAmero do cart\xE3o", /*#__PURE__*/React.createElement("span", {
        class: "give-required-indicator",
        style: {
          color: 'red'
        }
      }, " *"), /*#__PURE__*/React.createElement("span", {
        class: "give-tooltip hint--top hint--medium hint--bounce",
        "aria-label": "Normalmente possui 16 digitos na frente do seu cart\xE3o de d\xE9bito.",
        rel: "tooltip"
      }, /*#__PURE__*/React.createElement("i", {
        class: "give-icon give-icon-question"
      }))), /*#__PURE__*/React.createElement("input", {
        onInput: e => {
          lknFormatNumbers(e), lknDebitCardMask(e), lknSetBorderColorOninput(e.target);
        },
        type: "text",
        autocomplete: "off",
        name: "card_number",
        id: "card_number",
        class: "card-number give-input required",
        placeholder: "N\xFAmero do cart\xE3o",
        "aria-required": "true",
        required: true
      })), /*#__PURE__*/React.createElement("div", {
        id: "give-card-expiration-wrap",
        class: "card-expiration form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap"
      }, /*#__PURE__*/React.createElement("span", {
        for: "give-card-expiration-field",
        class: "give-label",
        style: {
          display: 'block',
          padding: '10 0'
        }
      }, "Expira\xE7\xE3o", /*#__PURE__*/React.createElement("span", {
        class: "give-required-indicator",
        style: {
          color: 'red'
        }
      }, " *"), /*#__PURE__*/React.createElement("span", {
        class: "give-tooltip give-icon give-icon-question",
        "data-tooltip": "A data de expira\xE7\xE3o do cart\xE3o de d\xE9bito, geralmente na frente do cart\xE3o."
      })), /*#__PURE__*/React.createElement("input", {
        onInput: e => {
          lknApplyDateMask(e), lknSetBorderColorOninput(e.target);
        },
        type: "text",
        autocomplete: "off",
        name: "card_expiry",
        id: "card_expiry",
        class: "card-expiry give-input required",
        placeholder: "MM / AAAA",
        "aria-required": "true",
        required: true
      })), /*#__PURE__*/React.createElement("div", {
        id: "give-card-name-wrap",
        class: "form-row form-row-two-thirds form-row-responsive"
      }, /*#__PURE__*/React.createElement("span", {
        for: "give-card-name-field",
        class: "give-label",
        style: {
          display: 'block',
          padding: '10 0'
        }
      }, "Nome do t\xEDtular do cart\xE3o", /*#__PURE__*/React.createElement("span", {
        class: "give-required-indicator",
        style: {
          color: 'red'
        }
      }, " *"), /*#__PURE__*/React.createElement("span", {
        class: "give-tooltip give-icon give-icon-question",
        "data-tooltip": "O nome do titular da conta do cart\xE3o de d\xE9bito."
      })), /*#__PURE__*/React.createElement("input", {
        onInput: e => {
          lknNameValidation(e), lknSetBorderColorOninput(e.target);
        },
        type: "text",
        autocomplete: "off",
        id: "give-card-name-field",
        name: "card_name",
        class: "card-name give-input required",
        placeholder: "Nome do titular do cart\xE3o",
        "aria-required": "true",
        required: true
      })), /*#__PURE__*/React.createElement("div", {
        id: "give-card-cvc-wrap",
        class: "form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap"
      }, /*#__PURE__*/React.createElement("span", {
        for: "give-card-cvc-field",
        class: "give-label",
        style: {
          display: 'block',
          padding: '10 0'
        }
      }, "CVV", /*#__PURE__*/React.createElement("span", {
        class: "give-required-indicator",
        style: {
          color: 'red'
        }
      }, " *"), /*#__PURE__*/React.createElement("span", {
        class: "give-tooltip give-icon give-icon-question",
        "data-tooltip": "S\xE3o os 3 ou 4 d\xEDgitos que est\xE3o atr\xE1s do seu cart\xE3o de d\xE9bito."
      })), /*#__PURE__*/React.createElement("div", {
        id: "give-card-cvc-field",
        class: "input empty give-lkn-cielo-api-cc-field give-lkn-cielo-api-card-cvc-field"
      }), /*#__PURE__*/React.createElement("input", {
        onInput: e => {
          lknCVVMask(e), lknSetBorderColorOninput(e.target);
        },
        type: "text",
        size: "4",
        maxlength: "4",
        autocomplete: "off",
        name: "card_cvc",
        id: "card_cvc",
        class: "give-input required",
        placeholder: "CVV",
        "aria-required": "true",
        required: true
      })));
    }
  }
};
window.givewp.gateways.register(lkn_erede_debit_3ds);