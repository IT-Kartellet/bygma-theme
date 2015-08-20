M.block_progress = {
    progressBarLast: new Array(),

    init: function (YUIObject, instances, users) {
        for (instance = 0; instance < instances.length; instance++) {
            for (user = 0; user < users.length; user++) {
                this.progressBarLast[instances[instance] + '-' + users[user]] = 'info';
            }
        }
    },

    showInfo: function (instance, user, id) {
        var last = this.progressBarLast[instance + '-' + user];
        if(last != 'info'){
            document.getElementById('progressBarInfo' + instance + '-' + user + '-' + last).style.display = 'none';
        }
        document.getElementById('progressBarInfo' + instance + '-' + user + '-' + id).style.display = 'block';
        this.progressBarLast[instance + '-' + user] = id;

        console.log(this.progressBarLast);
    },

    hideInfo: function (instance, user) {
        console.log('mouse left!!');
        var last = this.progressBarLast[instance + '-' + user];
        if(last != 'info'){
            document.getElementById('progressBarInfo' + instance + '-' + user + '-' + last).style.display = 'none';
        }
    }
};