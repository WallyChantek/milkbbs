var milkbbs = {};

milkbbs.initMainPage = function() {
    // Make it so clicking [Delete] and [Report] links takes the user to the
    // Post Management section, and auto-populates the ID field.
    var mgmtLinks = document.getElementsByClassName('milkbbs-post-management-link');
    for (var i = 0; i < mgmtLinks.length; i++) {
        mgmtLinks[i].addEventListener('click', function(event) {
            event.preventDefault();
            window.location = this.href;
            document.getElementById('milkbbs-post-management-post-id').value = this.closest('.milkbbs-entry').id;
            document.getElementById('milkbbs-post-management-post-id-visual').value = this.closest('.milkbbs-entry').id;
            if (this.text.includes('Delete'))
                document.getElementById('milkbbs-post-management-password').focus();
            else
                document.getElementById('milkbbs-post-management-reason').focus();
        });
    }
    
    // Determine the thread ID before the request is submitted.
    var mgmtBtns = [];
    mgmtBtns[0] = document.getElementById('milkbbs-post-management-delete-btn');
    mgmtBtns[1] = document.getElementById('milkbbs-post-management-report-btn');
    for (var i = 0; i < mgmtBtns.length; i++) {
        mgmtBtns[i].addEventListener('click', function(event) {
            var postId = document.getElementById('milkbbs-post-management-post-id').value;
            if (postId > 0) {
                var post = document.getElementById(postId);
                if (post) {
                    document.getElementById('milkbbs-post-management-thread-id').value = post.closest('.milkbbs-thread-container').firstChild.id;
                }
            }
        });
    }
}

milkbbs.initMainPage();
