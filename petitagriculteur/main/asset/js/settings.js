
document.delegateEventListener('load', '#account-list', function() {
    Settings.scrollTo();
});

class Settings {

    static scrollTo(id) {

        if(id !== null) {
            const { top: mainTop} = qs('main').getBoundingClientRect();
            const { top: divTop } = qs('#account-list [name="account-' + id + '"]').getBoundingClientRect();

            window.scrollTo({top: divTop - mainTop, behavior: 'smooth'});
        }
    }
}
