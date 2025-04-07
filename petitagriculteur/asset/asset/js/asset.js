class DepreciationList {

    static scrollTo(assetId) {

        if(parseInt(assetId) > 0) {
            const { top: mainTop} = qs('main').getBoundingClientRect();
            const stickyHeight = qs('[name="asset-' + assetId + '"]').firstParent('table')?.qs('.thead-sticky')?.scrollHeight || 0;
            const { top: divTop } = qs('#asset-list [name="asset-' + assetId + '"]').getBoundingClientRect();

            window.scrollTo({top: divTop - mainTop - stickyHeight, behavior: 'smooth'});
        }

    }

}
