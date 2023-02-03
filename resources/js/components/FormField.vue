<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div class="space-y-1">
                <div :id="`dz-${field.key}`"
                     class="dropzone w-full form-input form-input-bordered min-h-[90px]">
                    <p class="dz-message flex items-center justify-center mt-8">
                        <button class="dz-button text-gray-500" type="button">
                            {{ __('Drop files here to upload') }}
                        </button>
                    </p>
                    <div :id="`dz-${field.key}-previews`" class="dropzone-previews flex items-center justify-center gap-6">
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
            const dropzone = new Dropzone(`#dz-${this.field.key}`, this.field.options)
            dropzone.options.previewsContainer = `#dz-${this.field.key}-previews`

            dropzone.on('sending', (file, xhr, formData) => {
                formData.append('key', this.field.key);
                formData.append('attribute', this.field.attribute);
                formData.append('full_path', typeof file.fullPath === 'string' ? file.fullPath : '');
                formData.append('temp_path', typeof this.field.tempPath === 'string' ? this.field.tempPath : '');
                formData.append('temp_disk', typeof this.field.tempDisk === 'string' ? this.field.tempDisk : '');
            });

            dropzone.on('error', (file, error) => {
                console.log(error)
            })

            dropzone.on('removedfile', (file) => {
                fetch(this.field.options.url, {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        key: this.field.key,
                        attribute: this.field.attribute,
                        name: file.name,
                        full_path: typeof file.fullPath === 'string' ? file.fullPath : '',
                        temp_path: typeof this.field.tempPath === 'string' ? this.field.tempPath : '',
                        temp_disk: typeof this.field.tempDisk === 'string' ? this.field.tempDisk : '',
                    })
                })
            })
        },

        setInitialValue() {
            this.value = this.field.value || this.field.key || ''
        },

        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },
    },
}
</script>

<style>
@import "dropzone/dist/basic.css";

.dropzone .dz-preview {
    border: 1px solid lightgray;
    border-radius: 0.25rem;
    padding: 0.5rem;
}

.dropzone.form-control {
    height: auto;
}

.dropzone .dz-preview {
    background-color: #EEE;
}

.dropzone .dz-preview > .dz-details {
    display: flex;
    flex-direction: column-reverse;
}

.dropzone .dz-preview > .dz-details,
.dropzone .dz-preview > .dz-details strong {
    font-size: 0.75rem;
    font-weight: 400;
}

.dropzone .dz-preview > .dz-details .dz-filename {
    font-weight: 600;
}

.dropzone .dz-preview .dz-progress {
    border: none;
    height: auto;
}

.dropzone .dz-preview .dz-progress .dz-upload {
    height: 0.25rem;
    border-radius: 0.125rem;
}

.dropzone .dz-preview.dz-success .dz-success-mark,
.dropzone .dz-preview.dz-error .dz-error-mark {
    display: none;
}

.dropzone .dz-preview .dz-remove {
    margin-top: 0.5rem;
    display: block;
    padding: 0.125rem;
    border: 1px solid #aaa;
    border-radius: 0.25rem;
    text-align: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: #666;
}
</style>
