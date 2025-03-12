document.delegateEventListener('autocompleteSelect', '[data-account="journal-operation-create"]', function(e) {
    Operation.refreshCreate(e.detail);
});

document.delegateEventListener('autocompleteSelect', '[data-third-party="journal-operation-create"]', function(e) {
    Operation.updateThirdParty(e.detail);
});

class Operation {

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

    static refreshCreate(accountDetail) {

        const index = accountDetail.input.getAttribute('data-index');

        // On saisit le libellé
        qs('[name="accountLabel[' + index + ']"]').setAttribute('value', event.detail.class.padEnd(8, 0));

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

    static updateVatValue(index) {

        const amount = qs('[name="amount[' + index + ']"')?.value || 0;
        const vatRate = qs('[name="vatRate[' + index + ']"')?.value || 0;

        const newVatAmount = Math.round(amount * vatRate) / 100;
        qs('[name="vatValue[' + index + ']"').setAttribute('value', newVatAmount);

    }
}
