<script setup>

defineProps({
    headers: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div>
        <table>
            <tr>
                <th
                    v-for="(header, i) in headers"
                    :key="`${header}${i}`"
                    class="header-item"
                >
                    {{ header }}
                </th>
            </tr>
            <tr
                v-for="entity in data"
                :key="`entity-${entity.name}`"
                class="table-rows"
            >
                <td
                    v-for="(header, i) in headers"
                    :key="`${header}-${i}`"
                >
                    <slot :name="`column${i}`" :entity="entity"></slot>
                </td>
            </tr>
        </table>
    </div>
</template>

<style>

table {
    border-collapse: collapse;
    width: 100%;
    table-layout: auto !important;
    word-wrap: break-word;
}

td {
    padding: 24px;
    text-align: center;
    border-bottom: 1px solid rgb(224, 242, 237);
}

.header-item {
    padding: 30px 20px;
    font-size: 12px;
    background-color: rgb(224, 242, 237);
    text-transform: uppercase;
}

.table-rows:nth-child(odd) {
    background-color: rgb(250, 250, 250);
}

.table-rows:nth-child(n):hover {
    background-color: rgb(244, 246, 245);
}
</style>
