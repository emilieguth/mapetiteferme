class Cashflow {

    static recalculateAmounts() {

        const operationNumber = qs('#add-operation').getAttribute('post-index');

        let sum = 0;
        for(let index = 0; index < operationNumber; index++) {

            const targetAmount = qs('[name="amount[' + index + ']"');
            const amount = CalculationField.getValue(targetAmount);

            const targetVatValue = qs('[name="vatValue[' + index + ']"');
            const vatValue = CalculationField.getValue(targetVatValue);

            const type = Array.from(qsa('#create-operation-list [name="type[' + index + ']"]')).find((checkboxType) => checkboxType.checked === true);

            const amountToAdd = Math.abs(amount);
            const vatAmountToAdd = Math.abs((isNaN(vatValue) ? 0 : vatValue));

            const totalAmountToAdd = amountToAdd + vatAmountToAdd;

            sum += (type.value === 'credit' ? totalAmountToAdd : totalAmountToAdd * -1);
        }

        return (sum * 100) / 100;

    }

    static updateNewOperationLine(index) {

        Operation.updateVatValue(index);

        const sum = this.recalculateAmounts();
        const totalAmount = parseFloat(qs('span[name="cashflowAmount"]').innerHTML);

        const targetAmount = qs('[name="amount[' + index + ']"');
        CalculationField.updateValue(targetAmount, Math.abs(totalAmount - sum).toFixed(2));

        Operation.preFillNewOperation(index);

    }

    static fillShowHideAmountWarning() {

        const sum = this.recalculateAmounts();
        const totalAmount = parseFloat(qs('span[name="cashflowAmount"]').innerHTML);

        if(sum !== totalAmount) {
            var difference = totalAmount - sum;
            qs('#cashflow-allocate-difference-warning').classList.remove('hide');
            qs('#cashflow-allocate-difference-value').innerHTML = Math.round(Math.abs(difference) * 100) / 100;
        } else {
            qs('#cashflow-allocate-difference-warning').classList.add('hide');
        }

    }

    static copyDocument(target) {

        const documentValue = target.value;

        const operations = qsa('#create-operation-list [name^="document"]');
        Array.from(operations).forEach((operation) => {
            if(operation.getAttribute('value') !== '' && operation.getAttribute('value') !== null) {
                return;
            }
            operation.setAttribute('value', documentValue);
        })

        return true;

    }
}

class CashflowList {
    static scrollTo(cashflowId) {

        if(parseInt(cashflowId) > 0) {
            const { top: mainTop} = qs('main').getBoundingClientRect();
            const stickyHeight = qs('[name="cashflow-' + cashflowId + '"]').firstParent('table')?.qs('.thead-sticky')?.scrollHeight || 0;
            const { top: divTop } = qs('#cashflow-list [name="cashflow-' + cashflowId + '"]').getBoundingClientRect();
            window.scrollTo({top: divTop - mainTop - stickyHeight, behavior: 'smooth'});
        }

    }
}


document.delegateEventListener('click', '#cashflow-doAttach input[type="checkbox"]', function() {
    CashflowAttach.updateTotal();
});
class CashflowAttach {

    static updateTotal() {

        let total = 0;
        qsa('input[type="checkbox"][name="operation[]"]:checked', operation => total += parseFloat(qs('span[data-operation="' + operation.value + '"][name="amount"]').innerHTML));
        total = Math.round(total * 100) / 100;
        qs('span[data-field="totalAmount"]').innerHTML = money(total);

        const cashflowAmount = parseFloat(qs('span[name="cashflowAmount"]').innerHTML);

        if(Math.abs(cashflowAmount) !== Math.abs(total)) {
            qs('#cashflow-attach-difference-warning').classList.remove('hide');
        } else {
            qs('#cashflow-attach-difference-warning').classList.add('hide');
        }

    }
}
