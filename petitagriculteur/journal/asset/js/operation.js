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

    static refreshCreate(accountDetail) {
        const thirdParty = accountDetail.input.firstParent('div.operation-write').qs('[name^="thirdParty["]').value;
        const company = qs('#journal-operation-create').form().get('company');
        const { value: account, class: accountLabel } = accountDetail;

        new Ajax.Query()
            .url(company + '/journal/operation:create?'+ new URLSearchParams({
                company,
                account,
                accountLabel,
                thirdParty,
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

        if(operations === 1 && account !== undefined) {
            Operation.enableShippingButton();
        } else {
            Operation.disableShippingButton();
        }
    }

    static addShippingBlock() {

        const thirdParty = qs('[name="thirdParty[0]"]').value;
        const account = qs('[name="account[0]"]').value;
        const accountLabel = qs('[name="accountLabel[0]"]').value;
        const date = qs('[name="date[0]"]').value;
        const description = qs('[name="description[0]"]').value;
        const document = qs('[name="document[0]"]').value;
        const type = qs('[name="type[0]"]').value;
        const company = qs('#journal-operation-create').form().get('company');

        new Ajax.Query()
            .url(company + '/journal/operation:addShipping?'+ new URLSearchParams({
                company,
                account,
                accountLabel,
                thirdParty,
                date,
                description,
                document,
                type,
            }))
            .method('get')
            .fetch();

    }
}
