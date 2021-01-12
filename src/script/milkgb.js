/*
    Note: Because linebreaks will be stripped from this file when inserted into
    the page, you MUST use block comments instead of single-line comments, or
    anything after that comment will also be commented out and it'll break.
*/

var milkgb = {};

milkgb.initMainPage = function() {
    /*
      Post formatting buttons
    */
    if (document.getElementById('milkgb-posting-form-formatting')) {
        document.getElementById('milkgb-formatting-bold').addEventListener('click', function(event) {
            milkgb.insertTag('milkgb-posting-form-comment', 'b');
        });
        document.getElementById('milkgb-formatting-italic').addEventListener('click', function(event) {
            milkgb.insertTag('milkgb-posting-form-comment', 'i');
        });
        document.getElementById('milkgb-formatting-underline').addEventListener('click', function(event) {
            milkgb.insertTag('milkgb-posting-form-comment', 'u');
        });
        document.getElementById('milkgb-formatting-color').addEventListener('change', function(event) {
            if (this.value !== '') {
                milkgb.insertTag('milkgb-posting-form-comment', 'color=' + this.options[this.selectedIndex].text);
                this.selectedIndex = 0;
            }
        });
    }
    
    /*
       Make it so clicking a [Delete] button takes the user to the
       Entry Management section, and auto-populates the ID field.
    */
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
};

milkgb.insertTag = function(textareaId, tag) {
    var elem = document.getElementById(textareaId);
    if (elem.selectionStart || elem.selectionStart == '0') {
        var tagStart = '[' + tag + ']';
        var tagEnd = '[/' + tag + ']';
        var posStart = elem.selectionStart;
        var posEnd = elem.selectionEnd + tagStart.length;
        
        var newStr = elem.value;
        newStr = [newStr.slice(0, posStart), tagStart, newStr.slice(posStart)].join('');
        newStr = [newStr.slice(0, posEnd), tagEnd, newStr.slice(posEnd)].join('');
        elem.value = newStr;
    }
};

milkgb.initMainPage();