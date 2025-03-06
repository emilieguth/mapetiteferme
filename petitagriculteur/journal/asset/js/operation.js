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
        const thirdParty = qs('#journal-operation-create').form().get('thirdParty');
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

    static calculateVAT() {

        const amount = qs('[data-field="amount"]')?.value || 0;
        const vatRate = qs('[data-field="vatRate"]')?.value || 0;

        const newVatAmount = Math.round(amount * vatRate) / 100;
        qs('[data-field="vatValue"]').setAttribute('value', newVatAmount);

    }
}
