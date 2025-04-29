import { defineStore } from "pinia";
import { ref } from "vue";
import { loadLanguageAsync } from "laravel-vue-i18n";
import { usePage } from "@inertiajs/vue3";

export const useLocalesStore = defineStore('locales', () => {

    //States
    const appLocales = ref(usePage().props.data.app_locales);
    let currentUser = ref(usePage().props.auth.user);
    let locale = ref(usePage().props.data.default_locale);

    if(!localStorage.getItem('locale')) {
        if(currentUser.value) {
            locale.value = currentUser.value.preferred_locale;
        }
        localStorage.setItem('locale', locale.value);
    } else if(localStorage.getItem('locale') !== 'undefined') {
        locale.value = localStorage.getItem('locale');
    }

    loadLanguageAsync(locale.value);

    //Actions
    function changeLocale(index) {
        loadLanguageAsync(index);
        localStorage.setItem('locale', index);
        locale.value = index;
    }

    function refreshCurrentUser() {
        currentUser.value = usePage().props.auth.user;
    }

    return { locale, currentUser, appLocales, changeLocale, refreshCurrentUser };
})
