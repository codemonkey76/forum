
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

window.Vue.prototype.authorize = function (handler) {
    let user = window.App.user;

    return user ? handler(window.App.user) : false;
};

window.events = new Vue();

window.flash = function(message) {
    window.events.$emit('flash', message);
};

Vue.component('flash', require('./components/Flash.vue'));
Vue.component('thread-view', require('./pages/Thread.vue'));

const app = new Vue({
    el: '#app'
});
