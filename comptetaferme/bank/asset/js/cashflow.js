document.delegateEventListener('autocompleteSelect', '#bank-cashflow-allocate', function(e) {
    if (e.target.getAttribute('id') === 'bank-cashflow-allocate') {
        Cashflow.refreshAllocate(e);
    }
});

class Cashflow {

    static refreshAllocate(event) {
        event.detail.input.firstParent('.operation-write').qs('[data-field="vatRate"]').setAttribute('value', event.detail.vatRate);
        this.fillShowHideAmountWarning();
    }

    static recalculateAmounts() {

        const amounts = qsa('#cashflow-create-operation-list [data-type="amount"]');

        return Array.from(amounts).reduce((accumulator, amount) => {

            const index = amount.getAttribute('data-index');

            const amountValue = (isNaN(amount.valueAsNumber) ? 0 : amount.valueAsNumber);

            const vatRate = qs('#cashflow-create-operation-list [name="vatRate[' + index + ']*"]').valueAsNumber;
            const vatValue = (amountValue * vatRate / 100).toFixed(2);

            qs('#cashflow-create-operation-list [name="vatValue[' + index + ']"]').setAttribute('value', vatValue);

            const type = Array.from(qsa('#cashflow-create-operation-list [name="type[' + index + ']*"]')).find((checkboxType) => checkboxType.checked === true);

            const amountToAdd = Math.abs(amountValue);
            const vatAmountToAdd = Math.abs((isNaN(vatValue) ? 0 : vatValue));

            const totalAmountToAdd = amountToAdd + vatAmountToAdd;
console.log(amountToAdd, vatAmountToAdd, totalAmountToAdd);
            return accumulator + (type.value === 'credit' ? totalAmountToAdd : totalAmountToAdd * -1)
        }, 0);

    }
    static updateLastAmount(index) {

        const sum = this.recalculateAmounts();
        const totalAmount = parseFloat(qs('#get-allocate-total-amount').innerHTML);

        qs('#cashflow-create-operation-list [name="amount[' + index + ']*"]').setAttribute('value', abs(Math.round(totalAmount - sum)), 2);

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

        if (sum !== totalAmount) {
            var difference = totalAmount - sum;
            qs('#cashflow-allocate-difference-warning').classList.remove('hide');
            qs('#cashflow-allocate-difference-value').innerHTML = Math.abs(difference).toFixed(2);
        } else {
            qs('#cashflow-allocate-difference-warning').classList.add('hide');
        }

    }
}