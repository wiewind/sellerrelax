/**
 * Created by benying.zou on 29.06.2018.
 */


var app = new Vue({
    el: '#app',
    data: {
        fns: [
            { text: 'rest/login' },
            { text: 'rest/authorized_user' },
            { text: 'rest/items' },
            { text: 'rest/orders' },
            { text: 'rest/exports' }
        ],
        picked: '',
        fnparams: {},
        result: ''
    },

    methods: {
        submit: function (event) {
            var fn = this.picked;
            this.result = '';
            var me = this;

            $.ajax({
                url: Cake.api.path + "rest/test/index",
                method: 'POST',
                data: {
                    fn: fn
                }
            }).done(function(data) {
                me.result = data;
            });
        }
    }
});