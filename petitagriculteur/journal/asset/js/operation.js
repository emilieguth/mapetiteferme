document.delegateEventListener('autocompleteSelect', '#journal-operation-create', function(e) {
    Operation.refreshCreate(e.detail);
});
document.delegateEventListener('autocompleteBeforeQuery', '[data-account="journal-operation-create"]', function(e) {
    if(e.detail.input.firstParent('div.operation-write').qs('[name^="thirdParty"]') === null) {
        return;
    }
    const thirdParty = e.detail.input.firstParent('div.operation-write').qs('[name^="thirdParty"]').getAttribute('value');
    e.detail.body.append('thirdParty', thirdParty);
});

class Operation {

    static getFormValues() {

        const thirdParty = qs('[name="thirdParty[0]"]')?.value;
        const account = qs('[name="account[0]"]').value;
        const accountLabel = qs('[name="accountLabel[0]"]').value;
        const date = qs('[name="date[0]"]').value;
        const description = qs('[name="description[0]"]').value;
        const document = qs('[name="document[0]"]').value;
        const type = qs('[name="type[0]"]').value;
        const amount = qs('[name="amount[0]"]').value;

        return {
            account,
            accountLabel,
            amount,
            thirdParty,
            date,
            description,
            document,
            type,
        }
    }

    static refreshCreate(accountDetail) {
        const company = qs('#journal-operation-create').form().get('company');
        const { value: account, class: accountLabel } = accountDetail;

        new Ajax.Query()
            .url(company + '/journal/operation:create?'+ new URLSearchParams({
                company,
                ...Operation.getFormValues(),
                account,
                accountLabel,
            }))
            .method('get')
            .fetch();

    }

    static calculateVAT(index) {

        const amount = qs('[name="amount[' + index + ']"')?.value || 0;
        const vatRate = qs('[name="vatRate[' + index + ']"')?.value || 0;

        const newVatAmount = Math.round(amount * vatRate) / 100;
        qs('[name="vatValue[' + index + ']"').setAttribute('value', newVatAmount);

    }

    static enableShippingButton() {

        qs('#journal-operation-create-shipping-button').classList.remove('disabled');
        qs('#journal-operation-create-shipping-button').removeAttribute('disabled');

    }

    static disableShippingButton() {

        qs('#journal-operation-create-shipping-button').classList.add('disabled');
        qs('#journal-operation-create-shipping-button').setAttribute('disabled', true);

    }

    static checkShippingButtonStatus() {

        const operations = qsa('[data-field="amount"]').length;
        const account = qs('[name="account[0]"]')?.value;
        const accountClass = qs('[data-autocomplete-field="account[0]"]').value;

        if(operations === 1 && account !== undefined && [6, 7].includes(parseInt(accountClass.substring(0, 1))) === true) {
            Operation.enableShippingButton();
        } else {
            Operation.disableShippingButton();
        }
    }

    static addShippingBlock() {

        const company = qs('#journal-operation-create').form().get('company');

        new Ajax.Query()
            .url(company + '/journal/operation:addShipping?'+ new URLSearchParams({
                company,
                ...Operation.getFormValues(),
            }))
            .method('get')
            .fetch();

    }
}
