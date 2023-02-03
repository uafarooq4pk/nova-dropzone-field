<template>
    <details v-if="item.children.length" open>
        <summary>{{ item.name }}</summary>
        <div v-if="item.children.length" class="pl-6 mb-2">
            <template v-for="child in item.children">
                <Tree :item="child"/>
            </template>
        </div>
    </details>
    <p v-else>{{ item.name }}</p>
</template>

<script>
export default {
    name: "Tree",
    props: ['item']
}
</script>

<style scoped>

</style>
