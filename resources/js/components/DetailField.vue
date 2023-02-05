<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <template v-for="child in items">
                <Tree :item="child" current="" @removeFile="removeFile"/>
            </template>
        </template>
    </PanelItem>
</template>

<script>
import {value} from "lodash/seq";
import Tree from "./Tree.vue";

export default {
    components: {Tree},
    methods: {
        value,
        removeFile(data) {
            const field = this.field
            Nova.request()
                .delete(this.field.options.url, {
                    data: {
                        resource_name: this.resourceName,
                        resource_id: this.resourceId,
                        file: data.file,
                        attribute: this.field.attribute,
                    }
                })
                .catch(() => Nova.$toasted.show('Error  deleting file', {type: 'error'}))
                .then(() => {
                    Nova.$toasted.show('Deleted file', {type: 'success'});
                    this.removeFromList(data.file)
                })
        },
        removeFromList(file) {

            let index = this.field.displayedAs.indexOf(file)
            if (index === -1) {
                return this.field.displayedAs
                    .filter(path => path.startsWith(file + '/'))
                    .forEach(path => this.removeFromList(path))
            }

            this.field.displayedAs.splice(index, 1)
        }

    },
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

    computed: {
        items() {
            let result = [];
            let level = {result};

            this.field.displayedAs
                .forEach(path => {
                    path.split('/').reduce((r, name, i, a) => {
                        if (!r[name]) {
                            r[name] = {result: []};
                            r.result.push({name, children: r[name].result})
                        }
                        return r[name];
                    }, level)
                })

            return result;
        },
    },
}
</script>
