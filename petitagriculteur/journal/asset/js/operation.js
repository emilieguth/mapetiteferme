document.delegateEventListener('autocompleteBeforeQuery', '[data-account="journal-operation-create"], [data-account="bank-cashflow-allocate"]', function(e) {
    if(e.detail.input.firstParent('div.operation-write').qs('[name^="thirdParty"]') === null) {
        return;
    }
    const thirdParty = e.detail.input.firstParent('div.operation-write').qs('[name^="thirdParty"]').getAttribute('value');
    e.detail.body.append('thirdParty', thirdParty);
});

document.delegateEventListener('autocompleteBeforeQuery', '[data-account-label="journal-operation-create"], [data-account-label="bank-cashflow-allocate"]', function(e) {

    if(e.detail.input.firstParent('div.operation-write').qs('[name^="thirdParty"]') !== null) {
        const thirdParty = e.detail.input.firstParent('div.operation-write').qs('[name^="thirdParty"]').getAttribute('value');
        e.detail.body.append('thirdParty', thirdParty);
    }

    if(e.detail.input.firstParent('div.operation-write').qs('[name^="account"]') !== null) {
        const account = e.detail.input.firstParent('div.operation-write').qs('[name^="account"]').getAttribute('value');
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

document.delegateEventListener('change', '[data-field="amountIncludingVAT"], [data-field="amount"], [data-field="vatRate"]', function(e) {

    const index = this.dataset.index;

    Operation.updateAmountValue(index);
    Operation.updateVatValue(index);
    Asset.initializeData(index);

    const formId = e.delegateTarget.form.getAttribute('id');
    if(formId === 'bank-cashflow-allocate') {
        Cashflow.fillShowHideAmountWarning();
    }

    Operation.checkVatConsistency(index);
});
document.delegateEventListener('change', '[data-vat-value="journal-operation-create"], [data-vat-value="bank-cashflow-allocate"]', function() {

    const index = this.dataset.index;

    if(this.dataset.vatValue === 'bank-cashflow-allocate') {
        Cashflow.fillShowHideAmountWarning();
    }

    Operation.checkVatConsistency(index);
});

class Operation {

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

        const operations = qsa('#create-operation-list .create-operation').length;

        qsa('#create-operation-list .create-operation-delete', node => (operations > 1 && Number(node.getAttribute('data-index')) === operations - 1) ? node.classList.remove('hide') : node.classList.add('hide'));

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
        const value = [4, 6].includes(classValue) ? 'debit' : 'credit';
        qs('[name="type[' + index + ']"][value="' + value + '"]').setAttribute('checked', true);

    }

    static refreshVAT(accountDetail) {

        const index = accountDetail.input.getAttribute('data-index');

        // Si le taux de TVA était à 0, on va re-calculer le montant HT pour éviter d'avoir à le ressaisir.
        const amountElement = accountDetail.input.firstParent('div.operation-write').qs('[name^="amount["]');
        const amount = amountElement.getAttribute('value');
        const vatRate = parseFloat(accountDetail.input.firstParent('div.operation-write').qs('[name^="vatRate["]').getAttribute('value'));
        if(vatRate === 0.0) {
            const newAmount = (amount / (1 + accountDetail.vatRate / 100)).toFixed(2);
            amountElement.setAttribute('value', Math.abs(newAmount));
        }

        // On remplit ensuite le taux de TVA
        accountDetail.input.firstParent('.operation-write').qs('[data-field="vatRate"]').setAttribute('value', accountDetail.vatRate);

        // On vérifie les calculs de TVA
        this.updateVatValue(index);

    }

    // Fonction utilisée uniquement pour mettre à jour le montant HT / la TVA
    // si montant TTC ET taux de TVA remplis ET montant HT + montant TVA non remplis
    static updateAmountValue(index) {

        const amount = parseFloat(qs('[name="amount[' + index + ']"').valueAsNumber || 0);
        const vatValue = parseFloat(qs('[name="vatValue[' + index + ']"]').valueAsNumber || 0);
        const amountIncludingVAT = parseFloat(qs('[name="amountIncludingVAT[' + index + ']"]').valueAsNumber || 0);
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
        qs('[name="amount[' + index + ']"]').setAttribute('value', amountWithoutVAT);
        qs('[name="vatValue[' + index + ']"]').setAttribute('value', amountVAT);


    }

    static updateVatValue(index) {

        const amount = qs('[name="amount[' + index + ']"').valueAsNumber;
        const vatRate = qs('[name="vatRate[' + index + ']"').valueAsNumber;

        const newVatAmount = Math.round(amount * vatRate) / 100;
        qs('[name="vatValue[' + index + ']"').setAttribute('value', newVatAmount);

    }

    static checkVatConsistency(index) {

        const amount = qs('[name="amount[' + index + ']"]').valueAsNumber;
        const vatRate = qs('[name="vatRate[' + index + ']"]').valueAsNumber;
        const vatValue = Math.round(qs('[name="vatValue[' + index + ']"]').valueAsNumber * 100) / 100;
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
}
