// import {ref} from "vue";
//
// let perPage = ref(5);
//
// const routeUrl = 'users.get_current_users_page';
// export let currentData = ref([]);
// export let pagination = ref([]);
//
// export const getCurrentPageData = (page, routeUrl) => {
//     axios
//         .get(route(routeUrl,{ perPage: perPage.value, page: page }))
//         .then((response) => {
//             pagination.value = response.data.pagination;
//             currentData.value = response.data.pagination.data;
//         })
//         .catch((error) => console.log(error))
// };
//
// const initialUrl = document.URL;
// let initialPage = 1;
//
// if(initialUrl.includes('page=')) {
//     const regex = /page=(\d+)/;
//     const matches = initialUrl.match(regex);
//     if(matches && matches[1] != null) {
//         initialPage = matches[1];
//     }
// } else {
//     window.history.replaceState(null, document.title, '?page='+initialPage)
// }
//
// getCurrentPageData(initialPage, routeUrl);
//
// export const onPageChange = (url) => {
//     const regex = /page=(\d+)/;
//     const matches = url.match(regex);
//     if(matches && matches[1] != null) {
//         getCurrentPageData(matches[1], routeUrl);
//         window.history.replaceState(null, document.title, '?page='+matches[1])
//     }
// };
