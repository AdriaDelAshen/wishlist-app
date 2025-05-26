<script setup>

import {computed} from "vue";

const props = defineProps({
    pagination: {
        type: Object,
        required: true
    }
});

let links = computed(() => {
    if(props.pagination.links) {
        let links = props.pagination.links.map((link) => {
console.log(link.label)
            if(    link.label === 'Previous'
                || link.label === 'Next'
                || link.url === props.pagination.first_page_url
                || link.url === props.pagination.last_page_url
                || link.url === props.pagination.prev_page_url
                || link.url === props.pagination.next_page_url
                || link.active)
            {
                return link;
            }

            const regex = /page=(\d+)/;

            if(props.pagination.next_page_url) {
                let matchesForNextUrl = props.pagination.next_page_url.match(regex);
                if(matchesForNextUrl && +link.label === +(matchesForNextUrl[1])+1) {
                    return '...';
                }
            }
            if(props.pagination.prev_page_url) {
                let matchesForPreviousUrl = props.pagination.prev_page_url.match(regex);
                if(matchesForPreviousUrl && +link.label === +(matchesForPreviousUrl[1])-1) {
                    return '...';
                }
            }
        });
        return links.filter(function( element ) {
            return element !== undefined;
        });
    }
    return [];
});

</script>

<template>
    <div v-if="links && links.length > 0">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, linkIndex) in links" :key="linkIndex">

                <div v-if="link === '...'" class="mr-1 mb-1 px-4 py-3 text-sm leading-4">{{ link }}</div>

                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="$t('pagination.'+link.label.toLowerCase())">
                </div>

                <button v-if="link !== '...' && link.url !== null"
                        type="button"
                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-gray-200 hover:text-gray-700 focus:border-indigo-500 inline-block focus:text-indigo-500"
                        @click="$emit('pageChanged', link.url)"
                        :class="{ 'bg-blue-700 text-white':link.active }">
                    {{ isNaN(link.label)?$t('pagination.'+link.label.toLowerCase()):link.label }}
                </button>
            </template>
        </div>
        <div class="text-gray-400" style="margin-top: 10px;">{{ $t('pagination.results')+':'+ pagination.total }}</div>
    </div>
</template>

<style scoped>

</style>
