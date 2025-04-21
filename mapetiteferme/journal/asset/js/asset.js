document.delegateEventListener('autocompleteSelect', '[data-account="journal-operation-create"], [data-account="bank-cashflow-allocate"]', function(e) {
    Asset.openForm(e);
});

class Asset {

    static openForm(e) {

        const index = e.delegateTarget.dataset.index;
        const accountClass = e.detail.class;

        // assetClass & subventionAssetClass
        if(!accountClass || (!accountClass.startsWith('2') && !accountClass.startsWith('13'))) {

            const formId = e.delegateTarget.form.getAttribute('id');

            qs('[data-asset="' + formId + '"][data-index="' + index + '"]').hide();

            return;
        }


        const formId = e.delegateTarget.form.getAttribute('id');

        qs('[data-asset="' + formId + '"][data-index="' + index + '"]').removeHide();

        Asset.initializeData(index);

    }

    static initializeData(index) {

        qs('[name="asset[' + index + '][acquisitionDate]"]').setAttribute('value', qs('[name="date[' + index + ']"').value);
        qs('[name="asset[' + index + '][startDate]"]').setAttribute('value', qs('[name="date[' + index + ']"').value);
        qs('[name="asset[' + index + '][value]"]').setAttribute('value', qs('[name="amount[' + index + ']"').value);


    }
}
