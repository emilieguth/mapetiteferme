document.delegateEventListener('focus', '.create-operation [name^="amount"], .create-operation [name^="vat"]', function() {
    this.select();
});

document.delegateEventListener('autocompleteBeforeQuery', '[data-third-party="bank-cashflow-allocate"]', function(e) {
    if(e.detail.input.firstParent('form').qs('[name="id"]') === null) {
        return;
    }
    const cashflowId = e.detail.input.firstParent('form').qs('[name="id"]').value;
    e.detail.body.append('cashflowId', cashflowId);
});

document.delegateEventListener('autocompleteBeforeQuery', '[data-account="journal-operation-create"], [data-account="bank-cashflow-allocate"]', function(e) {
    if(e.detail.input.firstParent('div.create-operation').qs('[name^="thirdParty"]') !== null) {
        const thirdParty = e.detail.input.firstParent('div.create-operation').qs('[name^="thirdParty"]').getAttribute('value');
        e.detail.body.append('thirdParty', thirdParty);
    }
    Array.from(qsa('div.create-operation [name^="account"]')).map(element => e.detail.body.append('accountAlready[]', parseInt(element.value)));
});

document.delegateEventListener('autocompleteBeforeQuery', '[data-account-label="journal-operation-create"], [data-account-label="bank-cashflow-allocate"]', function(e) {

    if(e.detail.input.firstParent('div.create-operation').qs('[name^="thirdParty"]') !== null) {
        const thirdParty = e.detail.input.firstParent('div.create-operation').qs('[name^="thirdParty"]').getAttribute('value');
        e.detail.body.append('thirdParty', thirdParty);
    }

    if(e.detail.input.firstParent('div.create-operation').qs('[name^="account"]') !== null) {
        const account = e.detail.input.firstParent('div.create-operation').qs('[name^="account"]').getAttribute('value');
        e.detail.body.append('account', account);
    }

});

document.delegateEventListener('autocompleteSelect', '[data-account="journal-operation-create"], [data-account="bank-cashflow-allocate"]', function(e) {
    Operation.updateType(e.detail);
    Operation.refreshVAT(e.detail);
    Operation.checkAutocompleteStatus(e);
});

document.delegateEventListener('autocompleteUpdate', '[data-third-party="journal-operation-create"], [data-third-party="bank-cashflow-allocate"]', function(e) {
    Operation.checkAutocompleteStatus(e);
});

document.delegateEventListener('autocompleteSelect', '[data-third-party="journal-operation-create"], [data-third-party="bank-cashflow-allocate"]', function(e) {
    Operation.updateThirdParty(e.detail);
    Operation.checkAutocompleteStatus(e);
});

document.delegateEventListener('mouseover', '[data-highlight]', function(e) {
    const highlight = e.delegateTarget.dataset.highlight;
    Operation.highlight(highlight);
});
document.delegateEventListener('mouseout', '[data-highlight]', function(e) {
    const highlight = e.delegateTarget.dataset.highlight;
    Operation.unhighlight(highlight);
});

document.delegateEventListener('change', '[data-date="journal-operation-create"]', function(e) {
    Operation.copyDate(e);
});

document.delegateEventListener('change', '[data-field="amountIncludingVAT"], [data-field="amount"], [data-field="vatRate"]', function(e) {

    const index = this.dataset.index;

    Operation.updateAmountValue(index);
    Operation.updateVatValue(index);
    Asset.initializeData(index);

    const formId = e.delegateTarget.form.getAttribute('id');
    if(formId === 'bank-cashflow-allocate') {
        Cashflow.fillShowHideAmountWarning();
    }

    //Operation.checkVatConsistency(index);
});
document.delegateEventListener('change', '[data-vat-value="journal-operation-create"], [data-vat-value="bank-cashflow-allocate"]', function() {

    const index = this.dataset.index;

    if(this.dataset.vatValue === 'bank-cashflow-allocate') {
        Cashflow.fillShowHideAmountWarning();
    }

    //Operation.checkVatConsistency(index);
});

document.delegateEventListener('change', '[data-journal-type="journal-operation-create"]', function (e) {

    const index = this.dataset.index;

    //bankAccountClass and cashAccountClass
    if(['bank', 'cash'].indexOf(e.delegateTarget.value) > -1) {
        qs('[data-wrapper="counterpart[' + index + ']"]').removeHide();
    } else {
        qs('[data-wrapper="counterpart[' + index + ']"]').hide();
    }

});

