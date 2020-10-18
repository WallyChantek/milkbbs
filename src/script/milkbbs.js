var milkbbs = {};

milkbbs.initMainPage = function() {
    // Make it so clicking a [Delete] button takes the user to the
    // Entry Management section, and auto-populates the ID field.
    var delLinks = document.getElementsByClassName('milkbbs-entry-delete');
    for (var i = 0; i < delLinks.length; i++) {
        delLinks[i].firstChild.addEventListener('click', function(event) {
            event.preventDefault();
            window.location = this.href;
            var entryId = this.closest('.milkbbs-entry').getElementsByClassName('milkbbs-entry-id')[0].textContent;
            document.getElementById('milkbbs-entry-management-entry-id').value = entryId;
            document.getElementById('milkbbs-entry-management-entry-id-visual').value = entryId;
            document.getElementById('milkbbs-entry-management-password').focus();
        });
    }
}

milkbbs.initMainPage();