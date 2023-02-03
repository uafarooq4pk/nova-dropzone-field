<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div class="space-y-1">
                <div :id="`dz-${field['dz-key']}`"
                     class="dropzone w-full form-control form-input form-input-bordered min-h-[90px]">
                    <p class="dz-message flex items-center justify-center mt-8">
                        <button class="dz-button text-gray-500" type="button">
                            {{ __('Drop files here to upload') }}
                        </button>
                    </p>
                    <div :id="`dz-${field['dz-key']}-previews`" class="dropzone-previews flex items-center justify-center gap-6">
                    </div>
                </div>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'
import {Dropzone} from 'dropzone'

export default {
    components: {Dropzone},
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    mounted() {
        this.initializeComponent()
    },

    methods: {

        initializeComponent() {
            console.log(this.field['dz-key'])
            const dropzone = new Dropzone(`#dz-${this.field['dz-key']}`, this.field)
            dropzone.options.previewsContainer = `#dz-${this.field['dz-key']}-previews`

            dropzone.on('sending', (file, xhr, formData) => {
                formData.append('key', this.field['dz-key']);
                formData.append('attribute', this.field.attribute);
                formData.append('full_path', typeof file.fullPath === 'string' ? file.fullPath : '');
            });

            dropzone.on('error', (file, error) => {
                console.log(error)
            })
        },

        setInitialValue() {
            this.value = this.field.value || this.field['dz-key'] || ''
        },

        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },
    },
}
</script>

<style>
@import "dropzone/dist/basic.css";

.dz-preview {
    border: 1px solid lightgray;
    border-radius: 0.25rem;
    padding: 0.5rem;
}

.dz-error-mark > svg,
.dz-success-mark > svg {
    width: 1.5rem;
}
</style>
