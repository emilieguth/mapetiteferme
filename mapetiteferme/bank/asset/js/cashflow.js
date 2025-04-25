class Cashflow {

    static recalculateAmounts(excludeIndex) {

        const operationNumber = qs('#add-operation').getAttribute('post-index');

        let sum = 0;
        for(let index = 0; index < operationNumber; index++) {

            if(excludeIndex === index) {
                continue;
            }

            const targetAmount = qs('[name="amount[' + index + ']"');
            const amount = CalculationField.getValue(targetAmount);

            const targetVatValue = qs('[name="vatValue[' + index + ']"');
            const vatValue = CalculationField.getValue(targetVatValue);

            const type = Array.from(qsa('#create-operation-list [name="type[' + index + ']"]')).find((checkboxType) => checkboxType.checked === true);

            const amountToAdd = Math.abs(isNaN(amount) ? 0 : amount);
            const vatAmountToAdd = Math.abs(isNaN(vatValue) ? 0 : vatValue);

            const totalAmountToAdd = amountToAdd + vatAmountToAdd;

            sum += (type.value === 'credit' ? totalAmountToAdd : totalAmountToAdd * -1);
        }

        return (sum * 100) / 100;

    }

    // Remplit les valeurs de l'écriture en fonction des autres écritures créées et du montant total attendu
    static fillIndexAccordingly(index) {

        const totalAmountIncludingVat = parseFloat(qs('span[name="cashflowAmount"]').innerHTML);

        const sum = Cashflow.recalculateAmounts(index);

        const missingValue = Math.round((totalAmountIncludingVat - sum) * 100) / 100;

        if(missingValue !== 0.0) {

            const targetAmountIncludingVAT = qs('[name="amountIncludingVAT[' + index + ']"');
            CalculationField.setValue(targetAmountIncludingVAT, Math.abs(missingValue));

            const targetAmount = qs('[name="amount[' + index + ']"');
            const vatRate = qs('[name="vatRate[' + index + ']"]').valueAsNumber;
            const missingAmountValue = Math.round(missingValue / (1 + vatRate / 100) * 100) / 100;
            CalculationField.setValue(targetAmount, Math.abs(missingAmountValue));

        }
    }

    static updateNewOperationLine(index) {

        Operation.preFillNewOperation(index); // On copie ce qu'on peut copier

        Cashflow.fillIndexAccordingly(index); // On remplit les trous
        Operation.updateAmountValue(index); // On complète les calculs

        Cashflow.checkValidationValues();

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

    // Recalcule le montant TTC / HT en fonction de la TVA et des montants des autres écritures déjà remplies.
    static recalculate(index) {

        Cashflow.fillIndexAccordingly(index); // On remplit les trous
        Operation.updateAmountValue(index); // On complète les calculs

    }

    static checkValidationValues() {

        if(qs('form').getAttribute('id') !== 'bank-cashflow-allocate') {
            return;
        }

        const sum = this.recalculateAmounts();
        const totalAmount = parseFloat(qs('span[name="cashflowAmount"]').innerHTML);

        qs('.create-operation-validate-title > [data-field="cashflowAmount"]').innerHTML = money(Math.abs(totalAmount));

        const amountIncludingVAT = Array.from(qsa('[type="hidden"][name^="amountIncludingVAT["]', element => element.value))
            .reduce((acc, value) => acc + parseFloat(value.value || 0), 0);
        const amount = Array.from(qsa('[type="hidden"][name^="amount["]', element => element.value))
            .reduce((acc, value) => acc + parseFloat(value.value || 0), 0);
        const vatValue = Array.from(qsa('[type="hidden"][name^="vatValue["]', element => element.value))
            .reduce((acc, value) => acc + parseFloat(value.value || 0), 0);
        const assetValue = Array.from(qsa('[type="number"][name^="asset["]')).map(function(node) {
            if(node.name.match(/asset\[\d+\]\[value\]/)) {
                return node;
            }
        }).filter(asset => asset)
            .reduce((acc, value) => acc + parseFloat(value.value || 0), 0);
        qs('.create-operation-validate[data-field="amountIncludingVAT"]').innerHTML = '= ' + money(amountIncludingVAT);
        qs('.create-operation-validate[data-field="amount"]').innerHTML = '= ' + money(amount);
        qs('.create-operation-validate[data-field="vatValue"]').innerHTML = '= ' + money(vatValue);
        qs('.create-operation-validate[data-field="assetValue"]').innerHTML = '= ' + money(assetValue);

        if(sum !== totalAmount) {
            var difference = totalAmount - sum;
            qs('.create-operation-validate[data-field="amountIncludingVAT"]').classList.add('util-danger');
            qs('#cashflow-allocate-difference-warning').classList.remove('hide');
            qs('#cashflow-allocate-difference-value').innerHTML = money(Math.abs(difference));
        } else {
            qs('.create-operation-validate[data-field="amountIncludingVAT"]').classList.remove('util-danger');
            qs('#cashflow-allocate-difference-warning').classList.add('hide');
        }
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
