var milkgb = {};

milkgb.initMainPage = function() {
    // Make it so clicking a [Delete] button takes the user to the
    // Entry Management section, and auto-populates the ID field.
    var delLinks = document.getElementsByClassName('milkgb-entry-delete');
    for (var i = 0; i < delLinks.length; i++) {
        delLinks[i].firstChild.addEventListener('click', function(event) {
            event.preventDefault();
            window.location = this.href;
            var entryId = this.closest('.milkgb-entry').getElementsByClassName('milkgb-entry-id')[0].textContent;
            document.getElementById('milkgb-entry-management-entry-id').value = entryId;
            document.getElementById('milkgb-entry-management-entry-id-visual').value = entryId;
            document.getElementById('milkgb-entry-management-password').focus();
        });
    }
}

milkgb.initMainPage();