class Operation {

    static highlight(selector) {
        selector.indexOf('linked') > 0
            ? qs('[name-linked="' + selector + '"]').classList.add('row-highlight')
            : qs('[name="' + selector + '"]').classList.add('row-highlight');
    }

    static unhighlight(selector) {
        selector.indexOf('linked') > 0
             ? qs('[name-linked="' + selector + '"]').classList.remove('row-highlight')
             : qs('[name="' + selector + '"]').classList.remove('row-highlight');
    }

    static preFillNewOperation(index) {

        qs('[name="date[' + index + ']"]').setAttribute('value', qs('[name="date[' + (index - 1) + ']"]').value)
        qs('[name="document[' + index + ']"]').setAttribute('value', qs('[name="document[' + (index - 1) + ']"]').value)
        qs('[name="description[' + index + ']"]').setAttribute('value', qs('[name="description[' + (index - 1) + ']"]').value)

        if(qs('[name="paymentMode[' + (index - 1) + ']"]:checked')) {
            const checked = qs('[name="paymentMode[' + (index - 1) + ']"]:checked')?.value || '';
            qs('[name="paymentMode[' + index + ']"][value="' + checked + '"]').setAttribute('checked', 'checked');
        }

        if(qs('[name="paymentDate[' + index + ']"]') && qs('[name="paymentDate[' + (index - 1) + ']"]')) {
            qs('[name="paymentDate[' + index + ']"]').setAttribute('value', qs('[name="paymentDate[' + (index - 1) + ']"]').value)
        }

        if(qs('[name="thirdParty[' + index + ']"]') && qs('[name="thirdParty[' + (index - 1) + ']"]')) {
            qs('[name="thirdParty[' + index + ']"]').setAttribute('value', qs('[name="thirdParty[' + (index - 1) + ']"]').value || null)
        }

    }

    static checkAutocompleteStatus(e) {

        const field = e.delegateTarget.dataset.autocompleteField;
        qs('[data-wrapper="' + field + '"]', node => node.classList.remove('form-error-wrapper'));
        if(e.detail.value === undefined) {
            qs('[data-wrapper="' + field + '"]', node => node.classList.add('form-error-wrapper'));
        }
    }

    static initAutocomplete() {

        qsa('[data-autocomplete-field]', (node) => {
            const field = node.dataset.autocompleteField;
            // Exemple : formulaire de recherche
            if(node.firstParent('[data-wrapper]') === null) {
                return;
            }
            node.firstParent('[data-wrapper]').setAttribute('data-wrapper', field);
        });
    }

    static updateThirdParty(detail) {
        detail.input.firstParent('form').qs('#add-operation').setAttribute('post-third-party', detail.value);
    }

    static deleteOperation(target) {

        target.firstParent('.create-operation').remove();
        const index = Number(qs('#add-operation').getAttribute('post-index'));
        qs('#add-operation').setAttribute('post-index', index - 1);

        Operation.showOrHideDeleteOperation();
        Operation.updateSubmitText();

    }

    static showOrHideDeleteOperation() {

        const operations = qsa('#create-operation-list .create-operation:not(.create-operation-headers)').length;

        qsa('.create-operation-delete', node => (operations > 1 && Number(node.getAttribute('data-index')) === operations - 1) ? node.classList.remove('hide') : node.classList.add('hide'));

        qs('.create-operations-container').setAttribute('data-columns', operations);

        Operation.updateSubmitText();

    }

    static updateSubmitText() {

        const operations = qsa('#create-operation-list .create-operation').length;

        qs('#submit-save-operation').innerHTML = qs('#submit-save-operation').getAttribute(operations > 1 ? 'data-text-plural' : 'data-text-singular');
    }

    static updateType(accountDetail) {

        const index = accountDetail.input.getAttribute('data-index');

        if(qs('[name="type[' + index + ']"]:checked') !== null) {
            return;
        }
        const classValue = parseInt(accountDetail.itemText.substring(0, 1));
        const value = [2, 4, 6].includes(classValue) ? 'debit' : 'credit';
        qs('[name="type[' + index + ']"][value="' + value + '"]').setAttribute('checked', true);

    }

