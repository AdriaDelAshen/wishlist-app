import { defineStore } from "pinia";
import { ref } from "vue";
import { loadLanguageAsync } from "laravel-vue-i18n";
import { usePage } from "@inertiajs/vue3";

export const useLocalesStore = defineStore('locales', () => {

    //States
    const currentUser = ref(usePage().props.auth.user);
    const appLocales = usePage().props.data.app_locales;
    let locale = ref('en');//default locale

    if(!localStorage.getItem('locale')) {
        if(currentUser) {
            locale.value = currentUser.preferred_locale;
        }
        localStorage.setItem('locale', locale.value);
    } else {
        locale.value = localStorage.getItem('locale');
    }

    loadLanguageAsync(locale.value);

    //Actions
    function changeLocale(index) {
        loadLanguageAsync(index);
        localStorage.setItem('locale', index);
        locale.value = index;
    }

    return { locale, currentUser, appLocales, changeLocale };
})
