class Cashflow {

    static sumAmounts() {

        const amounts = qsa('#cashflow-create-operation-list [data-type="amount"]');

        return Array.from(amounts).reduce((accumulator, amount) => accumulator + (isNaN(amount.valueAsNumber) ? 0 : amount.valueAsNumber), 0);

    }
    static updateLastAmount(totalAmount, index) {

        const sum = this.sumAmounts();

        qs('#cashflow-create-operation-list [name="amount[' + index + ']*"]').setAttribute('value', Math.round(parseFloat(totalAmount) - sum), 2);

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

    static fillShowHideAmountWarning(totalAmount) {

        const sum = this.sumAmounts();

        if (sum !== parseFloat(totalAmount)) {
            qs('#cashflow-allocate-difference-warning').classList.remove('hide');
            qs('#cashflow-allocate-difference-value').innerHTML = Math.round(Math.abs(parseFloat(totalAmount) - sum), 2);
        } else {
            qs('#cashflow-allocate-difference-warning').classList.add('hide');
        }

    }
}