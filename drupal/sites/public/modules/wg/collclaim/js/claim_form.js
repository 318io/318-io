$(function () {
    $('#claim_form').w2form({ 
        name   : 'claim_form',
        fields : [
            { name: 'note', type: 'text'},
            { name: 'open', type: 'checkbox', required: true }
        ],
        actions: {
            reset: function () {
                this.clear();
            },
            save: function () {
                this.save();
            }
        }
    });
});