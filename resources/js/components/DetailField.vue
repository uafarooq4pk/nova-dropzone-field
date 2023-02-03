<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <template v-for="child in items">
                <Tree :item="child"/>
            </template>
        </template>
    </PanelItem>
</template>

<script>
import {value} from "lodash/seq";
import Tree from "./Tree.vue";

export default {
    components: {Tree},
    methods: {value},
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
