<template>
    <details v-if="item.children.length" open>
        <summary class="pl-4 hover:bg-gray-200 dark:hover:bg-gray-600 flex justify-between cursor-pointer">
            <span class="py-1 font-bold">{{ item.name }}</span>
            <button type="button" class="hidden px-4 py-1 hover:bg-red-400 hover:text-white" @click="removeThisFile()">Delete</button>
        </summary>
        <div v-if="item.children.length" class="pl-6">
            <template v-for="child in item.children">
                <Tree :item="child" :current="path" @removeFile="removeFile"/>
            </template>
        </div>
    </details>
    <p v-else class="pl-4 hover:bg-gray-100 dark:hover:bg-gray-700 flex justify-between">
        <span class="py-1">{{ item.name }}</span>
        <button type="button" class="hidden px-4 py-1 hover:bg-red-400 hover:text-white" @click="removeThisFile()">Delete</button>
    </p>
</template>

<script>
export default {
    name: "Tree",
    props: ['item', 'current'],
    computed: {
        path() {
            return typeof this.current === 'string' && this.current.length > 0
                ? `${this.current}/${this.item.name}`
                : this.item.name
        }
    },
    emits: ['removeFile'],
    methods: {
        removeFile(data) {
            this.$emit('removeFile', data)
        },
        removeThisFile() {
            this.$emit('removeFile', {element: this, file: this.path})
        }
    }
}
</script>

<style scoped>
summary:hover > button.hidden,
p:hover button.hidden {
    display: inline-block;
}
summary:hover + div {
    background-color: rgb(241,245,249);
}
</style>