    static refreshVAT(accountDetail) {

        const index = accountDetail.input.getAttribute('data-index');

        // Si le taux de TVA était à 0, on va re-calculer le montant HT pour éviter d'avoir à le ressaisir.
        const targetAmount = qs('[name="amount[' + index + ']"');
        const amount = CalculationField.getValue(targetAmount);

        const vatRate = parseFloat(qs('[name="vatRate[' + index + ']"').valueAsNumber || 0);
        if(vatRate === 0.0) {
            const newAmount = (amount / (1 + accountDetail.vatRate / 100)).toFixed(2);
            CalculationField.updateValue(targetAmount, Math.abs(newAmount));
        }

        // On remplit ensuite le taux de TVA
        accountDetail.input.firstParent('.create-operation').qs('[data-field="vatRate"]').value = accountDetail.vatRate;

        // On vérifie les calculs de TVA
        this.updateVatValue(index);

    }

    // Fonction utilisée uniquement pour mettre à jour le montant HT / la TVA
    // si montant TTC ET taux de TVA remplis ET montant HT + montant TVA non remplis
    static updateAmountValue(index) {

        const targetAmount = qs('[name="amount[' + index + ']"');
        const amount = CalculationField.getValue(targetAmount);

        const targetVatValue = qs('[name="vatValue[' + index + ']"');
        const vatValue = CalculationField.getValue(targetVatValue);

        const targetAmountIncludingVAT = qs('[name="amountIncludingVAT[' + index + ']"');
        const amountIncludingVAT = CalculationField.getValue(targetAmountIncludingVAT);

        const vatRate = parseFloat(qs('[name="vatRate[' + index + ']"]').valueAsNumber || 0);

        if(
            amount !== 0
            || vatValue !== 0
            || amountIncludingVAT === 0
            || vatRate === 0
        ) {
            return;
        }

        const amountWithoutVAT = Math.round(100 * 100 * amountIncludingVAT / (100 + vatRate)) / 100;
        const amountVAT = amountIncludingVAT - amountWithoutVAT

        CalculationField.updateValue(targetAmount, amountWithoutVAT);
        CalculationField.updateValue(targetVatValue, amountVAT);


    }

    static updateVatValue(index) {

        const targetAmount = qs('[name="amount[' + index + ']"');
        const amount = CalculationField.getValue(targetAmount);

        const vatRate = parseFloat(qs('[name="vatRate[' + index + ']"').valueAsNumber || 0);

        const newVatAmount = Math.round(amount * vatRate) / 100;
        const target = qs('[name="vatValue[' + index + ']"');

        CalculationField.updateValue(target, newVatAmount);

    }

    static checkVatConsistency(index) {

        const targetAmount = qs('[name="amount[' + index + ']"');
        const amount = CalculationField.getValue(targetAmount);
        const vatRate = parseFloat(qs('[name="vatRate[' + index + ']"').valueAsNumber || 0);

        const targetVatValue = qs('[name="vatValue[' + index + ']"');
        const vatValue = CalculationField.getValue(targetVatValue);

        const expectedVatValue = Math.round(amount * vatRate) / 100;

        if(vatValue !== expectedVatValue) {
            qs('[data-vat-warning][data-index="' + index + '"]').removeHide();
            qs('[data-wrapper="vatValue[' + index + ']"]', node => node.classList.add('form-warning-wrapper'));
        } else {
            qs('[data-wrapper="vatValue[' + index + ']"]', node => node.classList.remove('form-warning-wrapper'));
            qs('[data-vat-warning][data-index="' + index + '"]').hide();
        }

    }

    static warnVatConsistency(element) {

        let needsConfirm = 0;
        qsa('[data-vat-warning]', (node) => needsConfirm += node.classList.contains('hide') === false ? 1 : 0);

        if(needsConfirm === 0) {
            return;
        }

        const text = needsConfirm === 1 ? element.dataset.confirmTextSingular : element.dataset.confirmTextPlural;
        return confirm(text);

    }

    static copyDate(e) {

        if(e.delegateTarget.value === null) {
            return;
        }

        const index = e.delegateTarget.getAttribute('data-index');
        const paymentDateElement = e.delegateTarget.firstParent('div.create-operation').qs('[name="paymentDate[' + index + ']"]');

        if(!paymentDateElement.value) {
            paymentDateElement.setAttribute('value', e.delegateTarget.value);
        }

    }
}
