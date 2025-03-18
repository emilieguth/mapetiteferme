class Cashflow {

    static recalculateAmounts() {

        const amounts = qsa('#create-operation-list [data-field="amount"]');

        return Math.round(Array.from(amounts).reduce((accumulator, amount) => {

            const index = amount.getAttribute('data-index');

            const amountValue = (isNaN(amount.valueAsNumber) ? 0 : amount.valueAsNumber);

            const vatValue = qs('#create-operation-list [name="vatValue[' + index + ']"]').valueAsNumber;

            const type = Array.from(qsa('#create-operation-list [name="type[' + index + ']"]')).find((checkboxType) => checkboxType.checked === true);

            const amountToAdd = Math.abs(amountValue);
            const vatAmountToAdd = Math.abs((isNaN(vatValue) ? 0 : vatValue));

            const totalAmountToAdd = amountToAdd + vatAmountToAdd;

            return accumulator + (type.value === 'credit' ? totalAmountToAdd : totalAmountToAdd * -1)
        }, 0) * 100) / 100;

    }

    static updateNewOperationLine(index) {

        Operation.updateVatValue(index);

        const sum = this.recalculateAmounts();
        const totalAmount = parseFloat(qs('span[name="cashflowAmount"]').innerHTML);

        qs('#create-operation-list [name="amount[' + index + ']"]').setAttribute('value', Math.abs(totalAmount - sum).toFixed(2));
        qs('#create-operation-list [name="document[' + index + ']"]').setAttribute('value', qs('#bank-cashflow-allocate [name="cashflow[document]"]').value || '');
        qs('#create-operation-list [name="description[' + index + ']"]').setAttribute('value', qs('#create-operation-list [name="description[' + (index - 1) + ']"]').value);

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
            const { top: divTop } = qs('#cashflow-list [name="cashflow-' + cashflowId + '"]').getBoundingClientRect();
            window.scrollTo({top: divTop - mainTop, behavior: 'smooth'});
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
