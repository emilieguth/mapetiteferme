document.delegateEventListener('autocompleteSelect', '#journal-operation-create', function(e) {
    Operation.refreshCreate(e.detail);
});

class Operation {

    static refreshCreate(accountDetail) {

        const company = qs('#journal-operation-create').form().get('company');
        const { value: account, class: accountLabel } = accountDetail;

        new Ajax.Query()
            .url(company + '/journal/operation:create?'+ new URLSearchParams({
                company,
                account,
                accountLabel
            }))
            .method('get')
            .fetch();

    }
}