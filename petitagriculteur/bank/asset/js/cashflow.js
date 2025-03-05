document.delegateEventListener('autocompleteBeforeQuery', '[data-account="bank-cashflow-allocate"]', function(e) {
    if(e.detail.input.firstParent('div.operation-write').qs('[name^="thirdParty"]') === null) {
        return;
    }
    const thirdParty = e.detail.input.firstParent('div.operation-write').qs('[name^="thirdParty"]').getAttribute('value');
    e.detail.body.append('thirdParty', thirdParty);
});

document.delegateEventListener('autocompleteSelect', '[data-account="bank-cashflow-allocate"]', function(e) {
    Cashflow.refreshAllocate(e);
});

class Cashflow {

    static refreshAllocate(event) {
        // Si le taux de TVA était à 0, on va re-calculer le montant HT pour éviter d'avoir à le ressaisir.
        const amountElement = event.detail.input.firstParent('div.operation-write').qs('[name^="amount["]');
        const amount = amountElement.getAttribute('value');
        const vatRate = parseFloat(event.detail.input.firstParent('div.operation-write').qs('[name^="vatRate["]').getAttribute('value'));
        if(vatRate === 0.0) {
            const newAmount = (amount / (1 + event.detail.vatRate / 100)).toFixed(2);
            amountElement.setAttribute('value', Math.abs(newAmount));
        }

        // On remplit ensuite le taux de TVA
        event.detail.input.firstParent('.operation-write').qs('[data-field="vatRate"]').setAttribute('value', event.detail.vatRate);

        // On vérifie les calculs de TVA
        this.fillShowHideAmountWarning();
    }

    static recalculateAmounts() {

        const amounts = qsa('#cashflow-create-operation-list [data-type="amount"]');

        return Math.round(Array.from(amounts).reduce((accumulator, amount) => {

            const index = amount.getAttribute('data-index');

            const amountValue = (isNaN(amount.valueAsNumber) ? 0 : amount.valueAsNumber);

            const vatRate = qs('#cashflow-create-operation-list [name="vatRate[' + index + ']*"]').valueAsNumber;
            const vatValue = (amountValue * vatRate / 100).toFixed(2);

            qs('#cashflow-create-operation-list [name="vatValue[' + index + ']"]').setAttribute('value', vatValue);

            const type = Array.from(qsa('#cashflow-create-operation-list [name="type[' + index + ']*"]')).find((checkboxType) => checkboxType.checked === true);

            const amountToAdd = Math.abs(amountValue);
            const vatAmountToAdd = Math.abs((isNaN(vatValue) ? 0 : vatValue));

            const totalAmountToAdd = amountToAdd + vatAmountToAdd;

            return accumulator + (type.value === 'credit' ? totalAmountToAdd : totalAmountToAdd * -1)
        }, 0) * 100) / 100;

    }

    static updateNewOperationLine(index) {

        const sum = this.recalculateAmounts();
        const totalAmount = parseFloat(qs('#get-allocate-total-amount').innerHTML);

        qs('#cashflow-create-operation-list [name="amount[' + index + ']*"]').setAttribute('value', Math.abs(totalAmount - sum).toFixed(2));
        qs('#cashflow-create-operation-list [name="document[' + index + ']"]').setAttribute('value', qs('#bank-cashflow-allocate [name="cashflow[document]"]').value || '');

    }

    static deleteOperation(target) {

        target.firstParent('.cashflow-create-operation').remove();
        const index = Number(qs('#cashflow-add-operation').getAttribute('post-index'));
        qs('#cashflow-add-operation').setAttribute('post-index', index - 1);

    }

    static showOrHideDeleteOperation() {

        const operations = qsa('#cashflow-create-operation-list .cashflow-create-operation').length;

        qsa('#cashflow-create-operation-list .cashflow-create-operation-delete', node => (operations > 1 && Number(node.getAttribute('data-index')) === operations - 1) ? node.classList.remove('hide') : node.classList.add('hide'));

    }

    static fillShowHideAmountWarning() {

        const sum = this.recalculateAmounts();
        const totalAmount = parseFloat(qs('#get-allocate-total-amount').innerHTML);

        if(sum !== totalAmount) {
            var difference = totalAmount - sum;
            qs('#cashflow-allocate-difference-warning').classList.remove('hide');
            qs('#cashflow-allocate-difference-value').innerHTML = Math.abs(difference).toFixed(2);
        } else {
            qs('#cashflow-allocate-difference-warning').classList.add('hide');
        }

    }

    static copyDocument(target) {

        const documentValue = target.value;
        const operationDocuments = qsa('#cashflow-create-operation-list [name^="document"]');

        Array.from(operationDocuments).forEach((operationDocument) => {

            if(operationDocument.getAttribute('value') === null) {
                operationDocument.setAttribute('value', documentValue);
            }

        });

        return true;

    }
}

class CashflowList {
    static scrollTo(cashflowId) {

        const { top: mainTop} = qs('main').getBoundingClientRect();
        const { top: divTop } = qs('#cashflow-list [name="cashflow-' + cashflowId + '"]').getBoundingClientRect();
        window.scrollTo({top: divTop - mainTop, behavior: 'smooth'});

    }
}
