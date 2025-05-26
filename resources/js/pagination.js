import { ref } from 'vue';
import axios from 'axios';

export default function usePaginationAndSorting(routeName, parameters = {}, perPage = 5, defaultSort = 'id') {
    const currentData = ref([]);
    const pagination = ref([]);
    const sortBy = ref(defaultSort);
    const sortDirection = ref('asc');

    const getCurrentPageData = (page = 1) => {
        axios
            .get(route(routeName), {
                params: {
                    ...parameters,
                    perPage: perPage,
                    page,
                    sortBy: sortBy.value,
                    sortDirection: sortDirection.value,
                }
            })
            .then(response => {
                pagination.value = response.data.pagination;
                currentData.value = response.data.pagination.data;
                window.history.replaceState(null, document.title, '?page=' + page);
            })
            .catch(console.error);
    };

    const onPageChange = (url) => {
        const matches = url.match(/page=(\d+)/);
        if (matches && matches[1]) {
            getCurrentPageData(parseInt(matches[1]));
        }
    };

    const onSortChanged = (column) => {
        if (sortBy.value === column) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortBy.value = column;
            sortDirection.value = 'asc';
        }
        getCurrentPageData(1);
    };

    return {
        perPage,
        currentData,
        pagination,
        sortBy,
        sortDirection,
        getCurrentPageData,
        onPageChange,
        onSortChanged
    };
}
