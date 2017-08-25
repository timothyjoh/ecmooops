
window.onbeforeunload = function() {

if(!jQuery('#quiz_continue_link').is(':visible')){
        if(!window.confirm("Are you sure you want to leave?"))
            return 'You are about to navigate away from the current quiz. If you do, all your progress in this quiz will be lost.';
    }
}
;