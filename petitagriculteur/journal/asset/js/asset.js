document.delegateEventListener('autocompleteSelect', '[data-account-label="journal-operation-create"], [data-account-label="bank-cashflow-allocate"]', function(e) {
    Asset.openForm(e);
});

class Asset {


    static openForm(e) {

        const index = e.delegateTarget.dataset.index;
        const accountLabel = e.detail.value;
        if(accountLabel.substring(0, 1) !== '2') {
            return;
        }

        const formId = e.delegateTarget.form.getAttribute('id');

        qs('[data-asset="' + formId + '"][data-index="' + index + '"]').removeHide();
        qs('[data-asset="' + formId + '"][data-index="' + index + '"]').removeHide();

        Asset.initializeData(index);

    }

    static initializeData(index) {

        qs('[name="asset[' + index + '][acquisitionDate]"]').setAttribute('value', qs('[name="date[' + index + ']"').value);
        qs('[name="asset[' + index + '][startDate]"]').setAttribute('value', qs('[name="date[' + index + ']"').value);
        qs('[name="asset[' + index + '][value]"]').setAttribute('value', qs('[name="amount[' + index + ']"').value);


    }
}
